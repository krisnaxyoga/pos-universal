<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'items.product']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if (!auth()->user()->isAdmin() && !auth()->user()->isSupervisor()) {
            $query->where('user_id', auth()->id());
        }
        
        $transactions = $query->latest()->paginate(15);
        
        return view('transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isSupervisor() && $transaction->user_id !== auth()->id()) {
            abort(403, 'Tidak memiliki akses untuk melihat transaksi ini');
        }
        
        $transaction->load(['user', 'items.product']);
        return view('transactions.show', compact('transaction'));
    }

    public function cancel(Transaction $transaction)
    {
        if ($transaction->status === 'cancelled') {
            return redirect()->back()->with('error', 'Transaksi sudah dibatalkan');
        }
        
        if ($transaction->status === 'completed') {
            foreach ($transaction->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }
        }
        
        $transaction->update(['status' => 'cancelled']);
        
        return redirect()->back()->with('success', 'Transaksi berhasil dibatalkan');
    }

    public function retry(Transaction $transaction)
    {
        // Only allow retry for failed or cancelled transactions
        if (!in_array($transaction->status, ['failed', 'cancelled'])) {
            return redirect()->back()->with('error', 'Transaksi ini tidak dapat diulang');
        }
        
        // Only admin can retry transactions
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Tidak memiliki akses untuk mengulang transaksi');
        }
        
        // Redirect to POS with retry parameter
        return redirect()->route('pos.index', ['retry' => $transaction->id])
            ->with('info', 'Silakan lakukan pembayaran ulang untuk transaksi #' . $transaction->transaction_number);
    }

    public function createRefund(Request $request, Transaction $transaction)
    {
        $request->validate([
            'refund_amount' => 'required|numeric|min:1|max:' . $transaction->total,
            'refund_reason' => 'required|string|max:500',
        ]);

        if (!$transaction->canBeRefunded()) {
            return redirect()->back()->with('error', 'Transaksi ini tidak dapat diretur');
        }

        DB::beginTransaction();

        try {
            // Create refund transaction
            $refundTransaction = Transaction::create([
                'user_id' => auth()->id(),
                'subtotal' => -$request->refund_amount,
                'discount' => 0,
                'tax' => 0,
                'total' => -$request->refund_amount,
                'paid' => $request->refund_amount,
                'change' => 0,
                'payment_method' => $transaction->payment_method,
                'status' => 'refunded',
                'notes' => 'Refund for transaction: ' . $transaction->transaction_number,
                'refund_reference_id' => $transaction->id,
                'refund_amount' => $request->refund_amount,
                'refund_reason' => $request->refund_reason,
                'refunded_at' => now(),
                'is_draft' => false,
            ]);

            // Create refund items (copy from original transaction)
            foreach ($transaction->items as $item) {
                $refundQuantity = floor(($request->refund_amount / $transaction->total) * $item->quantity);
                
                if ($refundQuantity > 0) {
                    TransactionItem::create([
                        'transaction_id' => $refundTransaction->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product_name . ' (Refund)',
                        'product_price' => -$item->product_price,
                        'quantity' => $refundQuantity,
                        'subtotal' => -($item->product_price * $refundQuantity),
                    ]);

                    // Return stock to inventory
                    if ($item->product) {
                        $item->product->increment('stock', $refundQuantity);
                    }
                }
            }

            // If full refund, mark original transaction
            if ($request->refund_amount == $transaction->total) {
                $transaction->update([
                    'status' => 'refunded',
                    'refunded_at' => now()
                ]);
            }

            DB::commit();

            return redirect()->route('transactions.show', $transaction)
                ->with('success', 'Refund berhasil diproses. Transaction: ' . $refundTransaction->transaction_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memproses refund: ' . $e->getMessage());
        }
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->status === 'completed') {
            foreach ($transaction->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }
        }
        
        $transaction->delete();
        
        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus');
    }
}
