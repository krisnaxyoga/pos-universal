<x-store-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('store.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                        <a href="{{ route('store.index', ['category' => $product->category_id]) }}" 
                           class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600">
                            {{ $product->category->name }}
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                        <span class="ml-1 text-sm font-medium text-gray-500">{{ $product->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16">
            <!-- Product Image -->
            <div class="space-y-4">
                <div class="relative bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl overflow-hidden shadow-2xl group">
                    @if($product->image && Storage::disk('public')->exists($product->image))
                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" 
                             class="w-full h-80 md:h-96 lg:h-[500px] object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-80 md:h-96 lg:h-[500px] bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-6xl"></i>
                        </div>
                    @endif
                    
                    <!-- Stock Badge -->
                    @if($product->stock <= $product->min_stock)
                        <div class="absolute top-4 left-4">
                            @if($product->stock == 0)
                                <span class="bg-red-500 text-white px-4 py-2 rounded-full text-sm font-bold shadow-lg">
                                    <i class="fas fa-times mr-2"></i>Out of Stock
                                </span>
                            @else
                                <span class="bg-yellow-500 text-white px-4 py-2 rounded-full text-sm font-bold shadow-lg">
                                    <i class="fas fa-exclamation mr-2"></i>Low Stock
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
                
                <!-- Image Gallery Thumbnails (placeholder for future enhancement) -->
                <div class="hidden md:flex space-x-2">
                    <div class="w-16 h-16 bg-gray-200 rounded-lg border-2 border-blue-500"></div>
                    <div class="w-16 h-16 bg-gray-100 rounded-lg border border-gray-200"></div>
                    <div class="w-16 h-16 bg-gray-100 rounded-lg border border-gray-200"></div>
                </div>
            </div>

            <!-- Product Details -->
            <div class="space-y-8">
                <!-- Header Section -->
                <div class="space-y-4">
                    <!-- Category Badge -->
                    <div>
                        <span class="inline-flex items-center bg-gradient-to-r from-blue-100 to-indigo-100 text-blue-800 text-sm font-medium px-4 py-2 rounded-full">
                            <i class="fas fa-tag mr-2"></i>{{ $product->category->name }}
                        </span>
                    </div>

                    <!-- Product Name -->
                    <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-gray-900 leading-tight">{{ $product->name }}</h1>

                    <!-- Product Meta -->
                    <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">SKU:</span>
                                <span class="font-mono font-medium ml-2">{{ $product->sku }}</span>
                            </div>
                            @if($product->barcode)
                                <div>
                                    <span class="text-gray-500">Barcode:</span>
                                    <span class="font-mono font-medium ml-2">{{ $product->barcode }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Pricing Section -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Price</p>
                            <div class="text-3xl md:text-4xl font-bold text-blue-600">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600 mb-1">Availability</p>
                            <div class="flex items-center space-x-2">
                                <span class="text-xl font-bold {{ $product->stock > $product->min_stock ? 'text-green-600' : ($product->stock > 0 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $product->stock }}
                                </span>
                                <span class="text-gray-500">units</span>
                            </div>
                            @if($product->stock <= $product->min_stock && $product->stock > 0)
                                <span class="inline-block bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded-full mt-1">
                                    Low Stock
                                </span>
                            @elseif($product->stock == 0)
                                <span class="inline-block bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded-full mt-1">
                                    Out of Stock
                                </span>
                            @else
                                <span class="inline-block bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full mt-1">
                                    In Stock
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Description -->
                @if($product->description)
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                        <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>Description
                        </h3>
                        <p class="text-gray-700 leading-relaxed text-lg">{{ $product->description }}</p>
                    </div>
                @endif

                <!-- Add to Cart Form -->
                <div class="bg-gradient-to-r from-white to-gray-50 p-8 rounded-2xl border border-gray-200 shadow-lg">
                    <form id="addToCartForm" class="space-y-6">
                        @csrf
                        <div>
                            <label for="quantity" class="block text-lg font-semibold text-gray-900 mb-4">
                                <i class="fas fa-shopping-cart text-blue-600 mr-2"></i>Select Quantity
                            </label>
                            <div class="flex items-center justify-center space-x-4 mb-4">
                                <button type="button" onclick="decreaseQuantity()" 
                                        class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 w-12 h-12 rounded-xl flex items-center justify-center transition-all shadow-sm hover:shadow-md">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ $product->stock }}"
                                       class="w-24 h-12 text-center text-xl font-bold border-2 border-blue-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-blue-50">
                                <button type="button" onclick="increaseQuantity()" 
                                        class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 w-12 h-12 rounded-xl flex items-center justify-center transition-all shadow-sm hover:shadow-md">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <p class="text-center text-sm text-gray-600">Available: <span class="font-semibold">{{ $product->stock }}</span> units</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <button type="submit" 
                                    {{ $product->stock == 0 ? 'disabled' : '' }}
                                    class="w-full {{ $product->stock == 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800' }} text-white px-6 py-4 rounded-xl font-bold text-lg transition-all transform hover:scale-105 shadow-lg">
                                @if($product->stock == 0)
                                    <i class="fas fa-ban mr-2"></i>Out of Stock
                                @else
                                    <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                                @endif
                            </button>
                            <a href="{{ route('store.cart') }}" 
                               class="w-full bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 hover:border-blue-300 px-6 py-4 rounded-xl font-bold text-lg transition-all text-center shadow-lg">
                                <i class="fas fa-shopping-cart mr-2"></i>View Cart
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Additional Info -->
                <div class="border-t pt-6">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Category:</span>
                            <span class="font-medium ml-2">{{ $product->category->name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Availability:</span>
                            <span class="font-medium ml-2 {{ $product->stock > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $product->stock > 0 ? 'In Stock' : 'Out of Stock' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        @if($relatedProducts->count() > 0)
            <div class="mt-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-8">Related Products</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $relatedProduct)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="aspect-w-1 aspect-h-1 bg-gray-200">
                                @if($relatedProduct->image && Storage::disk('public')->exists($relatedProduct->image))
                                    <img src="{{ Storage::url($relatedProduct->image) }}" alt="{{ $relatedProduct->name }}" 
                                         class="w-full h-48 object-cover">
                                @else
                                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400 text-2xl"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                                    <a href="{{ route('store.product', $relatedProduct) }}" class="hover:text-blue-600 transition-colors">
                                        {{ $relatedProduct->name }}
                                    </a>
                                </h3>
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-blue-600">
                                        Rp {{ number_format($relatedProduct->price, 0, ',', '.') }}
                                    </span>
                                    <button onclick="quickAddToCart({{ $relatedProduct->id }})" 
                                            {{ $relatedProduct->stock == 0 ? 'disabled' : '' }}
                                            class="text-sm {{ $relatedProduct->stock == 0 ? 'text-gray-400' : 'text-blue-600 hover:text-blue-800' }} transition-colors">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <script>
        const maxStock = {{ $product->stock }};

        // Quantity controls
        function increaseQuantity() {
            const quantityInput = document.getElementById('quantity');
            let currentValue = parseInt(quantityInput.value);
            if (currentValue < maxStock) {
                quantityInput.value = currentValue + 1;
            }
        }

        function decreaseQuantity() {
            const quantityInput = document.getElementById('quantity');
            let currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        }

        // Add to cart form submission
        document.getElementById('addToCartForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const quantity = document.getElementById('quantity').value;
            
            fetch(`/store/add-to-cart/{{ $product->id }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ quantity: parseInt(quantity) })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    updateCartCount(data.cart_count);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while adding to cart', 'error');
            });
        });

        // Quick add to cart for related products
        function quickAddToCart(productId) {
            fetch(`/store/add-to-cart/${productId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ quantity: 1 })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    updateCartCount(data.cart_count);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while adding to cart', 'error');
            });
        }

        // Update cart count in navigation
        function updateCartCount(count) {
            const cartIcon = document.querySelector('a[href="{{ route("store.cart") }}"]');
            const existingBadge = cartIcon.querySelector('.absolute');
            
            if (count > 0) {
                if (existingBadge) {
                    existingBadge.textContent = count;
                } else {
                    const badge = document.createElement('span');
                    badge.className = 'absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center';
                    badge.textContent = count;
                    cartIcon.appendChild(badge);
                }
            } else {
                if (existingBadge) {
                    existingBadge.remove();
                }
            }
        }
    </script>

    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</x-store-layout>