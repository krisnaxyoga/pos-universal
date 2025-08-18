<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden">
            <!-- Header with animated checkmark -->
            <div class="bg-gradient-to-r from-green-500 to-green-600 p-8 text-center">
                <div class="animate-bounce">
                    <div class="bg-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check text-green-500 text-3xl"></i>
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Payment Successful!</h1>
                <p class="text-green-100">Your transaction has been completed</p>
            </div>

            <!-- Transaction Details -->
            <div class="p-6 space-y-4">
                @if(isset($transaction))
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-3">Transaction Details</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Transaction ID:</span>
                            <span class="font-medium">{{ $transaction->transaction_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Amount:</span>
                            <span class="font-medium text-green-600">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Method:</span>
                            <span class="font-medium capitalize">{{ str_replace('_', ' ', $transaction->payment_method) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Date:</span>
                            <span class="font-medium">{{ $transaction->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        @if($transaction->ipaymu_paid_at)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Paid At:</span>
                            <span class="font-medium">{{ \Carbon\Carbon::parse($transaction->ipaymu_paid_at)->format('d M Y, H:i') }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Items List -->
                @if($transaction->items && $transaction->items->count() > 0)
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-3">Items Purchased</h3>
                    <div class="space-y-2">
                        @foreach($transaction->items as $item)
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex-1">
                                <span class="font-medium">{{ $item->product_name }}</span>
                                <span class="text-gray-500 ml-2">x{{ $item->quantity }}</span>
                            </div>
                            <span class="font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                @endif

                <!-- Success Message -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-green-500 mt-0.5 mr-3"></i>
                        <div class="text-sm text-green-800">
                            <p class="font-medium mb-1">Payment Confirmed</p>
                            <p>{{ $message ?? 'Your payment has been successfully processed. Thank you for your purchase!' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    @if(isset($transaction))
                    <a href="{{ route('transactions.show', $transaction->id) }}" 
                       class="block w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-lg text-center transition-colors">
                        <i class="fas fa-receipt mr-2"></i>
                        View Receipt
                    </a>
                    @endif
                    
                    <a href="{{ route('dashboard') }}" 
                       class="block w-full bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-4 rounded-lg text-center transition-colors">
                        <i class="fas fa-home mr-2"></i>
                        Back to Dashboard
                    </a>
                    
                    <a href="{{ route('pos.index') }}" 
                       class="block w-full border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-3 px-4 rounded-lg text-center transition-colors">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Continue Shopping
                    </a>
                </div>

                <!-- Print Receipt Button -->
                @if(isset($transaction))
                <div class="pt-4 border-t">
                    <button onclick="printReceipt()" 
                            class="w-full text-gray-600 hover:text-gray-800 font-medium py-2 text-sm transition-colors">
                        <i class="fas fa-print mr-2"></i>
                        Print Receipt
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if(isset($transaction))
    <script>
        function printReceipt() {
            const receiptUrl = `{{ route('pos.receipt', ':id') }}`.replace(':id', '{{ $transaction->id }}');
            window.open(receiptUrl, '_blank', 'width=800,height=600');
        }
        
        // Auto redirect after 30 seconds
        setTimeout(() => {
            if (confirm('Redirect to dashboard?')) {
                window.location.href = '{{ route('dashboard') }}';
            }
        }, 30000);
    </script>
    @endif
</body>
</html>