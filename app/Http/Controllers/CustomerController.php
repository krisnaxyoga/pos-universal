<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Requests\CustomerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with('transactions');
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $customers = $query->paginate(15)->withQueryString();
        
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(CustomerRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $customer = Customer::create($request->validated());
            $customer->update(['customer_code' => $customer->generateCustomerCode()]);
            
            DB::commit();
            
            return redirect()->route('customers.index')
                           ->with('success', 'Customer berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal menambahkan customer: ' . $e->getMessage());
        }
    }

    public function show(Customer $customer)
    {
        $customer->load(['transactions' => function($query) {
            $query->with('items.product')->latest()->take(10);
        }]);
        
        // Calculate comprehensive statistics
        $transactions = $customer->transactions()->with('items.product')->get();
        
        $stats = [
            // Basic stats
            'total_transactions' => $customer->total_transactions,
            'total_spent' => $customer->total_spent,
            'average_transaction' => $customer->total_transactions > 0 
                ? $customer->total_spent / $customer->total_transactions 
                : 0,
            'last_transaction' => $customer->last_transaction_at,
            
            // Transaction status breakdown
            'completed_transactions' => $transactions->where('status', 'completed')->count(),
            'pending_transactions' => $transactions->where('status', 'pending')->count(),
            'failed_transactions' => $transactions->where('status', 'failed')->count(),
            'cancelled_transactions' => $transactions->where('status', 'cancelled')->count(),
            
            // Payment method breakdown
            'cash_transactions' => $transactions->where('payment_method', 'cash')->count(),
            'card_transactions' => $transactions->where('payment_method', 'card')->count(),
            'ewallet_transactions' => $transactions->where('payment_method', 'ewallet')->count(),
            'online_transactions' => $transactions->where('payment_method', 'online')->count(),
            
            // Spending analysis
            'cash_spent' => $transactions->where('payment_method', 'cash')->where('status', 'completed')->sum('total'),
            'card_spent' => $transactions->where('payment_method', 'card')->where('status', 'completed')->sum('total'),
            'ewallet_spent' => $transactions->where('payment_method', 'ewallet')->where('status', 'completed')->sum('total'),
            'online_spent' => $transactions->where('payment_method', 'online')->where('status', 'completed')->sum('total'),
            
            // Time-based analysis
            'this_month_transactions' => $transactions->filter(function($t) {
                return $t->created_at->isCurrentMonth();
            })->count(),
            'this_month_spent' => $transactions->filter(function($t) {
                return $t->created_at->isCurrentMonth() && $t->status === 'completed';
            })->sum('total'),
            'last_month_transactions' => $transactions->filter(function($t) {
                return $t->created_at->isLastMonth();
            })->count(),
            'last_month_spent' => $transactions->filter(function($t) {
                return $t->created_at->isLastMonth() && $t->status === 'completed';
            })->sum('total'),
            
            // Product analysis
            'total_items_purchased' => $transactions->sum(function($t) {
                return $t->items->sum('quantity');
            }),
            'favorite_products' => $transactions->flatMap(function($t) {
                return $t->items;
            })->groupBy('product_id')
            ->map(function($items) {
                return [
                    'product_name' => $items->first()->product->name ?? 'Unknown',
                    'quantity' => $items->sum('quantity'),
                    'total_spent' => $items->sum('subtotal')
                ];
            })->sortByDesc('quantity')->take(5)->values(),
            
            // Monthly trend (last 6 months)
            'monthly_trend' => collect(range(5, 0))->map(function($monthsAgo) use ($transactions) {
                $month = now()->subMonths($monthsAgo);
                $monthTransactions = $transactions->filter(function($t) use ($month) {
                    return $t->created_at->year === $month->year && 
                           $t->created_at->month === $month->month &&
                           $t->status === 'completed';
                });
                
                return [
                    'month' => $month->format('M Y'),
                    'transactions' => $monthTransactions->count(),
                    'total' => $monthTransactions->sum('total')
                ];
            }),
            
            // First and recent transaction comparison
            'first_transaction' => $transactions->sortBy('created_at')->first(),
            'recent_transaction' => $transactions->sortByDesc('created_at')->first(),
        ];
        
        return view('customers.show', compact('customer', 'stats'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        try {
            $customer->update($request->validated());
            
            return redirect()->route('customers.index')
                           ->with('success', 'Customer berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal memperbarui customer: ' . $e->getMessage());
        }
    }

    public function destroy(Customer $customer)
    {
        try {
            // Check if customer has transactions
            if ($customer->transactions()->count() > 0) {
                return redirect()->back()
                               ->with('error', 'Tidak dapat menghapus customer yang memiliki riwayat transaksi');
            }
            
            $customer->delete();
            
            return redirect()->route('customers.index')
                           ->with('success', 'Customer berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Gagal menghapus customer: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Customer $customer)
    {
        try {
            $customer->update(['is_active' => !$customer->is_active]);
            
            $status = $customer->is_active ? 'diaktifkan' : 'dinonaktifkan';
            
            return redirect()->back()
                           ->with('success', "Customer berhasil {$status}");
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Gagal mengubah status customer: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $customers = Customer::with('transactions')->get();
        
        $data = $customers->map(function($customer) {
            return [
                'customer_code' => $customer->customer_code,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'birth_date' => $customer->birth_date?->format('Y-m-d'),
                'gender' => $customer->gender,
                'total_spent' => $customer->total_spent,
                'total_transactions' => $customer->total_transactions,
                'last_transaction' => $customer->last_transaction_at?->format('Y-m-d H:i:s'),
                'status' => $customer->is_active ? 'Active' : 'Inactive',
                'created_at' => $customer->created_at->format('Y-m-d H:i:s'),
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'filename' => 'customers_' . date('Y-m-d_H-i-s') . '.csv'
        ]);
    }
}