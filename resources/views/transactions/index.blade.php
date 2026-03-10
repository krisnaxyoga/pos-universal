<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Riwayat Transaksi
        </h2>
    </x-slot>

    <!-- Offline Notice -->
    <div id="offline-tx-notice" class="hidden mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
        <div class="flex items-center text-yellow-800">
            <i class="fas fa-wifi-slash mr-2"></i>
            <span class="text-sm font-medium">Mode Offline — Menampilkan data dari cache lokal (terakhir: <span id="offline-tx-time">-</span>)</span>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <!-- Filters -->
            <div class="mb-6">
                <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari nomor transaksi atau kasir..."
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Batal</option>
                        </select>
                    </div>

                    <div>
                        <select name="payment_method" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Semua Metode</option>
                            <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Tunai</option>
                            <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Kartu</option>
                            <option value="ewallet" {{ request('payment_method') === 'ewallet' ? 'selected' : '' }}>E-Wallet</option>
                            <option value="bon" {{ request('payment_method') === 'bon' ? 'selected' : '' }}>Bon/Hutang</option>
                            <option value="online" {{ request('payment_method') === 'online' ? 'selected' : '' }}>Online/Transfer</option>
                        </select>
                    </div>

                    <div>
                        <select name="bon_status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Status Bon</option>
                            <option value="unpaid" {{ request('bon_status') === 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                            <option value="paid" {{ request('bon_status') === 'paid' ? 'selected' : '' }}>Sudah Lunas</option>
                        </select>
                    </div>

                    <div>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="flex space-x-2">
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap">
                            Filter
                        </button>
                    </div>
                </form>
                
                @if(request()->hasAny(['search', 'status', 'payment_method', 'bon_status', 'date_from', 'date_to']))
                    <div class="mt-2">
                        <a href="{{ route('transactions.index') }}" class="text-sm text-red-600 hover:text-red-800">
                            Reset Filter
                        </a>
                    </div>
                @endif
            </div>

            <!-- Transactions Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                No. Transaksi
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Kasir
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Metode Bayar
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transactions as $transaction)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $transaction->transaction_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $transaction->user->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">Rp {{ number_format($transaction->total, 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-500">{{ $transaction->items->count() }} item</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @switch($transaction->payment_method)
                                            @case('cash') Tunai @break
                                            @case('card') Kartu @break
                                            @case('ewallet') E-Wallet @break
                                            @case('bon') Bon/Hutang @break
                                            @case('online') Online/Transfer @break
                                            @default {{ ucfirst($transaction->payment_method) }}
                                        @endswitch
                                    </div>
                                    @if($transaction->payment_method === 'bon')
                                        @if($transaction->isBonPaid())
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Lunas</span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Belum Lunas</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @switch($transaction->status)
                                        @case('completed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Selesai
                                            </span>
                                            @break
                                        @case('pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                            @break
                                        @case('cancelled')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Batal
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $transaction->created_at->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $transaction->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('transactions.show', $transaction) }}" 
                                           class="text-indigo-600 hover:text-indigo-900">Detail</a>
                                        
                                        <a href="{{ route('pos.receipt', $transaction) }}" 
                                           target="_blank"
                                           class="text-green-600 hover:text-green-900">Struk</a>
                                        
                                        @if(auth()->user()->isAdmin() && $transaction->status !== 'cancelled')
                                            <form action="{{ route('transactions.cancel', $transaction) }}" 
                                                  method="POST" class="inline"
                                                  onsubmit="return confirm('Yakin ingin membatalkan transaksi ini?')">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    Batal
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    Belum ada transaksi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $transactions->withQueryString()->links() }}
            </div>

            <!-- Summary Stats -->
            @if($transactions->count() > 0)
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-sm text-blue-600">Total Transaksi</div>
                        <div class="text-lg font-semibold text-blue-900">{{ $transactions->total() }}</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-sm text-green-600">Total Penjualan</div>
                        <div class="text-lg font-semibold text-green-900">
                            Rp {{ number_format($transactions->where('status', 'completed')->sum('total'), 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-sm text-purple-600">Rata-rata Transaksi</div>
                        <div class="text-lg font-semibold text-purple-900">
                            @php
                                $completedTransactions = $transactions->where('status', 'completed');
                                $average = $completedTransactions->count() > 0 ? $completedTransactions->sum('total') / $completedTransactions->count() : 0;
                            @endphp
                            Rp {{ number_format($average, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script src="/js/pwa/offline-transactions.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Cache transactions when online
            OfflineTransactions.cacheFromServer();
            // Render from IndexedDB when offline
            OfflineTransactions.renderTransactionList();
        });
    </script>
</x-app-layout>