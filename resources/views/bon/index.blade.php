<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Bon / Hutang
        </h2>
    </x-slot>

    <!-- Offline Notice -->
    <div id="offline-bon-notice" class="hidden mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
        <div class="flex items-center text-yellow-800">
            <i class="fas fa-wifi-slash mr-2"></i>
            <span class="text-sm font-medium">Mode Offline — Data dari cache lokal (terakhir: <span id="offline-bon-time">-</span>). Pelunasan bon tidak tersedia saat offline.</span>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 sm:p-6 text-gray-900">

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-6">
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4">
                    <div class="text-xs sm:text-sm text-red-600 font-medium">Total Belum Lunas</div>
                    <div class="text-lg sm:text-2xl font-bold text-red-700">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</div>
                    <div class="text-xs text-red-500">{{ $countUnpaid }} transaksi</div>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4">
                    <div class="text-xs sm:text-sm text-green-600 font-medium">Total Sudah Lunas</div>
                    <div class="text-lg sm:text-2xl font-bold text-green-700">Rp {{ number_format($totalPaid, 0, ',', '.') }}</div>
                    <div class="text-xs text-green-500">{{ $countPaid }} transaksi</div>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
                    <div class="text-xs sm:text-sm text-blue-600 font-medium">Total Semua Bon</div>
                    <div class="text-lg sm:text-2xl font-bold text-blue-700">Rp {{ number_format($totalUnpaid + $totalPaid, 0, ',', '.') }}</div>
                    <div class="text-xs text-blue-500">{{ $countUnpaid + $countPaid }} transaksi</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="mb-6">
                <form method="GET" action="{{ route('bon.index') }}" class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                    <div class="col-span-2 md:col-span-1">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari nama/telepon..."
                               class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <select name="status" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Semua Status</option>
                            <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Sudah Lunas</option>
                        </select>
                    </div>

                    <div class="hidden md:block">
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="flex space-x-2">
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="hidden md:block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded whitespace-nowrap text-sm">
                            Filter
                        </button>
                    </div>
                </form>

                @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                    <div class="mt-2">
                        <a href="{{ route('bon.index') }}" class="text-sm text-red-600 hover:text-red-800">
                            Reset Filter
                        </a>
                    </div>
                @endif
            </div>

            <!-- Desktop Table (hidden on mobile) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Transaksi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelanggan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kasir</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transactions as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono">
                                <a href="{{ route('transactions.show', $transaction) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $transaction->transaction_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($transaction->customer_info)
                                    <div class="font-medium">{{ $transaction->customer_info['name'] ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $transaction->customer_info['phone'] ?? '' }}</div>
                                    @if(!empty($transaction->customer_info['address']))
                                        <div class="text-xs text-gray-400">{{ $transaction->customer_info['address'] }}</div>
                                    @endif
                                @elseif($transaction->customer)
                                    <div class="font-medium">{{ $transaction->customer->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $transaction->customer->phone }}</div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-medium">
                                Rp {{ number_format($transaction->total, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($transaction->isBonPaid())
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        LUNAS
                                    </span>
                                    <div class="text-xs text-gray-400 mt-1">{{ $transaction->bon_paid_at?->format('d/m/Y H:i') }}</div>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        BELUM LUNAS
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $transaction->user->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($transaction->isBonUnpaid())
                                    <button
                                        onclick="showPayModal('{{ $transaction->id }}', '{{ $transaction->transaction_number }}', {{ $transaction->total }}, '{{ addslashes($transaction->customer_info['name'] ?? ($transaction->customer->name ?? '-')) }}')"
                                        class="bg-green-500 hover:bg-green-700 text-white text-xs font-bold py-1 px-3 rounded transition-colors"
                                    >
                                        Lunasi
                                    </button>
                                @else
                                    <span class="text-green-600 text-xs font-medium">Dibayar Rp {{ number_format($transaction->bon_paid_amount ?? $transaction->total, 0, ',', '.') }}</span>
                                @endif
                                <a href="{{ route('pos.receipt', $transaction->id) }}" class="text-blue-600 hover:text-blue-800 text-xs ml-2" target="_blank">
                                    Struk
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data bon/hutang
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View (hidden on desktop) -->
            <div class="md:hidden space-y-3">
                @forelse($transactions as $transaction)
                <div class="border border-gray-200 rounded-lg p-4 {{ $transaction->isBonPaid() ? 'bg-green-50/30' : 'bg-white' }}">
                    <!-- Header: Status + Transaction Number -->
                    <div class="flex items-center justify-between mb-3">
                        <a href="{{ route('transactions.show', $transaction) }}" class="text-sm font-mono text-blue-600 hover:text-blue-800">
                            {{ $transaction->transaction_number }}
                        </a>
                        @if($transaction->isBonPaid())
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                LUNAS
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                BELUM LUNAS
                            </span>
                        @endif
                    </div>

                    <!-- Customer Info + Total -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0 mr-3">
                            @if($transaction->customer_info)
                                <div class="font-medium text-sm text-gray-900 truncate">{{ $transaction->customer_info['name'] ?? '-' }}</div>
                                @if(!empty($transaction->customer_info['phone']))
                                    <div class="text-xs text-gray-500">{{ $transaction->customer_info['phone'] }}</div>
                                @endif
                                @if(!empty($transaction->customer_info['address']))
                                    <div class="text-xs text-gray-400 truncate">{{ $transaction->customer_info['address'] }}</div>
                                @endif
                            @elseif($transaction->customer)
                                <div class="font-medium text-sm text-gray-900 truncate">{{ $transaction->customer->name }}</div>
                                <div class="text-xs text-gray-500">{{ $transaction->customer->phone }}</div>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-sm font-bold text-gray-900">Rp {{ number_format($transaction->total, 0, ',', '.') }}</div>
                        </div>
                    </div>

                    <!-- Meta: Date + Cashier -->
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                        <span><i class="fas fa-calendar mr-1"></i>{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
                        <span><i class="fas fa-user mr-1"></i>{{ $transaction->user->name ?? '-' }}</span>
                    </div>

                    @if($transaction->isBonPaid())
                        <div class="text-xs text-gray-400 mb-3">
                            Dilunasi: {{ $transaction->bon_paid_at?->format('d/m/Y H:i') }}
                            — Rp {{ number_format($transaction->bon_paid_amount ?? $transaction->total, 0, ',', '.') }}
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex items-center space-x-2 pt-2 border-t border-gray-100">
                        @if($transaction->isBonUnpaid())
                            <button
                                onclick="showPayModal('{{ $transaction->id }}', '{{ $transaction->transaction_number }}', {{ $transaction->total }}, '{{ addslashes($transaction->customer_info['name'] ?? ($transaction->customer->name ?? '-')) }}')"
                                class="flex-1 bg-green-500 hover:bg-green-700 text-white text-xs font-bold py-2 px-3 rounded transition-colors text-center"
                            >
                                <i class="fas fa-check-circle mr-1"></i> Lunasi
                            </button>
                        @endif
                        <a href="{{ route('transactions.show', $transaction) }}" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium py-2 px-3 rounded transition-colors text-center">
                            <i class="fas fa-eye mr-1"></i> Detail
                        </a>
                        <a href="{{ route('pos.receipt', $transaction->id) }}" target="_blank" class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium py-2 px-3 rounded transition-colors text-center">
                            <i class="fas fa-receipt mr-1"></i> Struk
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-file-invoice-dollar text-3xl mb-2 text-gray-300"></i>
                    <p>Tidak ada data bon/hutang</p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>

    <!-- Pay Modal -->
    <div id="pay-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-5 sm:p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Lunasi Bon</h3>
                <button onclick="hidePayModal()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">
                    &times;
                </button>
            </div>

            <form id="pay-form" method="POST">
                @csrf
                <div class="space-y-4">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-sm"><strong>No. Transaksi:</strong> <span id="pay-transaction-number"></span></p>
                        <p class="text-sm"><strong>Pelanggan:</strong> <span id="pay-customer-name"></span></p>
                        <p class="text-sm"><strong>Total Hutang:</strong> <span id="pay-total" class="text-red-600 font-bold"></span></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Bayar</label>
                        <input
                            type="number"
                            id="pay-amount"
                            name="amount"
                            min="0"
                            step="1000"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Masukkan jumlah bayar"
                            required
                        >
                    </div>

                    <div class="flex space-x-3">
                        <button
                            type="button"
                            onclick="hidePayModal()"
                            class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2.5 px-4 rounded transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-2.5 px-4 rounded transition-colors"
                        >
                            Bayar & Lunasi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showPayModal(transactionId, transactionNumber, total, customerName) {
            document.getElementById('pay-transaction-number').textContent = transactionNumber;
            document.getElementById('pay-customer-name').textContent = customerName;
            document.getElementById('pay-total').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
            document.getElementById('pay-amount').value = total;
            document.getElementById('pay-form').action = `/bon/${transactionId}/pay`;

            const modal = document.getElementById('pay-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function hidePayModal() {
            const modal = document.getElementById('pay-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>

    <script src="/js/pwa/idb-helper.js"></script>
    <script src="/js/pwa/offline-transactions.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            OfflineTransactions.cacheFromServer();
            OfflineTransactions.renderBonList();
        });
    </script>
</x-app-layout>
