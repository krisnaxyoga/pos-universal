<x-store-layout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Success Header -->
        <div class="text-center mb-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <i class="fas fa-check text-green-600 text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Order Placed Successfully!</h1>
            <p class="text-gray-600 mt-2">Thank you for your purchase. Your order has been received and is being processed.</p>
        </div>

        <!-- Order Details -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Order Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Order Details</h2>
                        <p class="text-sm text-gray-600">Order #{{ $transaction->transaction_number }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Order Date</p>
                        <p class="font-medium">{{ $transaction->created_at->format('M d, Y - H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-md font-semibold text-gray-900 mb-3">Customer Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Name</p>
                        <p class="font-medium">{{ $transaction->customer_info['name'] }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="font-medium">{{ $transaction->customer_info['email'] }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Phone</p>
                        <p class="font-medium">{{ $transaction->customer_info['phone'] }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Payment Method</p>
                        <p class="font-medium capitalize">
                            @switch($transaction->payment_method)
                                @case('card') Debit/Credit Card @break
                                @case('ewallet') E-Wallet @break
                                @case('online') Bank Transfer @break
                                @case('qris') QRIS @break
                                @default {{ ucfirst($transaction->payment_method) }}
                            @endswitch
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-600">Delivery Address</p>
                    <p class="font-medium">{{ $transaction->customer_info['address'] }}</p>
                </div>
            </div>

            <!-- Order Items -->
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-md font-semibold text-gray-900 mb-3">Order Items</h3>
                <div class="space-y-4">
                    @foreach($transaction->items as $item)
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                @if($item->product && $item->product->image && Storage::disk('public')->exists($item->product->image))
                                    <img src="{{ Storage::url($item->product->image) }}" alt="{{ $item->product_name }}" 
                                         class="w-16 h-16 object-cover rounded-lg">
                                @else
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">{{ $item->product_name }}</h4>
                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                    <span>Qty: {{ $item->quantity }}</span>
                                    <span>Price: Rp {{ number_format($item->product_price, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-gray-900">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Totals -->
            <div class="px-6 py-4 bg-gray-50">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                    </div>
                    
                    @if($transaction->discount > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Discount</span>
                            <span class="font-medium text-green-600">-Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    
                    @if($transaction->tax > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax</span>
                            <span class="font-medium">Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Shipping</span>
                        <span class="font-medium text-green-600">Free</span>
                    </div>
                    
                    <div class="border-t pt-2">
                        <div class="flex justify-between">
                            <span class="text-lg font-semibold text-gray-900">Total</span>
                            <span class="text-lg font-bold text-blue-600">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Status -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Status</h3>
            <div class="flex items-center">
                <div class="flex items-center justify-center h-10 w-10 rounded-full {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600' }}">
                    @if($transaction->status === 'completed')
                        <i class="fas fa-check"></i>
                    @else
                        <i class="fas fa-clock"></i>
                    @endif
                </div>
                <div class="ml-4">
                    <p class="font-medium text-gray-900 capitalize">{{ $transaction->status }}</p>
                    <p class="text-sm text-gray-600">
                        @if($transaction->status === 'completed')
                            Your order has been confirmed and will be processed for delivery.
                        @elseif($transaction->status === 'pending')
                            Your order is pending payment confirmation.
                        @else
                            Order status: {{ $transaction->status }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- What's Next -->
        <div class="mt-8 bg-blue-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-4">What happens next?</h3>
            <div class="space-y-3 text-blue-800">
                @if($transaction->status === 'pending')
                    <div class="flex items-start">
                        <i class="fas fa-credit-card text-blue-600 mt-1 mr-3"></i>
                        <p>Complete your payment using your selected method. Payment instructions will be sent to your email.</p>
                    </div>
                @endif
                
                <div class="flex items-start">
                    <i class="fas fa-envelope text-blue-600 mt-1 mr-3"></i>
                    <p>You'll receive an order confirmation email with tracking information.</p>
                </div>
                
                <div class="flex items-start">
                    <i class="fas fa-truck text-blue-600 mt-1 mr-3"></i>
                    <p>We'll notify you when your order is out for delivery.</p>
                </div>
                
                <div class="flex items-start">
                    <i class="fas fa-headset text-blue-600 mt-1 mr-3"></i>
                    <p>Contact our customer service if you have any questions about your order.</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('store.index') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white py-3 px-8 rounded-lg font-semibold text-center transition-colors">
                Continue Shopping
            </a>
            
            <button onclick="window.print()" 
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 px-8 rounded-lg font-semibold transition-colors">
                <i class="fas fa-print mr-2"></i>Print Order
            </button>
        </div>
    </div>

    <!-- Print Styles -->
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                background: white !important;
            }
            
            .shadow-md {
                box-shadow: none !important;
                border: 1px solid #e5e7eb !important;
            }
        }
    </style>
</x-store-layout>