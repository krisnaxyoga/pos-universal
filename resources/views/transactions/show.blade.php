<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Transaction Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('transactions.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Transactions
                </a>
                @if($transaction->status === 'completed')
                <a href="{{ route('pos.receipt', $transaction->id) }}" 
                   target="_blank"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-print mr-2"></i>Print Receipt
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Transaction Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Transaction Number -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Transaction Number</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $transaction->transaction_number }}</p>
                        </div>
                        
                        <!-- Status -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status</h3>
                            <p class="mt-1">
                                @switch($transaction->status)
                                    @case('completed')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>Completed
                                        </span>
                                        @break
                                    @case('pending')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>Pending
                                        </span>
                                        @break
                                    @case('processing')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <i class="fas fa-spinner mr-1"></i>Processing
                                        </span>
                                        @break
                                    @case('cancelled')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>Cancelled
                                        </span>
                                        @break
                                    @case('failed')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Failed
                                        </span>
                                        @break
                                    @case('draft')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            <i class="fas fa-save mr-1"></i>Draft
                                        </span>
                                        @break
                                    @case('refunded')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                            <i class="fas fa-undo mr-1"></i>Refunded
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                @endswitch
                            </p>
                        </div>
                        
                        <!-- Total Amount -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Total Amount</h3>
                            <p class="mt-1 text-lg font-semibold text-gray-900">Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>
                        </div>
                        
                        <!-- Date -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Transaction Date</h3>
                            <p class="mt-1 text-sm text-gray-900">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Transaction Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Transaction Items -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Transaction Items</h3>
                            
                            @if($transaction->items && $transaction->items->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
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
                                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product_name }}">
                                                    </div>
                                                    @else
                                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-300 rounded-full flex items-center justify-center">
                                                        <i class="fas fa-box text-gray-500"></i>
                                                    </div>
                                                    @endif
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ $item->product_name }}</div>
                                                        @if($item->product)
                                                        <div class="text-sm text-gray-500">SKU: {{ $item->product->sku }}</div>
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
                                </table>
                            </div>
                            @else
                            <div class="text-center py-4">
                                <i class="fas fa-box-open text-gray-400 text-3xl mb-2"></i>
                                <p class="text-gray-500">No items found for this transaction</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Information (if online payment) -->
                    @if($transaction->payment_method === 'online' && ($transaction->ipaymu_session_id || $transaction->ipaymu_transaction_id))
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if($transaction->ipaymu_session_id)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Session ID</h4>
                                    <p class="mt-1 text-sm text-gray-900 font-mono">{{ $transaction->ipaymu_session_id }}</p>
                                </div>
                                @endif
                                
                                @if($transaction->ipaymu_transaction_id)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Payment Transaction ID</h4>
                                    <p class="mt-1 text-sm text-gray-900 font-mono">{{ $transaction->ipaymu_transaction_id }}</p>
                                </div>
                                @endif
                                
                                @if($transaction->ipaymu_payment_method)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Payment Method</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ ucfirst($transaction->ipaymu_payment_method) }}</p>
                                </div>
                                @endif
                                
                                @if($transaction->ipaymu_payment_channel)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Payment Channel</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ ucfirst($transaction->ipaymu_payment_channel) }}</p>
                                </div>
                                @endif
                                
                                @if($transaction->ipaymu_fee)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Payment Fee</h4>
                                    <p class="mt-1 text-sm text-gray-900">Rp {{ number_format($transaction->ipaymu_fee, 0, ',', '.') }}</p>
                                </div>
                                @endif
                                
                                @if($transaction->ipaymu_paid_at)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Paid At</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($transaction->ipaymu_paid_at)->format('d M Y, H:i') }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Transaction Summary -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Transaction Summary</h3>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Subtotal:</span>
                                    <span class="text-sm font-medium text-gray-900">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                                </div>
                                
                                @if($transaction->discount > 0)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Discount:</span>
                                    <span class="text-sm font-medium text-red-600">-Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
                                </div>
                                @endif
                                
                                @if($transaction->tax > 0)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Tax:</span>
                                    <span class="text-sm font-medium text-gray-900">Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span>
                                </div>
                                @endif
                                
                                <div class="border-t pt-3">
                                    <div class="flex justify-between">
                                        <span class="text-base font-medium text-gray-900">Total:</span>
                                        <span class="text-base font-bold text-gray-900">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Paid:</span>
                                    <span class="text-sm font-medium text-gray-900">Rp {{ number_format($transaction->paid, 0, ',', '.') }}</span>
                                </div>
                                
                                @if($transaction->change > 0)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Change:</span>
                                    <span class="text-sm font-medium text-green-600">Rp {{ number_format($transaction->change, 0, ',', '.') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Customer & Staff Info -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Transaction Info</h3>
                            
                            <div class="space-y-3">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Cashier</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ $transaction->user->name }}</p>
                                </div>
                                
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Payment Method</h4>
                                    <p class="mt-1 text-sm text-gray-900 capitalize">{{ str_replace('_', ' ', $transaction->payment_method) }}</p>
                                </div>
                                
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Transaction Date</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ $transaction->created_at->format('d M Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $transaction->created_at->format('H:i:s') }}</p>
                                </div>
                                
                                @if($transaction->updated_at != $transaction->created_at)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Last Updated</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ $transaction->updated_at->format('d M Y, H:i') }}</p>
                                </div>
                                @endif
                                
                                @if($transaction->notes)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Notes</h4>
                                    <p class="mt-1 text-sm text-gray-900">{{ $transaction->notes }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                            
                            <div class="space-y-3">
                                @if($transaction->status === 'completed')
                                <a href="{{ route('pos.receipt', $transaction->id) }}" 
                                   target="_blank"
                                   class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <i class="fas fa-print mr-2"></i>Print Receipt
                                </a>
                                @endif
                                
                                @if(in_array($transaction->status, ['failed', 'cancelled']) && auth()->user()->role === 'admin')
                                <form action="{{ route('transactions.retry', $transaction->id) }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <i class="fas fa-redo mr-2"></i>Retry Payment
                                    </button>
                                </form>
                                @endif
                                
                                @if($transaction->status === 'pending' && auth()->user()->role === 'admin')
                                <form action="{{ route('transactions.cancel', $transaction->id) }}" method="POST" class="w-full" 
                                      onsubmit="return confirm('Are you sure you want to cancel this transaction?')">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" 
                                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        <i class="fas fa-times mr-2"></i>Cancel Transaction
                                    </button>
                                </form>
                                @endif
                                
                                @if($transaction->canBeRefunded() && auth()->user()->role === 'admin')
                                <button 
                                    onclick="openRefundModal()"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <i class="fas fa-undo mr-2"></i>Refund Transaction
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Refund Modal -->
    <div id="refundModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Refund Transaksi</h3>
                <button onclick="closeRefundModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('transactions.refund', $transaction->id) }}" method="POST" onsubmit="return confirmRefund()">
                @csrf
                
                <div class="space-y-4">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <div class="text-sm text-gray-600">
                            <p><strong>Transaksi:</strong> {{ $transaction->transaction_number }}</p>
                            <p><strong>Total Awal:</strong> Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>
                            <p><strong>Metode Bayar:</strong> {{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Refund</label>
                        <input 
                            type="number" 
                            name="refund_amount" 
                            min="1" 
                            max="{{ $transaction->total }}" 
                            step="1000"
                            placeholder="Masukkan jumlah refund"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-orange-500"
                            required
                        >
                        <div class="mt-1 flex justify-between text-xs text-gray-500">
                            <span>Min: Rp 1.000</span>
                            <span>Max: Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Refund</label>
                        <textarea 
                            name="refund_reason" 
                            rows="3"
                            placeholder="Jelaskan alasan refund..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:border-orange-500 focus:ring-orange-500"
                            required
                        ></textarea>
                    </div>

                    <div class="bg-yellow-50 p-3 rounded-lg">
                        <div class="text-sm text-yellow-800">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>Perhatian:</strong> Refund akan mengembalikan stok produk ke inventory dan membuat transaksi refund baru.
                        </div>
                    </div>
                </div>

                <div class="flex space-x-3 mt-6">
                    <button 
                        type="button"
                        onclick="closeRefundModal()"
                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded"
                    >
                        <i class="fas fa-undo mr-2"></i>
                        Proses Refund
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRefundModal() {
            document.getElementById('refundModal').classList.remove('hidden');
        }

        function closeRefundModal() {
            document.getElementById('refundModal').classList.add('hidden');
        }

        function confirmRefund() {
            const amount = document.querySelector('input[name="refund_amount"]').value;
            const reason = document.querySelector('textarea[name="refund_reason"]').value;
            
            if (!amount || !reason) {
                alert('Mohon lengkapi semua field');
                return false;
            }
            
            return confirm(`Yakin ingin refund sebesar Rp ${parseInt(amount).toLocaleString('id-ID')}?\n\nAlasan: ${reason}\n\nTindakan ini tidak dapat dibatalkan.`);
        }

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRefundModal();
            }
        });
    </script>
</x-app-layout>