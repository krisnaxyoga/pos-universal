<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Customer') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('customers.edit', $customer) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    Edit
                </a>
                <a href="{{ route('customers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Customer Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Basic Info -->
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Customer</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Nama Lengkap</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customer->name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Kode Customer</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customer->customer_code }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Email</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customer->email }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Nomor Telepon</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customer->phone }}</p>
                                </div>
                                @if($customer->birth_date)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Tanggal Lahir</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customer->birth_date->format('d F Y') }}</p>
                                </div>
                                @endif
                                @if($customer->gender)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Jenis Kelamin</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customer->gender == 'male' ? 'Laki-laki' : 'Perempuan' }}</p>
                                </div>
                                @endif
                                @if($customer->address)
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-500">Alamat</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customer->address }}</p>
                                </div>
                                @endif
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Status</label>
                                    <span class="mt-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                               {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $customer->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Tanggal Daftar</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customer->created_at->format('d F Y H:i') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Statistik Transaksi</h3>
                            <div class="space-y-4">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">{{ $stats['total_transactions'] }}</div>
                                    <div class="text-sm text-blue-800">Total Transaksi</div>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</div>
                                    <div class="text-sm text-green-800">Total Belanja</div>
                                </div>
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-purple-600">Rp {{ number_format($stats['average_transaction'], 0, ',', '.') }}</div>
                                    <div class="text-sm text-purple-800">Rata-rata per Transaksi</div>
                                </div>
                                <div class="bg-orange-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-orange-600">{{ $stats['total_items_purchased'] }}</div>
                                    <div class="text-sm text-orange-800">Total Item Dibeli</div>
                                </div>
                                @if($stats['last_transaction'])
                                <div class="bg-yellow-50 p-4 rounded-lg">
                                    <div class="text-sm font-medium text-yellow-800">Transaksi Terakhir</div>
                                    <div class="text-sm text-yellow-600">{{ $stats['last_transaction']->format('d F Y H:i') }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Statistics -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Transaction Status Statistics -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Status Transaksi</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Selesai</span>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-green-600">{{ $stats['completed_transactions'] }}</span>
                                    <div class="w-12 bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $stats['total_transactions'] > 0 ? ($stats['completed_transactions'] / $stats['total_transactions'] * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                            @if($stats['pending_transactions'] > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Pending</span>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-yellow-600">{{ $stats['pending_transactions'] }}</span>
                                    <div class="w-12 bg-gray-200 rounded-full h-2">
                                        <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $stats['total_transactions'] > 0 ? ($stats['pending_transactions'] / $stats['total_transactions'] * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($stats['failed_transactions'] > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Gagal</span>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-red-600">{{ $stats['failed_transactions'] }}</span>
                                    <div class="w-12 bg-gray-200 rounded-full h-2">
                                        <div class="bg-red-500 h-2 rounded-full" style="width: {{ $stats['total_transactions'] > 0 ? ($stats['failed_transactions'] / $stats['total_transactions'] * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($stats['cancelled_transactions'] > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Dibatalkan</span>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-600">{{ $stats['cancelled_transactions'] }}</span>
                                    <div class="w-12 bg-gray-200 rounded-full h-2">
                                        <div class="bg-gray-500 h-2 rounded-full" style="width: {{ $stats['total_transactions'] > 0 ? ($stats['cancelled_transactions'] / $stats['total_transactions'] * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payment Method Statistics -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Metode Pembayaran</h3>
                        <div class="space-y-3">
                            @if($stats['cash_transactions'] > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Cash</span>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">{{ $stats['cash_transactions'] }}x</div>
                                    <div class="text-xs text-gray-500">Rp {{ number_format($stats['cash_spent'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                            @endif
                            @if($stats['card_transactions'] > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Kartu</span>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">{{ $stats['card_transactions'] }}x</div>
                                    <div class="text-xs text-gray-500">Rp {{ number_format($stats['card_spent'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                            @endif
                            @if($stats['ewallet_transactions'] > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">E-Wallet</span>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">{{ $stats['ewallet_transactions'] }}x</div>
                                    <div class="text-xs text-gray-500">Rp {{ number_format($stats['ewallet_spent'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                            @endif
                            @if($stats['online_transactions'] > 0)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Online</span>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">{{ $stats['online_transactions'] }}x</div>
                                    <div class="text-xs text-gray-500">Rp {{ number_format($stats['online_spent'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Monthly Comparison -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Perbandingan Bulanan</h3>
                        <div class="space-y-4">
                            <div class="border-l-4 border-blue-500 pl-4">
                                <div class="text-lg font-semibold text-blue-600">{{ $stats['this_month_transactions'] }}</div>
                                <div class="text-sm text-gray-600">Transaksi Bulan Ini</div>
                                <div class="text-xs text-gray-500">Rp {{ number_format($stats['this_month_spent'], 0, ',', '.') }}</div>
                            </div>
                            <div class="border-l-4 border-gray-400 pl-4">
                                <div class="text-lg font-semibold text-gray-600">{{ $stats['last_month_transactions'] }}</div>
                                <div class="text-sm text-gray-600">Transaksi Bulan Lalu</div>
                                <div class="text-xs text-gray-500">Rp {{ number_format($stats['last_month_spent'], 0, ',', '.') }}</div>
                            </div>
                            @php
                                $monthlyChange = $stats['last_month_transactions'] > 0 
                                    ? (($stats['this_month_transactions'] - $stats['last_month_transactions']) / $stats['last_month_transactions'] * 100)
                                    : ($stats['this_month_transactions'] > 0 ? 100 : 0);
                            @endphp
                            @if($monthlyChange != 0)
                            <div class="text-xs {{ $monthlyChange > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $monthlyChange > 0 ? '+' : '' }}{{ number_format($monthlyChange, 1) }}% dari bulan lalu
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Favorite Products & Monthly Trend -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Favorite Products -->
                @if($stats['favorite_products']->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Produk Favorit</h3>
                        <div class="space-y-3">
                            @foreach($stats['favorite_products'] as $index => $product)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-bold text-blue-600">{{ $index + 1 }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $product['product_name'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $product['quantity'] }} item dibeli</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">Rp {{ number_format($product['total_spent'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Monthly Trend -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Tren 6 Bulan Terakhir</h3>
                        <div class="space-y-3">
                            @foreach($stats['monthly_trend'] as $month)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ $month['month'] }}</span>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">{{ $month['transactions'] }} transaksi</div>
                                    <div class="text-xs text-gray-500">Rp {{ number_format($month['total'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Journey -->
            @if($stats['first_transaction'] && $stats['recent_transaction'])
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Journey</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="border-l-4 border-green-500 pl-4">
                            <h4 class="font-medium text-green-600 mb-2">Transaksi Pertama</h4>
                            <div class="text-sm text-gray-600">{{ $stats['first_transaction']->created_at->format('d F Y') }}</div>
                            <div class="text-sm text-gray-900">{{ $stats['first_transaction']->transaction_number }}</div>
                            <div class="text-sm text-gray-900">Rp {{ number_format($stats['first_transaction']->total, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-500">{{ ucfirst($stats['first_transaction']->payment_method) }}</div>
                        </div>
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h4 class="font-medium text-blue-600 mb-2">Transaksi Terbaru</h4>
                            <div class="text-sm text-gray-600">{{ $stats['recent_transaction']->created_at->format('d F Y') }}</div>
                            <div class="text-sm text-gray-900">{{ $stats['recent_transaction']->transaction_number }}</div>
                            <div class="text-sm text-gray-900">Rp {{ number_format($stats['recent_transaction']->total, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-500">{{ ucfirst($stats['recent_transaction']->payment_method) }}</div>
                        </div>
                    </div>
                    @php
                        $daysSinceFirst = $stats['first_transaction']->created_at->diffInDays(now());
                        $customerLifetime = $daysSinceFirst > 0 ? $daysSinceFirst : 1;
                        $averageDaysBetweenTransactions = $stats['total_transactions'] > 1 
                            ? $customerLifetime / ($stats['total_transactions'] - 1) 
                            : 0;
                    @endphp
                    @if($stats['total_transactions'] > 1)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-lg font-bold text-indigo-600">{{ $customerLifetime }}</div>
                                <div class="text-xs text-gray-500">Hari Customer</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-purple-600">{{ number_format($averageDaysBetweenTransactions, 1) }}</div>
                                <div class="text-xs text-gray-500">Hari/Transaksi</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-pink-600">{{ number_format($stats['total_spent'] / $customerLifetime, 0) }}</div>
                                <div class="text-xs text-gray-500">Rp/Hari</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Recent Transactions -->
            @if($customer->transactions->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Transaksi Terbaru</h3>
                        <a href="{{ route('transactions.index', ['customer_id' => $customer->id]) }}" class="text-blue-600 hover:text-blue-900 text-sm">
                            Lihat Semua
                        </a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No. Transaksi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($customer->transactions as $transaction)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $transaction->transaction_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $transaction->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div>{{ $transaction->items->count() }} item(s)</div>
                                        @if($transaction->items->count() > 0)
                                            <div class="text-xs text-gray-400">
                                                {{ $transaction->items->first()->product->name }}
                                                @if($transaction->items->count() > 1)
                                                    + {{ $transaction->items->count() - 1 }} lainnya
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Rp {{ number_format($transaction->total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ ucfirst($transaction->payment_method) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                   @if($transaction->status == 'completed') bg-green-100 text-green-800 
                                                   @elseif($transaction->status == 'pending') bg-yellow-100 text-yellow-800 
                                                   @elseif($transaction->status == 'failed') bg-red-100 text-red-800 
                                                   @elseif($transaction->status == 'cancelled') bg-gray-100 text-gray-800 
                                                   @else bg-blue-100 text-blue-800 @endif">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('transactions.show', $transaction) }}" class="text-blue-600 hover:text-blue-900">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-center text-gray-500">
                        <p>Customer belum memiliki riwayat transaksi</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>