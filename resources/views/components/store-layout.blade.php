<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $appSettings['app_name'] ?? config('app.name', 'Online Store') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white/95 backdrop-blur-md shadow-xl sticky top-0 z-50 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-18 lg:h-22">
                <!-- Logo & Brand -->
                <div class="flex items-center flex-shrink-0">
                    <a href="{{ route('store.index') }}" class="flex items-center space-x-4 group py-2">
                        @if(isset($appSettings['app_logo']) && $appSettings['app_logo'] && Storage::disk('public')->exists($appSettings['app_logo']))
                            <div class="relative">
                                <img src="{{ Storage::url($appSettings['app_logo']) }}" alt="{{ $appSettings['app_name'] ?? 'Store' }}" class="h-12 w-12 lg:h-14 lg:w-14 object-contain transition-all duration-300 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-indigo-600/20 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            </div>
                        @else
                            <div class="h-12 w-12 lg:h-14 lg:w-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-110">
                                <i class="fas fa-store text-white text-xl lg:text-2xl"></i>
                            </div>
                        @endif
                        <div class="hidden sm:block">
                            <h1 class="text-xl lg:text-2xl font-black text-gray-900 group-hover:text-blue-600 transition-colors duration-300 leading-tight">
                                {{ $appSettings['app_name'] ?? config('app.name', 'Online Store') }}
                            </h1>
                            <p class="text-sm text-gray-500 font-medium tracking-wide">Premium Online Store</p>
                        </div>
                    </a>
                </div>

                <!-- Search Bar - Desktop -->
                <div class="hidden lg:flex items-center flex-1 max-w-2xl mx-8">
                    <form method="GET" action="{{ route('store.index') }}" class="w-full">
                        <div class="relative group">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Search for amazing products..." 
                                   class="w-full pl-14 pr-14 py-4 border-2 border-gray-300 rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 shadow-lg bg-gray-50 focus:bg-white transition-all duration-300 text-lg font-medium placeholder-gray-400">
                            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400 text-lg group-focus-within:text-blue-500 transition-colors"></i>
                            </div>
                            <button type="submit" class="absolute inset-y-0 right-0 pr-5 flex items-center text-gray-400 hover:text-blue-600 transition-colors duration-300">
                                <i class="fas fa-arrow-right text-lg"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Cart & Actions -->
                <div class="flex items-center space-x-3 lg:space-x-6">
                    <!-- Search Button - Mobile -->
                    <button id="mobileSearchBtn" class="lg:hidden p-3 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all duration-300">
                        <i class="fas fa-search text-xl"></i>
                    </button>
                    
                    <!-- Cart -->
                    <a href="{{ route('store.cart') }}" class="relative group">
                        <div class="flex flex-col items-center p-3 lg:p-4 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all duration-300">
                            <div class="relative">
                                <i class="fas fa-shopping-cart text-xl lg:text-2xl"></i>
                                @if(isset($cartCount) && $cartCount > 0)
                                    <span class="absolute -top-2 -right-2 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center animate-bounce shadow-lg">
                                        {{ $cartCount }}
                                    </span>
                                @endif
                            </div>
                            <span class="hidden lg:block text-sm font-medium mt-1 group-hover:text-blue-600 transition-colors">Cart</span>
                        </div>
                    </a>
                    
                    <!-- Mobile Menu Button -->
                    <button id="mobileMenuBtn" class="lg:hidden p-3 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all duration-300">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <!-- Desktop Menu -->
                    <div class="hidden lg:flex items-center space-x-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 px-4 py-3 rounded-xl transition-all duration-300 font-medium">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="flex items-center space-x-2 text-gray-600 hover:text-red-600 hover:bg-red-50 px-4 py-3 rounded-xl transition-all duration-300 font-medium">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="flex items-center space-x-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-xl transition-all duration-300 font-bold shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>Admin Login</span>
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Search (Hidden by default) -->
        <div id="mobileSearch" class="lg:hidden px-6 pb-6 hidden bg-gradient-to-r from-blue-50 to-indigo-50">
            <form method="GET" action="{{ route('store.index') }}">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search for amazing products..." 
                           class="w-full pl-12 pr-4 py-4 border-2 border-gray-300 rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 shadow-lg bg-white transition-all text-lg font-medium">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-lg"></i>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Mobile Menu (Hidden by default) -->
        <div id="mobileMenu" class="lg:hidden border-t border-gray-200 bg-white/95 backdrop-blur-md hidden">
            <div class="px-6 py-4 space-y-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-4 text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-4 py-4 rounded-xl transition-all font-medium">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-tachometer-alt text-white"></i>
                        </div>
                        <span class="text-lg">Dashboard</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="block">
                        @csrf
                        <button type="submit" class="w-full flex items-center space-x-4 text-gray-700 hover:text-red-600 hover:bg-red-50 px-4 py-4 rounded-xl transition-all font-medium">
                            <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl flex items-center justify-center">
                                <i class="fas fa-sign-out-alt text-white"></i>
                            </div>
                            <span class="text-lg">Logout</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="flex items-center space-x-4 text-gray-700 hover:text-blue-600 hover:bg-blue-50 px-4 py-4 rounded-xl transition-all font-medium">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-sign-in-alt text-white"></i>
                        </div>
                        <span class="text-lg">Admin Login</span>
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        @if(isset($appSettings['app_logo']) && $appSettings['app_logo'] && Storage::disk('public')->exists($appSettings['app_logo']))
                            <img src="{{ Storage::url($appSettings['app_logo']) }}" alt="{{ $appSettings['app_name'] ?? 'Store' }}" class="h-8 w-8 object-contain">
                        @else
                            <div class="h-8 w-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-store text-white text-sm"></i>
                            </div>
                        @endif
                        <h3 class="text-lg font-semibold">{{ $appSettings['company_name'] ?? 'Your Company' }}</h3>
                    </div>
                    <p class="text-gray-300 mb-4">{{ $appSettings['company_address'] ?? 'Your Company Address' }}</p>
                    <p class="text-gray-300">Telp: {{ $appSettings['company_phone'] ?? 'Your Phone Number' }}</p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('store.index') }}" class="text-gray-300 hover:text-white transition-colors">Home</a></li>
                        <li><a href="{{ route('store.cart') }}" class="text-gray-300 hover:text-white transition-colors">Cart</a></li>
                        @auth
                            <li><a href="{{ route('dashboard') }}" class="text-gray-300 hover:text-white transition-colors">Dashboard</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition-colors">Admin Login</a></li>
                        @endauth
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact</h3>
                    <div class="space-y-2 text-gray-300">
                        <p><i class="fas fa-envelope mr-2"></i> info@yourstore.com</p>
                        <p><i class="fas fa-phone mr-2"></i> {{ $appSettings['company_phone'] ?? 'Your Phone' }}</p>
                        <p class="text-gray-300"><i class="fas fa-clock mr-2"></i> 24/7 Online</p>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
                <p>&copy; {{ date('Y') }} {{ $appSettings['company_name'] ?? 'Your Company' }}. All rights reserved.</p>
                <p class="mt-2">Powered by {{ $appSettings['app_name'] ?? config('app.name') }}</p>
            </div>
        </div>
    </footer>

    <!-- Notification Toast -->
    <div id="toast" class="fixed top-4 right-4 z-50 hidden">
        <div id="toast-content" class="bg-white border border-gray-200 rounded-lg shadow-lg p-4 max-w-sm">
            <div class="flex items-center">
                <div id="toast-icon" class="flex-shrink-0 w-6 h-6 mr-3"></div>
                <div id="toast-message" class="text-sm font-medium text-gray-900"></div>
                <button onclick="hideToast()" class="ml-4 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Toast notification functions
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toast-icon');
            const messageEl = document.getElementById('toast-message');
            const content = document.getElementById('toast-content');
            
            messageEl.textContent = message;
            
            if (type === 'success') {
                content.className = 'bg-green-50 border border-green-200 rounded-lg shadow-lg p-4 max-w-sm';
                icon.innerHTML = '<i class="fas fa-check-circle text-green-500"></i>';
            } else if (type === 'error') {
                content.className = 'bg-red-50 border border-red-200 rounded-lg shadow-lg p-4 max-w-sm';
                icon.innerHTML = '<i class="fas fa-exclamation-circle text-red-500"></i>';
            }
            
            toast.classList.remove('hidden');
            
            setTimeout(() => {
                hideToast();
            }, 5000);
        }
        
        function hideToast() {
            document.getElementById('toast').classList.add('hidden');
        }
        
        // Show Laravel flash messages as toasts
        @if(session('success'))
            showToast('{{ session('success') }}', 'success');
        @endif
        
        @if(session('error'))
            showToast('{{ session('error') }}', 'error');
        @endif
        
        // Mobile menu functionality
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            const isHidden = mobileMenu.classList.contains('hidden');
            
            if (isHidden) {
                mobileMenu.classList.remove('hidden');
                this.querySelector('i').classList.replace('fa-bars', 'fa-times');
            } else {
                mobileMenu.classList.add('hidden');
                this.querySelector('i').classList.replace('fa-times', 'fa-bars');
            }
        });
        
        // Mobile search functionality
        document.getElementById('mobileSearchBtn').addEventListener('click', function() {
            const mobileSearch = document.getElementById('mobileSearch');
            const isHidden = mobileSearch.classList.contains('hidden');
            
            if (isHidden) {
                mobileSearch.classList.remove('hidden');
                mobileSearch.querySelector('input').focus();
            } else {
                mobileSearch.classList.add('hidden');
            }
        });
    </script>
</body>
</html>