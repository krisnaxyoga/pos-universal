<x-store-layout>
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 bg-black bg-opacity-10"></div>
        <div class="absolute inset-0 opacity-20">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 30px 30px;"></div>
        </div>
        
        <!-- Content Container -->
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-32">
            <div class="text-center space-y-8">
                <!-- Main Heading -->
                <div class="space-y-4">
                    <h1 class="text-4xl md:text-6xl lg:text-7xl font-black leading-tight tracking-tight">
                        <span class="block text-white">Welcome to</span>
                        <span class="block bg-gradient-to-r from-yellow-300 to-orange-300 bg-clip-text text-transparent">
                            {{ $appSettings['app_name'] ?? 'Our Store' }}
                        </span>
                    </h1>
                    <p class="text-xl md:text-2xl lg:text-3xl text-blue-100 max-w-4xl mx-auto font-light leading-relaxed">
                        Discover premium products with unbeatable prices and lightning-fast delivery
                    </p>
                </div>
                
                <!-- Call to Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-6 justify-center items-center pt-4">
                    <a href="#products" class="group bg-white text-blue-700 px-10 py-5 rounded-2xl font-bold text-lg hover:bg-gray-50 transition-all duration-300 transform hover:scale-105 shadow-2xl hover:shadow-white/20">
                        <i class="fas fa-shopping-bag mr-3 group-hover:scale-110 transition-transform"></i>
                        Start Shopping
                    </a>
                    <a href="#categories" class="group border-3 border-white text-white px-10 py-5 rounded-2xl font-bold text-lg hover:bg-white hover:text-blue-700 transition-all duration-300 backdrop-blur-sm bg-white/10">
                        <i class="fas fa-th-large mr-3 group-hover:scale-110 transition-transform"></i>
                        Browse Categories
                    </a>
                </div>

                <!-- Trust Indicators -->
                <div class="flex flex-wrap justify-center items-center gap-8 pt-8 text-blue-200">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-shield-alt text-green-300"></i>
                        <span class="font-medium">Secure Payment</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-truck text-blue-300"></i>
                        <span class="font-medium">Fast Delivery</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-star text-yellow-300"></i>
                        <span class="font-medium">Quality Products</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Decorative Wave Bottom -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto">
                <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="white"/>
            </svg>
        </div>
    </section>

    <!-- Features Section -->
    <section class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center group">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-200 transition-colors">
                        <i class="fas fa-shipping-fast text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Fast Delivery</h3>
                    <p class="text-gray-600">Quick and reliable delivery to your doorstep</p>
                </div>
                <div class="text-center group">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-green-200 transition-colors">
                        <i class="fas fa-shield-alt text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Secure Payment</h3>
                    <p class="text-gray-600">Your payment information is safe and secure</p>
                </div>
                <div class="text-center group">
                    <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-purple-200 transition-colors">
                        <i class="fas fa-headset text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">24/7 Support</h3>
                    <p class="text-gray-600">Always here to help you with any questions</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="bg-gray-50 py-16" id="categories">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Shop by Category</h2>
                <p class="text-xl text-gray-600">Explore our diverse range of product categories</p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <a href="{{ route('store.index') }}" 
                   class="group bg-white rounded-2xl p-6 text-center shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 {{ !request('category') ? 'ring-2 ring-blue-500 bg-blue-50' : '' }}">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <i class="fas fa-th-large text-white text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">All Products</h3>
                    <p class="text-sm text-gray-500 mt-1">Browse Everything</p>
                </a>
                
                @foreach($categories as $category)
                    <a href="{{ route('store.index', ['category' => $category->id]) }}" 
                       class="group bg-white rounded-2xl p-6 text-center shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 {{ request('category') == $category->id ? 'ring-2 ring-blue-500 bg-blue-50' : '' }}">
                        <div class="w-16 h-16 bg-gradient-to-br from-gray-400 to-gray-500 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-cube text-white text-2xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">{{ $category->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $category->products->count() }} Products</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="bg-white py-16" id="products">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-12">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">
                        @if(request('category'))
                            {{ $categories->find(request('category'))->name ?? 'Products' }}
                        @else
                            Featured Products
                        @endif
                    </h2>
                    <p class="text-gray-600">Discover our best products with great quality and prices</p>
                </div>
                
                <!-- Sort and Filter Controls -->
                <div class="flex flex-col sm:flex-row gap-4 mt-6 lg:mt-0">
                    <select id="sortSelect" class="bg-white border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm min-w-0 sm:min-w-48">
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Sort by Name A-Z</option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            @if($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($products as $product)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 group transform hover:-translate-y-2 border border-gray-100">
                        <!-- Product Image -->
                        <div class="relative overflow-hidden bg-gradient-to-br from-gray-50 to-gray-100">
                            @if($product->image && Storage::disk('public')->exists($product->image))
                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" 
                                     class="w-full h-56 object-cover group-hover:scale-110 transition-transform duration-500">
                            @else
                                <div class="w-full h-56 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-4xl"></i>
                                </div>
                            @endif
                            
                            <!-- Stock Badge -->
                            @if($product->stock <= $product->min_stock)
                                <div class="absolute top-3 left-3">
                                    @if($product->stock == 0)
                                        <span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                            <i class="fas fa-times mr-1"></i>Out of Stock
                                        </span>
                                    @else
                                        <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                            <i class="fas fa-exclamation mr-1"></i>Low Stock
                                        </span>
                                    @endif
                                </div>
                            @endif
                            
                            <!-- Quick View Button -->
                            <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <a href="{{ route('store.product', $product) }}" 
                                   class="bg-white/90 backdrop-blur-sm text-gray-700 p-2 rounded-full shadow-lg hover:bg-white transition-colors">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="p-5">
                            <!-- Category Badge -->
                            <div class="mb-3">
                                <span class="inline-block bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full uppercase tracking-wide">
                                    {{ $product->category->name }}
                                </span>
                            </div>
                            
                            <!-- Product Name -->
                            <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 min-h-14">
                                <a href="{{ route('store.product', $product) }}" class="hover:text-blue-600 transition-colors">
                                    {{ $product->name }}
                                </a>
                            </h3>
                            
                            <!-- Description -->
                            @if($product->description)
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2 min-h-10">{{ $product->description }}</p>
                            @else
                                <div class="mb-4 min-h-10"></div>
                            @endif
                            
                            <!-- Price and Stock -->
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <span class="text-2xl font-bold text-blue-600">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm text-gray-500 block">Stock Available</span>
                                    <span class="text-lg font-semibold {{ $product->stock > 10 ? 'text-green-600' : ($product->stock > 0 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $product->stock }}
                                    </span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex space-x-2">
                                <button onclick="quickAddToCart({{ $product->id }})" 
                                        {{ $product->stock == 0 ? 'disabled' : '' }}
                                        class="flex-1 {{ $product->stock == 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800' }} text-white px-4 py-3 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg">
                                    @if($product->stock == 0)
                                        <i class="fas fa-ban mr-2"></i>Unavailable
                                    @else
                                        <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                                    @endif
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="mt-12 flex justify-center">
                        {{ $products->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-16">
                    <div class="bg-gray-100 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-search text-gray-400 text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4">No products found</h3>
                    <p class="text-gray-600 mb-8 max-w-md mx-auto">We couldn't find any products matching your criteria. Try adjusting your search or browse our categories.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('store.index') }}" class="bg-blue-600 text-white px-8 py-3 rounded-xl hover:bg-blue-700 transition-colors font-semibold">
                            View All Products
                        </a>
                        <a href="#categories" class="bg-gray-200 text-gray-700 px-8 py-3 rounded-xl hover:bg-gray-300 transition-colors font-semibold">
                            Browse Categories
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <script>
        // Sort functionality
        document.getElementById('sortSelect').addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('sort', this.value);
            window.location.href = url.toString();
        });

        // Quick add to cart
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