<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Customer Management') }}
            </h2>
            <a href="{{ route('customers.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Tambah Customer
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Filter Section -->
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                        <form method="GET" action="{{ route('customers.index') }}" class="flex flex-wrap gap-4">
                            <div class="flex-1 min-w-64">
                                <input type="text" name="search" value="{{ request('search') }}" 
                                       placeholder="Cari nama, email, telepon, atau kode customer..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <select name="status" class="px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="">Semua Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                                </select>
                            </div>
                            <div>
                                <select name="sort_by" class="px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Tanggal Dibuat</option>
                                    <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Nama</option>
                                    <option value="total_spent" {{ request('sort_by') == 'total_spent' ? 'selected' : '' }}>Total Belanja</option>
                                    <option value="total_transactions" {{ request('sort_by') == 'total_transactions' ? 'selected' : '' }}>Total Transaksi</option>
                                </select>
                            </div>
                            <div>
                                <select name="sort_order" class="px-3 py-2 border border-gray-300 rounded-md">
                                    <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Terbaru</option>
                                    <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Terlama</option>
                                </select>
                            </div>
                            <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Filter
                            </button>
                            <a href="{{ route('customers.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Reset
                            </a>
                            <button type="button" onclick="exportCustomers()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Export CSV
                            </button>
                        </form>
                    </div>

                    <!-- Customers Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Customer
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Kontak
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Statistik
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal Daftar
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($customers as $customer)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $customer->customer_code }}</div>
                                            @if($customer->gender)
                                                <div class="text-xs text-gray-400">{{ ucfirst($customer->gender) }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $customer->email }}</div>
                                        <div class="text-sm text-gray-500">{{ $customer->phone }}</div>
                                        @if($customer->address)
                                            <div class="text-xs text-gray-400">{{ Str::limit($customer->address, 30) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            Rp {{ number_format($customer->total_spent, 0, ',', '.') }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $customer->total_transactions }} transaksi
                                        </div>
                                        @if($customer->last_transaction_at)
                                            <div class="text-xs text-gray-400">
                                                Terakhir: {{ $customer->last_transaction_at->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                   {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $customer->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $customer->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('customers.show', $customer) }}" 
                                               class="text-blue-600 hover:text-blue-900">Detail</a>
                                            <a href="{{ route('customers.edit', $customer) }}" 
                                               class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            
                                            <form method="POST" action="{{ route('customers.toggle-status', $customer) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="{{ $customer->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }}"
                                                        onclick="return confirm('Yakin ingin mengubah status customer?')">
                                                    {{ $customer->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                            
                                            @if($customer->transactions()->count() == 0)
                                                <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-red-600 hover:text-red-900"
                                                            onclick="return confirm('Yakin ingin menghapus customer ini?')">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        Tidak ada data customer
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($customers->hasPages())
                        <div class="mt-6">
                            {{ $customers->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportCustomers() {
            fetch('{{ route("customers.export") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Convert to CSV
                        const csvContent = convertToCSV(data.data);
                        downloadCSV(csvContent, data.filename);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal export data customer');
                });
        }

        function convertToCSV(data) {
            if (!data.length) return '';
            
            const headers = Object.keys(data[0]);
            const csvContent = [
                headers.join(','),
                ...data.map(row => headers.map(header => `"${row[header] || ''}"`).join(','))
            ].join('\n');
            
            return csvContent;
        }

        function downloadCSV(csvContent, filename) {
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</x-app-layout>