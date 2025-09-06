<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $todaySales = Transaction::completed()->today()->sum('total');
        $todayTransactions = Transaction::completed()->today()->count();
        $lowStockProducts = Product::lowStock()->count();
        $totalProducts = Product::active()->count();
        
        $recentTransactions = Transaction::with(['user', 'items.product'])
            ->latest()
            ->take(5)
            ->get();
            
        $lowStockItems = Product::with('category')
            ->lowStock()
            ->active()
            ->take(10)
            ->get();
            
        $salesChart = $this->getSalesChartData();
        $topProducts = $this->getTopProducts();
        
        return view('dashboard', compact(
            'todaySales',
            'todayTransactions', 
            'lowStockProducts',
            'totalProducts',
            'recentTransactions',
            'lowStockItems',
            'salesChart',
            'topProducts'
        ));
    }
    
    private function getSalesChartData()
    {
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $sales = Transaction::completed()
                ->whereDate('created_at', $date)
                ->sum('total');
            $last7Days->push([
                'date' => $date->format('M d'),
                'sales' => $sales
            ]);
        }
        return $last7Days;
    }
    
    private function getTopProducts()
    {
        return Product::select('products.*')
            ->selectRaw('SUM(transaction_items.quantity) as total_sold')
            ->join('transaction_items', 'products.id', '=', 'transaction_items.product_id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [now()->subDays(30), now()])
            ->groupBy('products.id')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();
    }

    /**
     * Get dashboard stats for notifications API
     */
    public function getStats()
    {
        try {
            $stats = [
                'low_stock_count' => Product::lowStock()->active()->count(),
                'today_transactions' => Transaction::completed()->today()->count(),
                'today_revenue' => Transaction::completed()->today()->sum('total'),
                'pending_payments' => Transaction::where('status', 'pending')
                    ->whereNotNull('ipaymu_transaction_id')
                    ->count(),
                'total_products' => Product::active()->count(),
                'total_customers' => \App\Models\Customer::count(),
                'last_update' => now()->toISOString()
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
