<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-red-50 to-orange-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden">
            <!-- Header with animated error icon -->
            <div class="bg-gradient-to-r from-red-500 to-red-600 p-8 text-center">
                <div class="animate-pulse">
                    <div class="bg-white rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-times text-red-500 text-3xl"></i>
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Payment Failed</h1>
                <p class="text-red-100">Your transaction could not be completed</p>
            </div>

            <!-- Error Details -->
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
                            <span class="font-medium text-red-600 capitalize">{{ $transaction->status }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Date:</span>
                            <span class="font-medium">{{ $transaction->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Error Message -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 mr-3"></i>
                        <div class="text-sm text-red-800">
                            <p class="font-medium mb-1">Payment Could Not Be Processed</p>
                            <p>{{ $message ?? 'Your payment was declined or cancelled. Please try again with a different payment method.' }}</p>
                            
                            @if(isset($error_reason))
                            <p class="mt-2 text-red-700">
                                <strong>Reason:</strong> {{ $error_reason }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Possible Reasons -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="font-medium text-yellow-800 mb-2">Possible Reasons:</h4>
                    <ul class="text-sm text-yellow-700 space-y-1">
                        <li class="flex items-start">
                            <i class="fas fa-circle text-xs mt-2 mr-2"></i>
                            <span>Insufficient balance or credit limit</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-circle text-xs mt-2 mr-2"></i>
                            <span>Payment was cancelled by user</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-circle text-xs mt-2 mr-2"></i>
                            <span>Network or technical issue</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-circle text-xs mt-2 mr-2"></i>
                            <span>Payment method temporarily unavailable</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3">
                    @if(isset($transaction) && $transaction->status !== 'cancelled')
                    <a href="{{ route('pos.index') }}?retry={{ $transaction->id }}" 
                       class="block w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-lg text-center transition-colors">
                        <i class="fas fa-redo mr-2"></i>
                        Try Again
                    </a>
                    @endif
                    
                    <a href="{{ route('pos.index') }}" 
                       class="block w-full bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-4 rounded-lg text-center transition-colors">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Make New Order
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

                <!-- Help Section -->
                <div class="pt-4 border-t">
                    <h4 class="font-medium text-gray-800 mb-2">Need Help?</h4>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p>If you continue to experience issues, please:</p>
                        <ul class="ml-4 space-y-1">
                            <li>• Contact our customer support</li>
                            <li>• Try a different payment method</li>
                            <li>• Check your account balance</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto redirect to POS after 60 seconds
        setTimeout(() => {
            if (confirm('Would you like to try making a new order?')) {
                window.location.href = '{{ route('pos.index') }}';
            }
        }, 60000);
    </script>
</body>
</html>