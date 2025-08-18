<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('iPaymu Transaction Detail') }}
            </h2>
            <a href="{{ route('ipaymu.transactions') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Transaction Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Transaction Summary</h3>
                        @if($transaction->status == 'completed')
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-2"></i>Completed
                            </span>
                        @elseif($transaction->status == 'processing')
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-2"></i>Pending
                            </span>
                        @elseif($transaction->status == 'failed')
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-2"></i>Failed
                            </span>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Transaction Number</dt>
                            <dd class="mt-1 text-lg font-bold text-gray-900">{{ $transaction->transaction_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">iPaymu Transaction ID</dt>
                            <dd class="mt-1 text-lg font-bold text-blue-600">{{ $transaction->ipaymu_transaction_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Amount</dt>
                            <dd class="mt-1 text-lg font-bold text-green-600">Rp {{ number_format($transaction->total, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Fee</dt>
                            <dd class="mt-1 text-lg font-bold text-orange-600">Rp {{ number_format($transaction->ipaymu_fee ?? 0, 0, ',', '.') }}</dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- iPaymu Details and Customer Info -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- iPaymu Payment Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="fas fa-credit-card mr-2 text-blue-500"></i>iPaymu Payment Details
                        </h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Session ID</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $transaction->ipaymu_session_id }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Reference ID</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $transaction->ipaymu_reference_id }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">
                                        {{ strtoupper($transaction->ipaymu_payment_method) }}
                                    </span>
                                    @if($transaction->ipaymu_payment_channel)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800 ml-2">
                                            {{ strtoupper($transaction->ipaymu_payment_channel) }}
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            @if($transaction->ipaymu_payment_code)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Payment Code</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $transaction->ipaymu_payment_code }}</dd>
                            </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">iPaymu Status</dt>
                                <dd class="mt-1 text-sm">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded 
                                        @if($transaction->ipaymu_status == 'berhasil') bg-green-100 text-green-800
                                        @elseif($transaction->ipaymu_status == 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $transaction->ipaymu_status }} ({{ $transaction->ipaymu_status_code }})
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Payment URL</dt>
                                <dd class="mt-1 text-sm">
                                    @if($transaction->ipaymu_payment_url)
                                        <a href="{{ $transaction->ipaymu_payment_url }}" target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 underline break-all">
                                            {{ $transaction->ipaymu_payment_url }}
                                        </a>
                                    @else
                                        <span class="text-gray-500">N/A</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="fas fa-user mr-2 text-green-500"></i>Customer Information
                        </h3>
                        @if($transaction->customer)
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $transaction->customer->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $transaction->customer->email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $transaction->customer->phone }}</dd>
                                </div>
                                @if($transaction->customer->address)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $transaction->customer->address }}</dd>
                                </div>
                                @endif
                            </dl>
                        @elseif($transaction->customer_info)
                            <dl class="space-y-3">
                                @foreach($transaction->customer_info as $key => $value)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $value }}</dd>
                                </div>
                                @endforeach
                            </dl>
                        @else
                            <p class="text-gray-500">No customer information available.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Timeline -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-clock mr-2 text-purple-500"></i>Payment Timeline
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-plus text-white text-xs"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">Transaction Created</div>
                                <div class="text-sm text-gray-500">{{ $transaction->created_at->format('d M Y H:i:s') }}</div>
                            </div>
                        </div>
                        
                        @if($transaction->ipaymu_expired_date)
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-hourglass-half text-white text-xs"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">Payment Expires</div>
                                <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($transaction->ipaymu_expired_date)->format('d M Y H:i:s') }}</div>
                            </div>
                        </div>
                        @endif
                        
                        @if($transaction->ipaymu_paid_at)
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-white text-xs"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">Payment Completed</div>
                                <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($transaction->ipaymu_paid_at)->format('d M Y H:i:s') }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Transaction Items -->
            @if($transaction->items && $transaction->items->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-shopping-cart mr-2 text-indigo-500"></i>Transaction Items
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($transaction->items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($item->product && $item->product->image)
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full object-cover" 
                                                         src="{{ Storage::url($item->product->image) }}" 
                                                         alt="{{ $item->product_name }}">
                                                </div>
                                            @else
                                                <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-box text-gray-400 text-sm"></i>
                                                </div>
                                            @endif
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $item->product_name }}</div>
                                                @if($item->product)
                                                    <div class="text-sm text-gray-500">SKU: {{ $item->product->sku ?? 'N/A' }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Rp {{ number_format($item->product_price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Total:</td>
                                    <td class="px-6 py-3 text-sm font-bold text-gray-900">
                                        Rp {{ number_format($transaction->total, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>