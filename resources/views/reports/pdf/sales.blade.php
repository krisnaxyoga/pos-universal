<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - {{ $dateFrom ?? now()->format('Y-m-d') }} sampai {{ $dateTo ?? now()->format('Y-m-d') }}</title>
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
            width: 33.33%;
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
        
        <div class="report-title">LAPORAN PENJUALAN</div>
        <div class="report-period">
            Periode: {{ \Carbon\Carbon::parse($dateFrom ?? now()->format('Y-m-d'))->format('d F Y') }} - {{ \Carbon\Carbon::parse($dateTo ?? now()->format('Y-m-d'))->format('d F Y') }}
        </div>
    </div>
    
    <!-- Summary Section -->
    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Total Transaksi</div>
            <div class="summary-value">{{ $summary['total_transactions'] ?? 0 }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Penjualan</div>
            <div class="summary-value">Rp {{ number_format($summary['total_sales'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Profit</div>
            <div class="summary-value">Rp {{ number_format($summary['total_profit'] ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>
    
    <!-- Transactions Table -->
    <h3>Detail Transaksi</h3>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Transaksi</th>
                <th>Kasir</th>
                <th class="text-center">Items</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Total</th>
                <th>Metode</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $transaction->transaction_number }}</td>
                    <td>{{ $transaction->user->name }}</td>
                    <td class="text-center">{{ $transaction->items->count() }}</td>
                    <td class="text-right">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
                    <td class="text-right">
                        @if($transaction->discount > 0)
                            Rp {{ number_format($transaction->discount, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
                    <td>
                        @switch($transaction->payment_method)
                            @case('cash') Tunai @break
                            @case('card') Kartu @break
                            @case('ewallet') E-Wallet @break
                            @default {{ ucfirst($transaction->payment_method) }}
                        @endswitch
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Summary by Payment Method -->
    <h3>Ringkasan per Metode Pembayaran</h3>
    <table style="width: 50%;">
        <thead>
            <tr>
                <th>Metode Pembayaran</th>
                <th class="text-center">Jumlah</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $paymentSummary = $transactions->groupBy('payment_method')->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'total' => $group->sum('total')
                    ];
                });
            @endphp
            @foreach($paymentSummary as $method => $data)
                <tr>
                    <td>
                        @switch($method)
                            @case('cash') Tunai @break
                            @case('card') Kartu @break
                            @case('ewallet') E-Wallet @break
                            @default {{ ucfirst($method) }}
                        @endswitch
                    </td>
                    <td class="text-center">{{ $data['count'] }}</td>
                    <td class="text-right">Rp {{ number_format($data['total'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <div>Laporan digenerate pada: {{ now()->format('d F Y, H:i:s') }}</div>
        <div>{{ config('app.name') }} - Point of Sale System</div>
    </div>
</body>
</html>