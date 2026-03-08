<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BonController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['customer', 'user', 'items.product'])
            ->where('payment_method', 'bon')
            ->where('is_draft', false);

        // Filter by status
        $status = $request->get('status', '');
        if ($status === 'unpaid') {
            $query->where('status', 'pending')->whereNull('bon_paid_at');
        } elseif ($status === 'paid') {
            $query->where('status', 'completed')->whereNotNull('bon_paid_at');
        }

        // Search by customer name/phone or transaction number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhere('customer_info->name', 'like', "%{$search}%")
                  ->orWhere('customer_info->phone', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();

        $totalUnpaid = Transaction::bon()->bonUnpaid()->where('is_draft', false)->sum('total');
        $totalPaid = Transaction::bon()->bonPaid()->where('is_draft', false)->sum('total');
        $countUnpaid = Transaction::bon()->bonUnpaid()->where('is_draft', false)->count();
        $countPaid = Transaction::bon()->bonPaid()->where('is_draft', false)->count();

        return view('bon.index', compact('transactions', 'totalUnpaid', 'totalPaid', 'countUnpaid', 'countPaid'));
    }

    public function markAsPaid(Request $request, Transaction $transaction)
    {
        if ($transaction->payment_method !== 'bon') {
            return redirect()->back()->with('error', 'Transaksi ini bukan bon/hutang');
        }

        if ($transaction->status === 'completed') {
            return redirect()->back()->with('error', 'Bon ini sudah lunas');
        }

        $request->validate([
            'amount' => 'required|numeric|min:' . $transaction->total,
        ]);

        DB::beginTransaction();
        try {
            $transaction->update([
                'status' => 'completed',
                'paid' => $request->amount,
                'change' => $request->amount - $transaction->total,
                'bon_paid_at' => now(),
                'bon_paid_amount' => $request->amount,
            ]);

            if ($transaction->customer) {
                $transaction->customer->updateTransactionStats($transaction->total);
            }

            DB::commit();

            return redirect()->back()->with('success',
                'Bon #' . $transaction->transaction_number . ' berhasil dilunasi');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal melunasi bon: ' . $e->getMessage());
        }
    }
}
