<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-yellow-50 to-orange-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden">
            <!-- Header with warning icon -->
            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-8 text-center">
                <div class="animate-bounce">
                    <div class="bg-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation text-yellow-500 text-3xl"></i>
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Payment Cancelled</h1>
                <p class="text-yellow-100">Your transaction was cancelled</p>
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
                            <span class="font-medium text-gray-800">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="font-medium text-yellow-600 capitalize">{{ $transaction->status }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Date:</span>
                            <span class="font-medium">{{ $transaction->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Items List (if available) -->
                @if($transaction->items && $transaction->items->count() > 0)
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-3">Items (Not Purchased)</h3>
                    <div class="space-y-2">
                        @foreach($transaction->items as $item)
                        <div class="flex justify-between items-center text-sm">
                            <div class="flex-1">
                                <span class="font-medium text-gray-600">{{ $item->product_name }}</span>
                                <span class="text-gray-500 ml-2">x{{ $item->quantity }}</span>
                            </div>
                            <span class="font-medium text-gray-600">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                @endif

                <!-- Cancellation Message -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-yellow-500 mt-0.5 mr-3"></i>
                        <div class="text-sm text-yellow-800">
                            <p class="font-medium mb-1">Transaction Cancelled</p>
                            <p>{{ $message ?? 'You have cancelled the payment process. No charges have been made to your account.' }}</p>
                        </div>
                    </div>
                </div>

                <!-- What Happens Next -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-medium text-blue-800 mb-2">What happens next?</h4>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li class="flex items-start">
                            <i class="fas fa-check text-xs mt-2 mr-2"></i>
                            <span>No charges have been made</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-xs mt-2 mr-2"></i>
                            <span>Your cart items are still available</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-xs mt-2 mr-2"></i>
                            <span>Product stock has been restored</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-xs mt-2 mr-2"></i>
                            <span>You can try payment again anytime</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    @if(isset($transaction))
                    <a href="{{ route('pos.index') }}?retry={{ $transaction->id }}" 
                       class="block w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-lg text-center transition-colors">
                        <i class="fas fa-credit-card mr-2"></i>
                        Try Payment Again
                    </a>
                    @endif
                    
                    <a href="{{ route('pos.index') }}" 
                       class="block w-full bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-4 rounded-lg text-center transition-colors">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Continue Shopping
                    </a>
                    
                    @if(isset($transaction))
                    <a href="{{ route('transactions.show', $transaction->id) }}" 
                       class="block w-full border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-3 px-4 rounded-lg text-center transition-colors">
                        <i class="fas fa-eye mr-2"></i>
                        View Transaction Details
                    </a>
                    @endif
                    
                    <a href="{{ route('dashboard') }}" 
                       class="block w-full border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-3 px-4 rounded-lg text-center transition-colors">
                        <i class="fas fa-home mr-2"></i>
                        Back to Dashboard
                    </a>
                </div>

                <!-- Note -->
                <div class="pt-4 border-t">
                    <div class="text-center text-sm text-gray-500">
                        <p>Need help? Contact our customer support team.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto redirect to POS after 45 seconds
        setTimeout(() => {
            if (confirm('Continue shopping?')) {
                window.location.href = '{{ route('pos.index') }}';
            }
        }, 45000);
    </script>
</body>
</html>