<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportController extends Controller
{
    public function index()
    {
        $todaySales = Transaction::completed()->today()->sum('total');
        $todayProfit = $this->calculateProfit(Carbon::today(), Carbon::today());
        $monthSales = Transaction::completed()->thisMonth()->sum('total');
        $monthProfit = $this->calculateProfit(now()->startOfMonth(), now()->endOfMonth());
        
        return view('reports.index', compact(
            'todaySales',
            'todayProfit', 
            'monthSales',
            'monthProfit'
        ));
    }
    
    public function sales(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::today()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::today()->format('Y-m-d');
        
        $transactions = Transaction::with(['user', 'items.product'])
            ->completed()
            ->whereBetween('created_at', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay()
            ])
            ->latest()
            ->paginate(20);
            
        $summary = [
            'total_transactions' => $transactions->total(),
            'total_sales' => Transaction::completed()
                ->whereBetween('created_at', [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay()
                ])
                ->sum('total'),
            'total_profit' => $this->calculateProfit($dateFrom, $dateTo),
        ];
        
        return view('reports.sales', compact('transactions', 'summary', 'dateFrom', 'dateTo'));
    }
    
    public function products(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::today()->subDays(30)->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::today()->format('Y-m-d');
        
        $products = Product::select('products.*')
            ->selectRaw('COALESCE(SUM(transaction_items.quantity), 0) as total_sold')
            ->selectRaw('COALESCE(SUM(transaction_items.subtotal), 0) as total_revenue')
            ->selectRaw('COUNT(DISTINCT transactions.id) as total_transactions')
            ->leftJoin('transaction_items', 'products.id', '=', 'transaction_items.product_id')
            ->leftJoin('transactions', function($join) use ($dateFrom, $dateTo) {
                $join->on('transaction_items.transaction_id', '=', 'transactions.id')
                     ->where('transactions.status', 'completed')
                     ->whereBetween('transactions.created_at', [
                         Carbon::parse($dateFrom)->startOfDay(),
                         Carbon::parse($dateTo)->endOfDay()
                     ]);
            })
            ->with('category')
            ->groupBy('products.id')
            ->orderByDesc('total_sold')
            ->paginate(20);
            
        // Calculate summary statistics
        $allProducts = Product::select('products.*')
            ->selectRaw('COALESCE(SUM(transaction_items.quantity), 0) as total_sold')
            ->selectRaw('COALESCE(SUM(transaction_items.subtotal), 0) as total_revenue')
            ->leftJoin('transaction_items', 'products.id', '=', 'transaction_items.product_id')
            ->leftJoin('transactions', function($join) use ($dateFrom, $dateTo) {
                $join->on('transaction_items.transaction_id', '=', 'transactions.id')
                     ->where('transactions.status', 'completed')
                     ->whereBetween('transactions.created_at', [
                         Carbon::parse($dateFrom)->startOfDay(),
                         Carbon::parse($dateTo)->endOfDay()
                     ]);
            })
            ->groupBy('products.id')
            ->get();
            
        $summary = [
            'total_products' => $allProducts->count(),
            'products_sold' => $allProducts->where('total_sold', '>', 0)->count(),
            'total_quantity_sold' => $allProducts->sum('total_sold'),
            'total_revenue' => $allProducts->sum('total_revenue'),
        ];
        
        // Get category statistics
        $categories = \App\Models\Category::select('categories.*')
            ->selectRaw('COUNT(products.id) as products_count')
            ->selectRaw('SUM(CASE WHEN products.is_active = 1 THEN 1 ELSE 0 END) as active_products')
            ->selectRaw('COALESCE(SUM(transaction_items.quantity), 0) as total_sold')
            ->selectRaw('COALESCE(SUM(transaction_items.subtotal), 0) as total_revenue')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('transaction_items', 'products.id', '=', 'transaction_items.product_id')
            ->leftJoin('transactions', function($join) use ($dateFrom, $dateTo) {
                $join->on('transaction_items.transaction_id', '=', 'transactions.id')
                     ->where('transactions.status', 'completed')
                     ->whereBetween('transactions.created_at', [
                         Carbon::parse($dateFrom)->startOfDay(),
                         Carbon::parse($dateTo)->endOfDay()
                     ]);
            })
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get();
            
        return view('reports.products', compact('products', 'summary', 'categories', 'dateFrom', 'dateTo'));
    }
    
    public function exportSales(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::today()->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::today()->format('Y-m-d');
        
        $transactions = Transaction::with(['user', 'items.product'])
            ->completed()
            ->whereBetween('created_at', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay()
            ])
            ->latest()
            ->get();
            
        $summary = [
            'total_transactions' => $transactions->count(),
            'total_sales' => $transactions->sum('total'),
            'total_profit' => $this->calculateProfit($dateFrom, $dateTo),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
        
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        
        $html = view('reports.pdf.sales', compact('transactions', 'summary'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf->stream('laporan-penjualan-' . $dateFrom . '-to-' . $dateTo . '.pdf');
    }
    
    public function exportProducts(Request $request)
    {
        $dateFrom = $request->date_from ?? Carbon::today()->subDays(30)->format('Y-m-d');
        $dateTo = $request->date_to ?? Carbon::today()->format('Y-m-d');
        
        $products = Product::select('products.*')
            ->selectRaw('COALESCE(SUM(transaction_items.quantity), 0) as total_sold')
            ->selectRaw('COALESCE(SUM(transaction_items.subtotal), 0) as total_revenue')
            ->leftJoin('transaction_items', 'products.id', '=', 'transaction_items.product_id')
            ->leftJoin('transactions', function($join) use ($dateFrom, $dateTo) {
                $join->on('transaction_items.transaction_id', '=', 'transactions.id')
                     ->where('transactions.status', 'completed')
                     ->whereBetween('transactions.created_at', [
                         Carbon::parse($dateFrom)->startOfDay(),
                         Carbon::parse($dateTo)->endOfDay()
                     ]);
            })
            ->with('category')
            ->groupBy('products.id')
            ->orderByDesc('total_sold')
            ->get();
            
        // Calculate summary statistics
        $summary = [
            'total_products' => $products->count(),
            'products_sold' => $products->where('total_sold', '>', 0)->count(),
            'total_quantity_sold' => $products->sum('total_sold'),
            'total_revenue' => $products->sum('total_revenue'),
        ];
        
        // Get category statistics
        $categories = \App\Models\Category::select('categories.*')
            ->selectRaw('COUNT(products.id) as products_count')
            ->selectRaw('COALESCE(SUM(transaction_items.quantity), 0) as total_sold')
            ->selectRaw('COALESCE(SUM(transaction_items.subtotal), 0) as total_revenue')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('transaction_items', 'products.id', '=', 'transaction_items.product_id')
            ->leftJoin('transactions', function($join) use ($dateFrom, $dateTo) {
                $join->on('transaction_items.transaction_id', '=', 'transactions.id')
                     ->where('transactions.status', 'completed')
                     ->whereBetween('transactions.created_at', [
                         Carbon::parse($dateFrom)->startOfDay(),
                         Carbon::parse($dateTo)->endOfDay()
                     ]);
            })
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get();
            
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        
        $html = view('reports.pdf.products', compact('products', 'summary', 'categories', 'dateFrom', 'dateTo'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return $dompdf->stream('laporan-produk-' . $dateFrom . '-to-' . $dateTo . '.pdf');
    }
    
    private function calculateProfit($dateFrom, $dateTo)
    {
        return TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay()
            ])
            ->selectRaw('SUM((transaction_items.product_price - products.cost) * transaction_items.quantity) as profit')
            ->value('profit') ?? 0;
    }
}
