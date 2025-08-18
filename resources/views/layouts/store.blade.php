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
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center">
                    <a href="{{ route('store.index') }}" class="flex items-center space-x-3">
                        @if(isset($appSettings['app_logo']) && $appSettings['app_logo'] && Storage::disk('public')->exists($appSettings['app_logo']))
                            <img src="{{ Storage::url($appSettings['app_logo']) }}" alt="{{ $appSettings['app_name'] ?? 'Store' }}" class="h-10 w-10 object-contain">
                        @else
                            <div class="h-10 w-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-store text-white text-lg"></i>
                            </div>
                        @endif
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ $appSettings['app_name'] ?? config('app.name', 'Online Store') }}</h1>
                            <p class="text-xs text-gray-500">Online Store</p>
                        </div>
                    </a>
                </div>

                <!-- Search Bar -->
                <div class="hidden md:flex items-center flex-1 max-w-lg mx-8">
                    <form method="GET" action="{{ route('store.index') }}" class="w-full">
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Cari produk..." 
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-arrow-right text-gray-400 hover:text-blue-500"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Cart & Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Cart -->
                    <a href="{{ route('store.cart') }}" class="relative p-2 text-gray-600 hover:text-blue-600 transition-colors">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        @if(isset($cartCount) && $cartCount > 0)
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                    
                    <!-- Admin Login -->
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-blue-600 transition-colors">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="hidden sm:inline ml-1">Dashboard</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-red-600 transition-colors">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="hidden sm:inline ml-1">Logout</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-blue-600 transition-colors">
                            <i class="fas fa-sign-in-alt"></i>
                            <span class="hidden sm:inline ml-1">Admin</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
        
        <!-- Mobile Search -->
        <div class="md:hidden px-4 pb-3">
            <form method="GET" action="{{ route('store.index') }}">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari produk..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </form>
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
                        <p><i class="fas fa-clock mr-2"></i> 24/7 Online</p>
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
    </script>
</body>
</html>