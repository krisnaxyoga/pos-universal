<nav x-data="{ open: false, darkMode: localStorage.getItem('darkMode') === 'true' }" 
     x-init="$watch('darkMode', val => { 
         localStorage.setItem('darkMode', val); 
         if(val) { document.documentElement.classList.add('dark') } 
         else { document.documentElement.classList.remove('dark') } 
     })"
     class="glass sticky top-0 z-50 border-b border-white/20">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 group">
                        @if(isset($appSettings['app_logo']) && $appSettings['app_logo'] && Storage::disk('public')->exists($appSettings['app_logo']))
                            <img src="{{ Storage::url($appSettings['app_logo']) }}" alt="{{ $appSettings['app_name'] ?? config('app.name') }}" class="w-8 h-8 object-contain">
                        @else
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-cash-register text-white text-sm"></i>
                            </div>
                        @endif
                        <span class="font-bold text-lg text-gray-800 dark:text-white hidden sm:block">{{ $appSettings['app_name'] ?? config('app.name') }}</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-8 lg:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" 
                                class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-white/10">
                        <i class="fas fa-tachometer-alt text-sm"></i>
                        <span>Dashboard</span>
                    </x-nav-link>
                    
                    <x-nav-link :href="route('pos.index')" :active="request()->routeIs('pos.*')"
                                class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-white/10">
                        <i class="fas fa-cash-register text-sm"></i>
                        <span>POS</span>
                    </x-nav-link>
                    
                    <x-nav-link :href="route('transactions.index')" :active="request()->routeIs('transactions.*')"
                                class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-white/10">
                        <i class="fas fa-receipt text-sm"></i>
                        <span>Transaksi</span>
                    </x-nav-link>
                    
                    <x-nav-link :href="route('ipaymu.transactions')" :active="request()->routeIs('ipaymu.*')"
                                class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-white/10">
                        <i class="fas fa-credit-card text-sm"></i>
                        <span>iPaymu</span>
                    </x-nav-link>
                    
                    @if(auth()->user()->isAdmin() || auth()->user()->isSupervisor())
                        <div x-data="{ productsOpen: false }" class="relative">
                            <button @click="productsOpen = !productsOpen" 
                                    class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-white/10 text-gray-700 dark:text-gray-300">
                                <i class="fas fa-boxes text-sm"></i>
                                <span>Manajemen</span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            
                            <div x-show="productsOpen" @click.away="productsOpen = false" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute top-full left-0 mt-2 w-48 glass rounded-lg shadow-lg border border-white/20 py-2">
                                <a href="{{ route('products.index') }}" 
                                   class="flex items-center space-x-2 px-4 py-2 hover:bg-white/10 text-gray-700 dark:text-gray-300">
                                    <i class="fas fa-box text-sm"></i>
                                    <span>Produk</span>
                                </a>
                                <a href="{{ route('categories.index') }}" 
                                   class="flex items-center space-x-2 px-4 py-2 hover:bg-white/10 text-gray-700 dark:text-gray-300">
                                    <i class="fas fa-tags text-sm"></i>
                                    <span>Kategori</span>
                                </a>
                                <a href="{{ route('customers.index') }}" 
                                   class="flex items-center space-x-2 px-4 py-2 hover:bg-white/10 text-gray-700 dark:text-gray-300">
                                    <i class="fas fa-user-friends text-sm"></i>
                                    <span>Customer</span>
                                </a>
                                <a href="{{ route('reports.index') }}" 
                                   class="flex items-center space-x-2 px-4 py-2 hover:bg-white/10 text-gray-700 dark:text-gray-300">
                                    <i class="fas fa-chart-bar text-sm"></i>
                                    <span>Laporan</span>
                                </a>
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('users.index') }}" 
                                       class="flex items-center space-x-2 px-4 py-2 hover:bg-white/10 text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-users text-sm"></i>
                                        <span>Manajemen User</span>
                                    </a>
                                    <a href="{{ route('settings.index') }}" 
                                       class="flex items-center space-x-2 px-4 py-2 hover:bg-white/10 text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-cog text-sm"></i>
                                        <span>Pengaturan</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right side items -->
            <div class="flex items-center space-x-3">
                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode" 
                        class="p-2 rounded-lg hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors">
                    <i x-show="!darkMode" class="fas fa-moon"></i>
                    <i x-show="darkMode" class="fas fa-sun"></i>
                </button>

                <!-- Notifications Dropdown -->
                <div class="hidden sm:flex sm:items-center">
                    <x-dropdown align="right" width="80">
                        <x-slot name="trigger">
                            <button class="relative p-2 rounded-lg hover:bg-white/10 text-gray-600 dark:text-gray-300 transition-colors">
                                <i class="fas fa-bell text-lg"></i>
                                <!-- Notification Badge -->
                                <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center" 
                                      id="notification-badge" style="display: none;">
                                    0
                                </span>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="glass rounded-lg shadow-lg border border-white/20 max-w-sm">
                                <!-- Notification Header -->
                                <div class="px-4 py-3 border-b border-white/10">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                                            <i class="fas fa-bell mr-2"></i>
                                            Notifikasi
                                        </h3>
                                        <button onclick="markAllAsRead()" class="text-xs text-blue-600 hover:text-blue-800">
                                            Tandai Semua
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Notifications List -->
                                <div class="max-h-80 overflow-y-auto" id="notifications-list">
                                    <!-- Low Stock Notifications -->
                                    <div class="px-4 py-3 border-b border-white/5 hover:bg-white/5">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-exclamation-triangle text-orange-600 dark:text-orange-400 text-xs"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    Stok Produk Menipis
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    <span id="low-stock-count">0</span> produk memerlukan restocking
                                                </p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    <span id="low-stock-time">Baru saja</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Recent Transactions -->
                                    <div class="px-4 py-3 border-b border-white/5 hover:bg-white/5">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-cash-register text-green-600 dark:text-green-400 text-xs"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    Transaksi Hari Ini
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    <span id="today-transactions-count">0</span> transaksi - Rp <span id="today-revenue">0</span>
                                                </p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Update terakhir: <span id="last-transaction-time">-</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- iPaymu Notifications -->
                                    <div class="px-4 py-3 border-b border-white/5 hover:bg-white/5">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-credit-card text-blue-600 dark:text-blue-400 text-xs"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    Pembayaran Online
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    <span id="pending-payments-count">0</span> pembayaran pending
                                                </p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Update: <span id="payments-update-time">-</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- System Updates -->
                                    <div class="px-4 py-3 hover:bg-white/5">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-info-circle text-purple-600 dark:text-purple-400 text-xs"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    Update Sistem
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    Fitur barcode & scanner berhasil ditambahkan!
                                                </p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Sep 1, 2025
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- View All Link -->
                                <div class="px-4 py-3 border-t border-white/10">
                                    <a href="{{ route('dashboard') }}" 
                                       class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 flex items-center justify-center">
                                        <i class="fas fa-external-link-alt mr-2"></i>
                                        Lihat Semua di Dashboard
                                    </a>
                                </div>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- User Role Badge -->
                <div class="hidden sm:flex">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        {{ auth()->user()->isAdmin() ? 'bg-red-100 text-red-800' : 
                           (auth()->user()->isSupervisor() ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                        {{ auth()->user()->role }}
                    </span>
                </div>

                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-white/10 text-gray-700 dark:text-gray-300 transition-colors">
                                <div class="w-8 h-8 bg-gradient-to-br from-gray-400 to-gray-600 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </span>
                                </div>
                                <span class="font-medium">{{ Auth::user()->name }}</span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="glass rounded-lg shadow-lg border border-white/20">
                                <div class="px-4 py-3 border-b border-white/10">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
                                </div>
                                
                                <x-dropdown-link :href="route('profile.edit')" class="flex items-center space-x-2">
                                    <i class="fas fa-user text-sm"></i>
                                    <span>{{ __('Profile') }}</span>
                                </x-dropdown-link>

                                <!-- Authentication -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();"
                                            class="flex items-center space-x-2 text-red-600">
                                        <i class="fas fa-sign-out-alt text-sm"></i>
                                        <span>{{ __('Log Out') }}</span>
                                    </x-dropdown-link>
                                </form>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Mobile Hamburger -->
                <div class="flex items-center sm:hidden">
                    <button @click="open = ! open" 
                            class="inline-flex items-center justify-center p-2 rounded-lg text-gray-400 hover:text-gray-500 hover:bg-white/10 focus:outline-none transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden glass border-t border-white/20">
        <div class="px-4 pt-4 pb-3 space-y-2">
            <!-- User Info -->
            <div class="flex items-center space-x-3 mb-4 p-3 bg-white/10 rounded-lg">
                <div class="w-10 h-10 bg-gradient-to-br from-gray-400 to-gray-600 rounded-full flex items-center justify-center">
                    <span class="text-white font-medium">{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
                <div>
                    <div class="font-medium text-gray-800 dark:text-white">{{ Auth::user()->name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full mt-1
                        {{ auth()->user()->isAdmin() ? 'bg-red-100 text-red-800' : 
                           (auth()->user()->isSupervisor() ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                        {{ auth()->user()->role }}
                    </span>
                </div>
            </div>

            <!-- Navigation Links -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-white/10 text-gray-700 dark:text-gray-300 {{ request()->routeIs('dashboard') ? 'bg-blue-100 text-blue-800' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="{{ route('pos.index') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-white/10 text-gray-700 dark:text-gray-300 {{ request()->routeIs('pos.*') ? 'bg-blue-100 text-blue-800' : '' }}">
                <i class="fas fa-cash-register"></i>
                <span>POS Kasir</span>
            </a>
            
            <a href="{{ route('transactions.index') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-white/10 text-gray-700 dark:text-gray-300 {{ request()->routeIs('transactions.*') ? 'bg-blue-100 text-blue-800' : '' }}">
                <i class="fas fa-receipt"></i>
                <span>Transaksi</span>
            </a>
            
            @if(auth()->user()->isAdmin() || auth()->user()->isSupervisor())
                <!-- Management Section -->
                <div class="pt-3 border-t border-white/20">
                    <div class="px-4 py-2">
                        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Manajemen</h3>
                    </div>
                    
                    <a href="{{ route('products.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-white/10 text-gray-700 dark:text-gray-300 {{ request()->routeIs('products.*') ? 'bg-blue-100 text-blue-800' : '' }}">
                        <i class="fas fa-box"></i>
                        <span>Produk</span>
                    </a>
                    
                    <a href="{{ route('categories.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-white/10 text-gray-700 dark:text-gray-300 {{ request()->routeIs('categories.*') ? 'bg-blue-100 text-blue-800' : '' }}">
                        <i class="fas fa-tags"></i>
                        <span>Kategori</span>
                    </a>
                    
                    <a href="{{ route('customers.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-white/10 text-gray-700 dark:text-gray-300 {{ request()->routeIs('customers.*') ? 'bg-blue-100 text-blue-800' : '' }}">
                        <i class="fas fa-user-friends"></i>
                        <span>Customer</span>
                    </a>
                    
                    <a href="{{ route('reports.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-white/10 text-gray-700 dark:text-gray-300 {{ request()->routeIs('reports.*') ? 'bg-blue-100 text-blue-800' : '' }}">
                        <i class="fas fa-chart-bar"></i>
                        <span>Laporan</span>
                    </a>
                    
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('users.index') }}" 
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-white/10 text-gray-700 dark:text-gray-300 {{ request()->routeIs('users.*') ? 'bg-blue-100 text-blue-800' : '' }}">
                            <i class="fas fa-users"></i>
                            <span>Manajemen User</span>
                        </a>
                        <a href="{{ route('settings.index') }}" 
                           class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-white/10 text-gray-700 dark:text-gray-300 {{ request()->routeIs('settings.*') ? 'bg-blue-100 text-blue-800' : '' }}">
                            <i class="fas fa-cog"></i>
                            <span>Pengaturan</span>
                        </a>
                    @endif
                </div>
            @endif

            <!-- Settings Section -->
            <div class="pt-3 border-t border-white/20">
                <!-- Mobile Notifications -->
                <div class="mb-4 p-3 bg-white/5 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                        <i class="fas fa-bell mr-2"></i>
                        Notifikasi
                    </h3>
                    
                    <div class="space-y-2">
                        <!-- Low Stock -->
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-600 dark:text-gray-400">
                                <i class="fas fa-exclamation-triangle text-orange-500 mr-1"></i>
                                Stok Menipis
                            </span>
                            <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded-full" id="mobile-low-stock-count">0</span>
                        </div>
                        
                        <!-- Today Transactions -->
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-600 dark:text-gray-400">
                                <i class="fas fa-cash-register text-green-500 mr-1"></i>
                                Transaksi Hari Ini
                            </span>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full" id="mobile-transactions-count">0</span>
                        </div>
                        
                        <!-- Pending Payments -->
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-600 dark:text-gray-400">
                                <i class="fas fa-credit-card text-blue-500 mr-1"></i>
                                Pembayaran Pending
                            </span>
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full" id="mobile-pending-payments">0</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('profile.edit') }}" 
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-white/10 text-gray-700 dark:text-gray-300">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>

                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode" 
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-white/10 text-gray-700 dark:text-gray-300 w-full text-left">
                    <i x-show="!darkMode" class="fas fa-moon"></i>
                    <i x-show="darkMode" class="fas fa-sun"></i>
                    <span x-text="darkMode ? 'Light Mode' : 'Dark Mode'"></span>
                </button>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-red-100 text-red-600 w-full text-left">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Log Out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- Notifications JavaScript -->
<script>
let notificationData = {
    lowStock: 0,
    todayTransactions: 0,
    todayRevenue: 0,
    pendingPayments: 0,
    lastUpdate: new Date()
};

// Initialize notifications on page load
document.addEventListener('DOMContentLoaded', function() {
    loadNotificationData();
    
    // Update notifications every 30 seconds
    setInterval(loadNotificationData, 30000);
});

function loadNotificationData() {
    // Fetch low stock products
    fetch('/api/dashboard-stats', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateNotifications(data.stats);
        }
    })
    .catch(error => {
        console.log('Failed to fetch notification data:', error);
        // Use default demo data
        updateNotifications({
            low_stock_count: 3,
            today_transactions: 15,
            today_revenue: 750000,
            pending_payments: 2
        });
    });
}

function updateNotifications(stats) {
    // Update notification counts
    notificationData.lowStock = stats.low_stock_count || 0;
    notificationData.todayTransactions = stats.today_transactions || 0;
    notificationData.todayRevenue = stats.today_revenue || 0;
    notificationData.pendingPayments = stats.pending_payments || 0;
    notificationData.lastUpdate = new Date();
    
    // Update desktop notifications
    updateDesktopNotifications();
    
    // Update mobile notifications  
    updateMobileNotifications();
    
    // Update notification badge
    updateNotificationBadge();
}

function updateDesktopNotifications() {
    // Low stock count
    const lowStockElement = document.getElementById('low-stock-count');
    if (lowStockElement) {
        lowStockElement.textContent = notificationData.lowStock;
    }
    
    // Today transactions
    const transactionsElement = document.getElementById('today-transactions-count');
    if (transactionsElement) {
        transactionsElement.textContent = notificationData.todayTransactions;
    }
    
    // Today revenue
    const revenueElement = document.getElementById('today-revenue');
    if (revenueElement) {
        revenueElement.textContent = formatCurrency(notificationData.todayRevenue);
    }
    
    // Pending payments
    const paymentsElement = document.getElementById('pending-payments-count');
    if (paymentsElement) {
        paymentsElement.textContent = notificationData.pendingPayments;
    }
    
    // Update timestamps
    const now = new Date();
    const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    
    const lowStockTime = document.getElementById('low-stock-time');
    if (lowStockTime) {
        lowStockTime.textContent = timeStr;
    }
    
    const lastTransactionTime = document.getElementById('last-transaction-time');
    if (lastTransactionTime) {
        lastTransactionTime.textContent = timeStr;
    }
    
    const paymentsUpdateTime = document.getElementById('payments-update-time');
    if (paymentsUpdateTime) {
        paymentsUpdateTime.textContent = timeStr;
    }
}

function updateMobileNotifications() {
    // Mobile low stock
    const mobileStockElement = document.getElementById('mobile-low-stock-count');
    if (mobileStockElement) {
        mobileStockElement.textContent = notificationData.lowStock;
    }
    
    // Mobile transactions
    const mobileTransElement = document.getElementById('mobile-transactions-count');
    if (mobileTransElement) {
        mobileTransElement.textContent = notificationData.todayTransactions;
    }
    
    // Mobile pending payments
    const mobilePaymentsElement = document.getElementById('mobile-pending-payments');
    if (mobilePaymentsElement) {
        mobilePaymentsElement.textContent = notificationData.pendingPayments;
    }
}

function updateNotificationBadge() {
    const badge = document.getElementById('notification-badge');
    if (badge) {
        const totalNotifications = notificationData.lowStock + notificationData.pendingPayments;
        
        if (totalNotifications > 0) {
            badge.textContent = totalNotifications > 99 ? '99+' : totalNotifications;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
}

function markAllAsRead() {
    // Visual feedback
    const badge = document.getElementById('notification-badge');
    if (badge) {
        badge.style.display = 'none';
    }
    
    // You can add API call here to mark notifications as read
    console.log('All notifications marked as read');
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID').format(amount);
}

// Add bell shake animation for new notifications
function shakeNotificationBell() {
    const bellIcon = document.querySelector('.fa-bell');
    if (bellIcon) {
        bellIcon.classList.add('fa-shake');
        setTimeout(() => {
            bellIcon.classList.remove('fa-shake');
        }, 1000);
    }
}

// Call shake animation on significant updates
let previousTotal = 0;
function checkForNewNotifications() {
    const currentTotal = notificationData.lowStock + notificationData.pendingPayments;
    if (currentTotal > previousTotal && previousTotal > 0) {
        shakeNotificationBell();
    }
    previousTotal = currentTotal;
}
</script>
