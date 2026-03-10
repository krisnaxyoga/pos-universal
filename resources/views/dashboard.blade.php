<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                <i class="fas fa-tachometer-alt mr-2"></i>
                {{ __('app.dashboard') }} POS
            </h2>
            <div class="hidden sm:flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <i class="fas fa-clock"></i>
                <span id="current-time"></span>
            </div>
        </div>
    </x-slot>

    <!-- Mobile Welcome Card -->
    <div class="md:hidden mb-6">
        <div class="glass rounded-xl p-6 text-center">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user text-white text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('app.welcome') }}, {{ auth()->user()->name }}!</h3>
            <p class="text-gray-600 dark:text-gray-400">{{ auth()->user()->role }}</p>
            <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                <i class="fas fa-calendar mr-1"></i>
                {{ now()->format('d F Y, H:i') }}
            </div>
        </div>
    </div>

    <!-- Bon/Hutang Alert -->
    @if($bonUnpaidCount > 0)
    <div class="mb-6">
        <a href="{{ route('bon.index') }}" class="block glass rounded-xl border border-red-200 bg-red-50/50 hover:bg-red-50 transition-colors">
            <div class="p-4 sm:p-5 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-rose-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-file-invoice-dollar text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-red-800">Bon/Hutang Belum Lunas</p>
                        <p class="text-lg sm:text-xl font-bold text-red-900">{{ $bonUnpaidCount }} bon — Rp {{ number_format($bonUnpaidTotal, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="flex-shrink-0 text-red-400">
                    <i class="fas fa-chevron-right text-lg"></i>
                </div>
            </div>
        </a>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="glass rounded-xl shadow-sm hover:shadow-md transition-shadow">
            <div class="p-4">
                <div class="flex flex-col sm:flex-row sm:items-center">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center mb-2 sm:mb-0">
                            <i class="fas fa-money-bill-wave text-white"></i>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <dt class="text-xs sm:text-sm font-medium text-dark truncate">{{ __('app.today_sales') }}</dt>
                            <dd class="text-sm sm:text-lg font-bold text-gray-900 dark:text-white">Rp {{ number_format($todaySales, 0, ',', '.') }}</dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass rounded-xl shadow-sm hover:shadow-md transition-shadow">
            <div class="p-4">
                <div class="flex flex-col sm:flex-row sm:items-center">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center mb-2 sm:mb-0">
                            <i class="fas fa-receipt text-white"></i>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ __('app.today_transactions') }}</dt>
                            <dd class="text-sm sm:text-lg font-bold text-gray-900 dark:text-white">{{ $todayTransactions }}</dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass rounded-xl shadow-sm hover:shadow-md transition-shadow">
            <div class="p-4">
                <div class="flex flex-col sm:flex-row sm:items-center">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-lg flex items-center justify-center mb-2 sm:mb-0">
                            <i class="fas fa-exclamation-triangle text-white"></i>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ __('app.low_stock') }}</dt>
                            <dd class="text-sm sm:text-lg font-bold text-gray-900 dark:text-white">{{ $lowStockProducts }}</dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass rounded-xl shadow-sm hover:shadow-md transition-shadow">
            <div class="p-4">
                <div class="flex flex-col sm:flex-row sm:items-center">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center mb-2 sm:mb-0">
                            <i class="fas fa-boxes text-white"></i>
                        </div>
                        <div class="ml-3 sm:ml-4">
                            <dt class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 truncate">{{ __('app.total_products') }}</dt>
                            <dd class="text-sm sm:text-lg font-bold text-gray-900 dark:text-white">{{ $totalProducts }}</dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Quick Actions -->
    <div class="md:hidden mb-6">
        <div class="glass rounded-xl p-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <i class="fas fa-bolt mr-2"></i>
                {{ __('app.quick_actions') }}
            </h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('pos.index') }}"
                   class="flex flex-col items-center p-4 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl text-white hover:scale-105 transition-transform">
                    <i class="fas fa-cash-register text-2xl mb-2"></i>
                    <span class="text-sm font-medium">{{ __('app.pos') }}</span>
                </a>
                <a href="{{ route('transactions.index') }}"
                   class="flex flex-col items-center p-4 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl text-white hover:scale-105 transition-transform">
                    <i class="fas fa-receipt text-2xl mb-2"></i>
                    <span class="text-sm font-medium">{{ __('app.transactions') }}</span>
                </a>
                <a href="{{ route('bon.index') }}"
                   class="flex flex-col items-center p-4 bg-gradient-to-br from-red-500 to-rose-600 rounded-xl text-white hover:scale-105 transition-transform relative">
                    <i class="fas fa-file-invoice-dollar text-2xl mb-2"></i>
                    <span class="text-sm font-medium">Bon/Hutang</span>
                    @if($bonUnpaidCount > 0)
                        <span class="absolute top-1 right-1 bg-yellow-400 text-yellow-900 text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $bonUnpaidCount }}</span>
                    @endif
                </a>
                @if(auth()->user()->isAdmin() || auth()->user()->isSupervisor())
                    <a href="{{ route('products.index') }}"
                       class="flex flex-col items-center p-4 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl text-white hover:scale-105 transition-transform">
                        <i class="fas fa-box text-2xl mb-2"></i>
                        <span class="text-sm font-medium">{{ __('app.products') }}</span>
                    </a>
                    <a href="{{ route('reports.index') }}"
                       class="flex flex-col items-center p-4 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl text-white hover:scale-105 transition-transform">
                        <i class="fas fa-chart-bar text-2xl mb-2"></i>
                        <span class="text-sm font-medium">{{ __('app.reports') }}</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Transactions -->
        <div class="glass rounded-xl shadow-sm">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-clock mr-2"></i>
                    {{ __('app.recent_transactions') }}
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">{{ __('app.last_5_transactions') }}</p>
            </div>
            <div class="border-t border-white/20">
                <ul class="divide-y divide-white/10">
                    @forelse($recentTransactions as $transaction)
                        <li class="px-4 py-4 hover:bg-white/5 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-check text-green-600 text-sm"></i>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $transaction->transaction_number }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $transaction->user->name }} • {{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    Rp {{ number_format($transaction->total, 0, ',', '.') }}
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-clock text-2xl mb-2"></i>
                            <p>{{ __('app.no_transactions_today') }}</p>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Low Stock Products -->
        <div class="glass rounded-xl shadow-sm">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2 text-orange-500"></i>
                    {{ __('app.low_stock') }}
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">{{ __('app.products_need_restock') }}</p>
            </div>
            <div class="border-t border-white/20">
                <ul class="divide-y divide-white/10">
                    @forelse($lowStockItems as $product)
                        <li class="px-4 py-4 hover:bg-white/5 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-box text-orange-600 text-sm"></i>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $product->category->name }}</p>
                                    </div>
                                </div>
                                <div class="text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ __('app.stock') }}: {{ $product->stock }}
                                    </span>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-check-circle text-2xl mb-2 text-green-500"></i>
                            <p>{{ __('app.all_stock_safe') }}</p>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Desktop Quick Actions -->
    <div class="hidden md:block mt-8 glass rounded-xl shadow-sm">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white flex items-center">
                <i class="fas fa-bolt mr-2"></i>
                {{ __('app.quick_actions') }}
            </h3>
        </div>
        <div class="border-t border-white/20 px-4 py-5 sm:px-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('pos.index') }}"
                   class="flex items-center justify-center px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg font-semibold text-white hover:from-blue-600 hover:to-indigo-700 transition-all hover:scale-105">
                    <i class="fas fa-cash-register mr-2"></i>
                    {{ __('app.create_transaction') }}
                </a>

                @if(auth()->user()->isAdmin() || auth()->user()->isSupervisor())
                    <a href="{{ route('products.create') }}"
                       class="flex items-center justify-center px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg font-semibold text-white hover:from-green-600 hover:to-emerald-700 transition-all hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>
                        {{ __('app.add_product') }}
                    </a>

                    <a href="{{ route('reports.sales') }}"
                       class="flex items-center justify-center px-4 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg font-semibold text-white hover:from-purple-600 hover:to-indigo-700 transition-all hover:scale-105">
                        <i class="fas fa-chart-bar mr-2"></i>
                        {{ __('app.view_reports') }}
                    </a>
                @endif

                <a href="{{ route('transactions.index') }}"
                   class="flex items-center justify-center px-4 py-3 bg-gradient-to-r from-orange-500 to-red-600 rounded-lg font-semibold text-white hover:from-orange-600 hover:to-red-700 transition-all hover:scale-105">
                    <i class="fas fa-history mr-2"></i>
                    {{ __('app.transaction_history') }}
                </a>

                <a href="{{ route('bon.index') }}"
                   class="flex items-center justify-center px-4 py-3 bg-gradient-to-r from-red-500 to-rose-600 rounded-lg font-semibold text-white hover:from-red-600 hover:to-rose-700 transition-all hover:scale-105 relative">
                    <i class="fas fa-file-invoice-dollar mr-2"></i>
                    Bon/Hutang
                    @if($bonUnpaidCount > 0)
                        <span class="ml-2 bg-yellow-400 text-yellow-900 text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $bonUnpaidCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    <!-- Pending Offline Transactions -->
    <div id="offline-pending-card" class="hidden mt-6 glass rounded-xl shadow-sm border border-yellow-200">
        <div class="p-4 sm:p-5 flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-wifi text-yellow-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-yellow-800">Transaksi Offline Menunggu Sync</p>
                    <p class="text-lg font-bold text-yellow-900"><span id="offline-pending-count">0</span> transaksi</p>
                </div>
            </div>
            <button onclick="window.syncManager && window.syncManager.syncAll()"
                    class="px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-sm font-medium">
                <i class="fas fa-sync mr-1"></i> Sync
            </button>
        </div>
    </div>

    <!-- Real-time Clock Script -->
    <script>
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const clockElement = document.getElementById('current-time');
            if (clockElement) {
                clockElement.textContent = timeString;
            }
        }

        updateClock();
        setInterval(updateClock, 1000);

        // Check for pending offline transactions
        (async function() {
            if (!window.posDB) return;
            try {
                const count = await window.posDB.getPendingCount();
                if (count > 0) {
                    const card = document.getElementById('offline-pending-card');
                    const countEl = document.getElementById('offline-pending-count');
                    if (card && countEl) {
                        countEl.textContent = count;
                        card.classList.remove('hidden');
                    }
                }
                // Cache dashboard stats for offline
                await window.posDB.setMeta('dashboard_cached_at', new Date().toISOString());
                await window.posDB.setMeta('dashboard_stats', {
                    todaySales: {{ $todaySales }},
                    todayTransactions: {{ $todayTransactions }},
                    lowStockProducts: {{ $lowStockProducts }},
                    totalProducts: {{ $totalProducts }}
                });
            } catch (e) {}
        })();
    </script>
</x-app-layout>
