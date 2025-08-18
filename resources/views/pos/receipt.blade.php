<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - {{ $transaction->transaction_number }} | {{ $appSettings['app_name'] ?? config('app.name', 'POS System') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            max-width: 300px;
            margin: 0 auto;
            padding: 10px;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .logo {
            margin-bottom: 8px;
        }
        
        .logo img {
            max-width: 60px;
            max-height: 60px;
            object-fit: contain;
        }
        
        .logo-placeholder {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            color: white;
            font-size: 20px;
        }
        
        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .transaction-info {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .items {
            margin-bottom: 10px;
        }
        
        .item {
            margin-bottom: 5px;
        }
        
        .item-name {
            font-weight: bold;
        }
        
        .item-details {
            display: flex;
            justify-content: space-between;
        }
        
        .totals {
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .total-final {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            margin: 5px 0;
        }
        
        .payment-info {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            border-top: 1px dashed #000;
            padding-top: 10px;
            font-size: 10px;
        }
        
        @media print {
            body {
                width: 58mm; /* Standard thermal printer width */
                margin: 0;
                padding: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            @if(isset($appSettings['app_logo']) && $appSettings['app_logo'] && Storage::disk('public')->exists($appSettings['app_logo']))
                <img src="{{ Storage::url($appSettings['app_logo']) }}" alt="{{ $appSettings['company_name'] ?? 'Company' }}">
            @else
                <div class="logo-placeholder">
                    <i class="fas fa-cash-register"></i>
                </div>
            @endif
        </div>
        <div class="store-name">{{ $appSettings['company_name'] ?? 'Your Company Name' }}</div>
        <div>{{ $appSettings['company_address'] ?? 'Your Company Address' }}</div>
        <div>Telp: {{ $appSettings['company_phone'] ?? 'Your Phone Number' }}</div>
    </div>
    
    <div class="transaction-info">
        <div><strong>No. Transaksi:</strong> {{ $transaction->transaction_number }}</div>
        <div><strong>Kasir:</strong> {{ $transaction->user->name }}</div>
        <div><strong>Tanggal:</strong> {{ $transaction->created_at->format('d/m/Y H:i:s') }}</div>
    </div>
    
    <div class="items">
        @foreach($transaction->items as $item)
            <div class="item">
                <div class="item-name">{{ $item->product_name }}</div>
                <div class="item-details">
                    <span>{{ $item->quantity }} x Rp {{ number_format($item->product_price, 0, ',', '.') }}</span>
                    <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
        @endforeach
    </div>
    
    <div class="totals">
        <div class="total-row">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
        </div>
        
        @if($transaction->discount > 0)
            <div class="total-row">
                <span>Diskon:</span>
                <span>- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
            </div>
        @endif
        
        @if($transaction->tax > 0)
            <div class="total-row">
                <span>Pajak:</span>
                <span>Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span>
            </div>
        @endif
        
        <div class="total-row total-final">
            <span>TOTAL:</span>
            <span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
        </div>
    </div>
    
    <div class="payment-info">
        <div class="total-row">
            <span>Metode Bayar:</span>
            <span>
                @switch($transaction->payment_method)
                    @case('cash') Tunai @break
                    @case('card') Kartu @break
                    @case('ewallet') E-Wallet @break
                    @default {{ ucfirst($transaction->payment_method) }}
                @endswitch
            </span>
        </div>
        <div class="total-row">
            <span>Bayar:</span>
            <span>Rp {{ number_format($transaction->paid, 0, ',', '.') }}</span>
        </div>
        @if($transaction->change > 0)
            <div class="total-row">
                <span>Kembalian:</span>
                <span>Rp {{ number_format($transaction->change, 0, ',', '.') }}</span>
            </div>
        @endif
    </div>
    
    <div class="footer">
        <div>{{ $appSettings['receipt_footer'] ?? 'Terima kasih atas kunjungan Anda!' }}</div>
        <div>Barang yang sudah dibeli tidak dapat dikembalikan</div>
        <div style="margin-top: 10px;">
            <div>Powered by {{ $appSettings['app_name'] ?? config('app.name', 'POS System') }}</div>
            <div>{{ now()->format('d/m/Y H:i:s') }}</div>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
        
        // Close window after printing
        window.onafterprint = function() {
            window.close();
        }
    </script>
</body>
</html>