<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Produk - {{ $dateFrom ?? now()->subDays(30)->format('Y-m-d') }} sampai {{ $dateTo ?? now()->format('Y-m-d') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .report-period {
            font-size: 14px;
            color: #666;
        }
        
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .summary-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
        }
        
        .summary-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .summary-value {
            font-size: 16px;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 11px;
        }
        
        td {
            font-size: 10px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .ranking {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            color: white;
            text-align: center;
            line-height: 20px;
            font-weight: bold;
            font-size: 10px;
        }
        
        .rank-1 { background-color: #fbbf24; }
        .rank-2 { background-color: #9ca3af; }
        .rank-3 { background-color: #d97706; }
        
        .category-section {
            margin-top: 30px;
        }
        
        .category-grid {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        
        .category-item {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        .category-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .category-stats {
            font-size: 10px;
            line-height: 1.5;
        }
        
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ config('app.name') }}</div>
        <div>Jl. Contoh No. 123, Kota Contoh</div>
        <div>Telp: (021) 1234-5678 | Email: info@pos.com</div>
        
        <div class="report-title">LAPORAN PRODUK</div>
        <div class="report-period">
            Periode: {{ \Carbon\Carbon::parse($dateFrom ?? now()->subDays(30)->format('Y-m-d'))->format('d F Y') }} - {{ \Carbon\Carbon::parse($dateTo ?? now()->format('Y-m-d'))->format('d F Y') }}
        </div>
    </div>
    
    <!-- Summary Section -->
    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Total Produk</div>
            <div class="summary-value">{{ $summary['total_products'] ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Produk Terjual</div>
            <div class="summary-value">{{ $summary['products_sold'] ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Qty</div>
            <div class="summary-value">{{ $summary['total_quantity_sold'] ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Revenue</div>
            <div class="summary-value">Rp {{ number_format($summary['total_revenue'] ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>
    
    <!-- Top Products Table -->
    <h3>Produk Terlaris</h3>
    <table>
        <thead>
            <tr>
                <th class="text-center">Rank</th>
                <th>Nama Produk</th>
                <th>SKU</th>
                <th>Kategori</th>
                <th class="text-center">Qty Terjual</th>
                <th class="text-right">Revenue</th>
                <th class="text-center">Stok Sisa</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products->take(20) as $index => $product)
                <tr>
                    <td class="text-center">
                        @if($index < 3)
                            <div class="ranking rank-{{ $index + 1 }}">{{ $index + 1 }}</div>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td class="text-center">{{ $product->total_sold }}</td>
                    <td class="text-right">Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $product->stock }}</td>
                    <td>
                        @if($product->stock <= $product->min_stock)
                            Stok Rendah
                        @elseif($product->stock <= ($product->min_stock * 2))
                            Perhatian
                        @else
                            Aman
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Category Performance -->
    <div class="category-section">
        <h3>Performa Kategori</h3>
        <div class="category-grid">
            @foreach(($categories ?? collect())->chunk(3) as $categoryChunk)
                @foreach($categoryChunk as $category)
                    <div class="category-item">
                        <div class="category-name">{{ $category->name }}</div>
                        <div class="category-stats">
                            <div>Produk: {{ $category->products_count }}</div>
                            <div>Qty Terjual: {{ $category->total_sold ?? 0 }}</div>
                            <div>Revenue: Rp {{ number_format($category->total_revenue ?? 0, 0, ',', '.') }}</div>
                            <div>Kontribusi: {{ ($summary['total_revenue'] ?? 0) > 0 ? number_format((($category->total_revenue ?? 0) / ($summary['total_revenue'] ?? 1)) * 100, 1) : 0 }}%</div>
                        </div>
                    </div>
                @endforeach
                @if(!$loop->last)
                    </div>
                    <div class="category-grid">
                @endif
            @endforeach
        </div>
    </div>
    
    <!-- Low Stock Alert -->
    @php
        $lowStockProducts = $products->filter(function($product) {
            return $product->stock <= $product->min_stock;
        });
    @endphp
    
    @if($lowStockProducts->count() > 0)
        <div style="margin-top: 30px;">
            <h3 style="color: #dc2626;">⚠️ Peringatan Stok Rendah</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>SKU</th>
                        <th class="text-center">Stok Saat Ini</th>
                        <th class="text-center">Min. Stok</th>
                        <th class="text-center">Perlu Restock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockProducts as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->sku }}</td>
                            <td class="text-center">{{ $product->stock }}</td>
                            <td class="text-center">{{ $product->min_stock }}</td>
                            <td class="text-center">{{ max(0, $product->min_stock - $product->stock) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    
    <div class="footer">
        <div>Laporan digenerate pada: {{ now()->format('d F Y, H:i:s') }}</div>
        <div>{{ config('app.name') }} - Point of Sale System</div>
    </div>
</body>
</html>