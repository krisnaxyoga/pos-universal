<x-store-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
            <p class="text-gray-600 mt-2">Complete your order information</p>
        </div>

        <form method="POST" action="{{ route('store.process-order') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            @csrf
            
            <!-- Customer Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Customer Details -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Full Name *
                            </label>
                            <input type="text" id="customer_name" name="customer_name" required
                                   value="{{ old('customer_name') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('customer_name')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email Address *
                            </label>
                            <input type="email" id="customer_email" name="customer_email" required
                                   value="{{ old('customer_email') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('customer_email')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1">
                                Phone Number *
                            </label>
                            <input type="tel" id="customer_phone" name="customer_phone" required
                                   value="{{ old('customer_phone') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('customer_phone')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="customer_address" class="block text-sm font-medium text-gray-700 mb-1">
                                Delivery Address *
                            </label>
                            <textarea id="customer_address" name="customer_address" rows="3" required
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('customer_address') }}</textarea>
                            @error('customer_address')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl shadow-lg p-8 border border-blue-200">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                            <i class="fas fa-credit-card text-white text-3xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Secure Payment with iPaymu</h2>
                        <p class="text-gray-600 mb-6 text-lg leading-relaxed">Your order will be processed securely through iPaymu payment gateway. You can pay using various methods including credit cards, e-wallets, bank transfers, and QRIS.</p>
                        
                        <!-- Payment Methods Icons -->
                        <div class="flex justify-center items-center space-x-6 mb-6">
                            <div class="flex flex-col items-center group">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-md flex items-center justify-center group-hover:shadow-lg transition-shadow">
                                    <i class="fas fa-credit-card text-blue-600 text-xl"></i>
                                </div>
                                <span class="text-xs text-gray-600 mt-2 font-medium">Cards</span>
                            </div>
                            <div class="flex flex-col items-center group">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-md flex items-center justify-center group-hover:shadow-lg transition-shadow">
                                    <i class="fas fa-mobile-alt text-purple-600 text-xl"></i>
                                </div>
                                <span class="text-xs text-gray-600 mt-2 font-medium">E-Wallet</span>
                            </div>
                            <div class="flex flex-col items-center group">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-md flex items-center justify-center group-hover:shadow-lg transition-shadow">
                                    <i class="fas fa-university text-indigo-600 text-xl"></i>
                                </div>
                                <span class="text-xs text-gray-600 mt-2 font-medium">Bank</span>
                            </div>
                            <div class="flex flex-col items-center group">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-md flex items-center justify-center group-hover:shadow-lg transition-shadow">
                                    <i class="fas fa-qrcode text-green-600 text-xl"></i>
                                </div>
                                <span class="text-xs text-gray-600 mt-2 font-medium">QRIS</span>
                            </div>
                        </div>
                        
                        <!-- Security Features -->
                        <div class="bg-white rounded-xl p-4 shadow-sm">
                            <div class="flex items-center justify-center space-x-8 text-sm">
                                <div class="flex items-center text-green-600">
                                    <i class="fas fa-shield-alt mr-2"></i>
                                    <span class="font-medium">SSL Encrypted</span>
                                </div>
                                <div class="flex items-center text-blue-600">
                                    <i class="fas fa-lock mr-2"></i>
                                    <span class="font-medium">Secure Payment</span>
                                </div>
                                <div class="flex items-center text-purple-600">
                                    <i class="fas fa-certificate mr-2"></i>
                                    <span class="font-medium">Verified</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                    
                    <!-- Cart Items -->
                    <div class="space-y-3 mb-6">
                        @foreach($cartItems as $item)
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    @if($item['image'] && Storage::disk('public')->exists($item['image']))
                                        <img src="{{ Storage::url($item['image']) }}" alt="{{ $item['name'] }}" 
                                             class="w-12 h-12 object-cover rounded">
                                    @else
                                        <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400 text-sm"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ $item['name'] }}</h4>
                                    <p class="text-sm text-gray-500">Qty: {{ $item['quantity'] }}</p>
                                </div>
                                <div class="text-sm font-medium text-gray-900">
                                    Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Totals -->
                    <div class="space-y-3 border-t pt-4">
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

                    <!-- Place Order Button -->
                    <div class="mt-6">
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-4 px-6 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                            <i class="fas fa-lock mr-2"></i>Pay with iPaymu
                        </button>
                        <p class="text-center text-sm text-gray-500 mt-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            You will be redirected to iPaymu for secure payment
                        </p>
                    </div>

                    <!-- Back to Cart -->
                    <div class="mt-4">
                        <a href="{{ route('store.cart') }}" 
                           class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-6 rounded-lg font-medium text-center block transition-colors">
                            Back to Cart
                        </a>
                    </div>

                    <!-- Security Notice -->
                    <div class="mt-6 pt-6 border-t">
                        <div class="flex items-center justify-center text-green-600">
                            <i class="fas fa-shield-alt mr-2"></i>
                            <span class="text-sm">Secure & Encrypted</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            // Add loading state to button
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            
            // Re-enable button after 10 seconds in case of error
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 10000);
        });
    </script>
</x-store-layout>