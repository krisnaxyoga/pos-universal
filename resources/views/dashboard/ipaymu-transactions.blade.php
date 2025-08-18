<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('iPaymu Transactions') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-receipt text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Total Transactions</div>
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_transactions']) }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-circle text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Completed</div>
                            <div class="text-2xl font-bold text-green-600">{{ number_format($stats['completed_transactions']) }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Pending</div>
                            <div class="text-2xl font-bold text-yellow-600">{{ number_format($stats['pending_transactions']) }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-times-circle text-white text-sm"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Failed</div>
                            <div class="text-2xl font-bold text-red-600">{{ number_format($stats['failed_transactions']) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Total Revenue</div>
                            <div class="text-3xl font-bold text-green-600">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</div>
                        </div>
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-money-bill-wave text-white"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Total Fees</div>
                            <div class="text-3xl font-bold text-orange-600">Rp {{ number_format($stats['total_fees'], 0, ',', '.') }}</div>
                        </div>
                        <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-percentage text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Breakdown -->
            @if($paymentMethods->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Methods Breakdown</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($paymentMethods as $method)
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-500">{{ strtoupper($method->ipaymu_payment_method) }}</div>
                                    <div class="text-xl font-bold text-gray-900">{{ number_format($method->count) }} transactions</div>
                                    <div class="text-sm text-green-600">Rp {{ number_format($method->total_amount, 0, ',', '.') }}</div>
                                </div>
                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                    @if($method->ipaymu_payment_method == 'qris')
                                        <i class="fas fa-qrcode text-white text-sm"></i>
                                    @elseif($method->ipaymu_payment_method == 'va')
                                        <i class="fas fa-university text-white text-sm"></i>
                                    @else
                                        <i class="fas fa-credit-card text-white text-sm"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Transaction number, customer..." 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Status</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Pending</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Methods</option>
                                <option value="qris" {{ request('payment_method') == 'qris' ? 'selected' : '' }}>QRIS</option>
                                <option value="va" {{ request('payment_method') == 'va' ? 'selected' : '' }}>Virtual Account</option>
                                <option value="cstore" {{ request('payment_method') == 'cstore' ? 'selected' : '' }}>Convenience Store</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fas fa-search mr-2"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($transactions as $transaction)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $transaction->transaction_number }}</div>
                                        <div class="text-sm text-gray-500">iPaymu ID: {{ $transaction->ipaymu_transaction_id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($transaction->customer)
                                            <div class="text-sm font-medium text-gray-900">{{ $transaction->customer->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $transaction->customer->email }}</div>
                                        @else
                                            <div class="text-sm text-gray-500">Guest Customer</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ strtoupper($transaction->ipaymu_payment_method) }}
                                            @if($transaction->ipaymu_payment_channel)
                                                <span class="text-gray-500">/ {{ strtoupper($transaction->ipaymu_payment_channel) }}</span>
                                            @endif
                                        </div>
                                        @if($transaction->ipaymu_fee)
                                            <div class="text-sm text-gray-500">Fee: Rp {{ number_format($transaction->ipaymu_fee, 0, ',', '.') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">Rp {{ number_format($transaction->total, 0, ',', '.') }}</div>
                                        @if($transaction->paid > 0)
                                            <div class="text-sm text-green-600">Paid: Rp {{ number_format($transaction->paid, 0, ',', '.') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($transaction->status == 'completed')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>Completed
                                            </span>
                                        @elseif($transaction->status == 'processing')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i>Pending
                                            </span>
                                        @elseif($transaction->status == 'failed')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1"></i>Failed
                                            </span>
                                        @endif
                                        <div class="text-xs text-gray-500 mt-1">{{ $transaction->ipaymu_status }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ $transaction->created_at->format('d/m/Y H:i') }}</div>
                                        @if($transaction->ipaymu_paid_at)
                                            <div class="text-green-600">Paid: {{ \Carbon\Carbon::parse($transaction->ipaymu_paid_at)->format('d/m/Y H:i') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('ipaymu.transaction.show', $transaction) }}" 
                                           class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fas fa-eye mr-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        <div class="py-4">
                                            <i class="fas fa-inbox text-4xl text-gray-400 mb-2"></i>
                                            <p>No iPaymu transactions found.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($transactions->hasPages())
                    <div class="mt-6">
                        {{ $transactions->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>