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
