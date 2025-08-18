<x-store-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        <!-- Page Header -->
        <div class="mb-8 text-center lg:text-left">
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">Shopping Cart</h1>
            <p class="text-gray-600 text-lg">Review your items and proceed to checkout</p>
        </div>

        @if(count($cartItems) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
                        <div class="px-6 py-5 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-bold text-gray-900">Your Items</h2>
                                <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-semibold">{{ count($cartItems) }} {{ count($cartItems) == 1 ? 'Item' : 'Items' }}</span>
                            </div>
                        </div>
                        
                        <div class="divide-y divide-gray-100">
                            @foreach($cartItems as $item)
                                <div class="p-4 lg:p-6 hover:bg-gray-50 transition-colors" data-product-id="{{ $item['id'] }}">
                                    <div class="flex flex-col sm:flex-row sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
                                        <!-- Product Image -->
                                        <div class="flex-shrink-0 self-start">
                                            @if($item['image'] && Storage::disk('public')->exists($item['image']))
                                                <img src="{{ Storage::url($item['image']) }}" alt="{{ $item['name'] }}" 
                                                     class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-xl shadow-md">
                                            @else
                                                <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl flex items-center justify-center shadow-md">
                                                    <i class="fas fa-image text-gray-400 text-xl"></i>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Product Details -->
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-lg lg:text-xl font-bold text-gray-900 mb-2">{{ $item['name'] }}</h3>
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                                                <div>
                                                    <p class="text-xl font-bold text-blue-600">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                                    <p class="text-sm text-gray-500">Stock: {{ $item['stock'] }} units</p>
                                                </div>
                                                
                                                <!-- Mobile: Stack quantity and total -->
                                                <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 sm:space-x-6">
                                                    <!-- Quantity Controls -->
                                                    <div class="flex items-center justify-center space-x-3">
                                                        <button onclick="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})" 
                                                                class="w-8 h-8 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg flex items-center justify-center transition-colors">
                                                            <i class="fas fa-minus text-sm"></i>
                                                        </button>
                                                        <input type="number" 
                                                               value="{{ $item['quantity'] }}" 
                                                               min="1" max="{{ $item['stock'] }}"
                                                               onchange="updateQuantity({{ $item['id'] }}, this.value)"
                                                               class="w-16 text-center border-2 border-gray-300 rounded-lg px-2 py-1 font-semibold focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                                        <button onclick="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})" 
                                                                class="w-8 h-8 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg flex items-center justify-center transition-colors">
                                                            <i class="fas fa-plus text-sm"></i>
                                                        </button>
                                                    </div>

                                                    <!-- Subtotal & Remove -->
                                                    <div class="text-center sm:text-right">
                                                        <p class="text-xl font-bold text-gray-900 mb-1">
                                                            Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                                        </p>
                                                        <button onclick="removeFromCart({{ $item['id'] }})" 
                                                                class="text-red-600 hover:text-red-800 text-sm font-medium hover:bg-red-50 px-2 py-1 rounded transition-colors">
                                                            <i class="fas fa-trash mr-1"></i>Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Continue Shopping -->
                    <div class="mt-6">
                        <a href="{{ route('store.index') }}" 
                           class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Continue Shopping
                        </a>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal ({{ count($cartItems) }} items)</span>
                                <span class="font-medium">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shipping</span>
                                <span class="font-medium text-green-600">Free</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tax</span>
                                <span class="font-medium">Rp 0</span>
                            </div>
                            
                            <div class="border-t pt-3">
                                <div class="flex justify-between">
                                    <span class="text-lg font-semibold text-gray-900">Total</span>
                                    <span class="text-lg font-bold text-blue-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <a href="{{ route('store.checkout') }}" 
                               class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-semibold text-center block transition-colors">
                                Proceed to Checkout
                            </a>
                        </div>

                        <!-- Trust Badges -->
                        <div class="mt-6 pt-6 border-t">
                            <div class="grid grid-cols-2 gap-4 text-center">
                                <div class="text-green-600">
                                    <i class="fas fa-shield-alt text-2xl mb-2"></i>
                                    <p class="text-xs text-gray-600">Secure Payment</p>
                                </div>
                                <div class="text-blue-600">
                                    <i class="fas fa-truck text-2xl mb-2"></i>
                                    <p class="text-xs text-gray-600">Fast Delivery</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty Cart -->
            <div class="text-center py-16">
                <div class="max-w-lg mx-auto">
                    <div class="bg-gradient-to-br from-blue-100 to-indigo-100 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-8">
                        <i class="fas fa-shopping-cart text-blue-600 text-5xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Your cart is empty</h2>
                    <p class="text-gray-600 mb-8 text-lg leading-relaxed">Discover amazing products and start building your perfect order. Your next favorite item is just a click away!</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('store.index') }}" 
                           class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white py-4 px-8 rounded-xl font-bold text-lg transition-all transform hover:scale-105 shadow-lg">
                            <i class="fas fa-shopping-bag mr-2"></i>Start Shopping
                        </a>
                        <a href="{{ route('store.index') }}#categories" 
                           class="bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 hover:border-blue-300 py-4 px-8 rounded-xl font-bold text-lg transition-all shadow-lg">
                            <i class="fas fa-th-large mr-2"></i>Browse Categories
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        // Update quantity
        function updateQuantity(productId, quantity) {
            quantity = parseInt(quantity);
            
            if (quantity < 0) {
                quantity = 0;
            }
            
            fetch('/store/update-cart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Reload to update totals
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while updating cart', 'error');
            });
        }

        // Remove from cart
        function removeFromCart(productId) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                fetch('/store/remove-from-cart', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        location.reload();
                    } else {
                        showToast('Failed to remove item from cart', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while removing item', 'error');
                });
            }
        }
    </script>
</x-store-layout>