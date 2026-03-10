<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Manajemen Produk
            </h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('products.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-sm">
                    <i class="fas fa-plus mr-1"></i> Tambah
                </a>
                <button onclick="document.getElementById('import-modal').classList.remove('hidden')" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded text-sm">
                    <i class="fas fa-file-import mr-1"></i> Import
                </button>
                <a href="{{ route('products.export') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-3 rounded text-sm">
                    <i class="fas fa-file-export mr-1"></i> Export
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Offline product notice -->
    <div id="offline-product-notice" class="hidden mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
        <i class="fas fa-wifi mr-1"></i>
        <strong>Mode Offline</strong> — Menampilkan data produk dari cache lokal. Perubahan akan disinkronkan saat online.
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 sm:p-6 text-gray-900">
            <!-- Filters -->
            <div class="mb-6">
                <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="col-span-2 sm:col-span-1">
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari produk..."
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <select name="category_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-3 rounded flex-1 text-sm">
                            Filter
                        </button>
                        @if(request()->hasAny(['search', 'category_id', 'status']))
                            <a href="{{ route('products.index') }}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded text-sm">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Mobile Card View -->
            <div class="block md:hidden space-y-3" id="product-cards-mobile">
                @forelse($products as $product)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <!-- Image -->
                            <div class="flex-shrink-0 h-14 w-14">
                                @if($product->image)
                                    <img class="h-14 w-14 rounded-lg object-cover" src="{{ asset($product->image) }}" alt="{{ $product->name }}">
                                @else
                                    <div class="h-14 w-14 rounded-lg bg-gray-200 flex items-center justify-center">
                                        <svg class="h-7 w-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $product->name }}</h3>
                                        <p class="text-xs text-gray-500">{{ $product->sku }}</p>
                                    </div>
                                    @if($product->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 whitespace-nowrap flex-shrink-0">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 whitespace-nowrap flex-shrink-0">
                                            Nonaktif
                                        </span>
                                    @endif
                                </div>

                                <!-- Price & Stock -->
                                <div class="mt-2 text-sm">
                                    <span class="font-semibold text-gray-900">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                    <span class="text-xs text-gray-400 ml-1">(Modal: Rp {{ number_format($product->cost, 0, ',', '.') }})</span>
                                </div>

                                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                                    <span><i class="fas fa-layer-group mr-1"></i>{{ $product->category->name }}</span>
                                    <span class="{{ $product->isLowStock() ? 'text-red-600 font-bold' : '' }}">
                                        <i class="fas fa-cubes mr-1"></i>Stok: {{ $product->stock }}/{{ $product->initial_stock }}
                                        @if($product->isLowStock())
                                            <i class="fas fa-exclamation-triangle ml-1"></i>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-3 text-sm">
                                <a href="{{ route('products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-eye mr-1"></i>Lihat
                                </a>
                                <a href="{{ route('products.edit', $product) }}" class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash mr-1"></i>Hapus
                                    </button>
                                </form>
                            </div>
                            <div class="text-xs">
                                @if($product->barcode)
                                    <a href="{{ route('products.print-barcode', $product) }}" target="_blank"
                                       class="text-purple-600 hover:text-purple-900">
                                        <i class="fas fa-barcode mr-1"></i>Print
                                    </a>
                                @else
                                    <button onclick="generateBarcode({{ $product->id }})" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-barcode mr-1"></i>Generate
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-box-open text-3xl mb-2 block"></i>
                        <p>Belum ada produk.</p>
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Awal / Sisa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($products as $product)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($product->image)
                                                <img class="h-10 w-10 rounded-full object-cover" src="{{ asset($product->image) }}" alt="{{ $product->name }}">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $product->sku }}</div>
                                            @if($product->barcode)
                                                <div class="text-xs text-gray-400">{{ $product->barcode }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $product->category->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-500">Modal: Rp {{ number_format($product->cost, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs text-gray-500">Awal: {{ $product->initial_stock }}</div>
                                    <div class="text-sm font-medium text-gray-900">Sisa: {{ $product->stock }}</div>
                                    @if($product->isLowStock())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Stok Menipis</span>
                                    @endif
                                    <div class="text-xs text-gray-500">Min: {{ $product->min_stock }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($product->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex flex-col space-y-1">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('products.show', $product) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                                            <a href="{{ route('products.edit', $product) }}" class="text-green-600 hover:text-green-900">Edit</a>
                                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        </div>
                                        <div class="flex space-x-2 text-xs">
                                            @if($product->barcode)
                                                <a href="{{ route('products.print-barcode', $product) }}" target="_blank"
                                                   class="text-purple-600 hover:text-purple-900" title="Print Barcode">
                                                    <i class="fas fa-print mr-1"></i>Print
                                                </a>
                                                <button onclick="regenerateBarcode({{ $product->id }})"
                                                        class="text-orange-600 hover:text-orange-900" title="Regenerate Barcode">
                                                    <i class="fas fa-sync mr-1"></i>Regen
                                                </button>
                                            @else
                                                <button onclick="generateBarcode({{ $product->id }})"
                                                        class="text-blue-600 hover:text-blue-900" title="Generate Barcode">
                                                    <i class="fas fa-barcode mr-1"></i>Generate
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada produk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $products->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="import-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Import Produk dari Excel</h3>
                <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File Excel</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-xs text-gray-500">Format: xlsx, xls, csv. Maks 5MB</p>
                    </div>
                    <div class="mb-4 p-3 bg-gray-50 rounded text-xs text-gray-600">
                        <p class="font-medium mb-1">Format kolom Excel:</p>
                        <p>nama, sku, barcode, deskripsi, kategori, harga_jual, harga_modal, stok, stok_minimum</p>
                        <p class="mt-1 text-blue-600">Baris pertama harus berisi header kolom</p>
                        <a href="{{ route('products.import-template') }}" class="inline-flex items-center mt-2 text-blue-700 hover:text-blue-900 font-medium">
                            <i class="fas fa-download mr-1"></i> Download Template
                        </a>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="document.getElementById('import-modal').classList.add('hidden')"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                            Batal
                        </button>
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Barcode Management JavaScript -->
    <script>
        function generateBarcode(productId) {
            fetch(`/products/${productId}/generate-barcode`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) { window.location.reload(); }
                else { alert('Error: ' + data.message); }
            })
            .catch(error => { console.error('Error:', error); alert('Terjadi kesalahan saat generate barcode'); });
        }

        function regenerateBarcode(productId) {
            if (confirm('Apakah Anda yakin ingin regenerate barcode? Barcode lama akan diganti.')) {
                fetch(`/products/${productId}/regenerate-barcode`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) { window.location.reload(); }
                    else { alert('Error: ' + data.message); }
                })
                .catch(error => { console.error('Error:', error); alert('Terjadi kesalahan saat regenerate barcode'); });
            }
        }

        // Offline: intercept delete forms when offline
        document.querySelectorAll('form[action*="products/"]').forEach(form => {
            if (!form.querySelector('input[name="_method"][value="DELETE"]')) return;
            form.onsubmit = null;
            form.addEventListener('submit', async function(e) {
                const isOnline = window.connectivityMonitor ? window.connectivityMonitor.isOnline : navigator.onLine;
                if (isOnline) {
                    if (!confirm('Yakin ingin menghapus produk ini?')) { e.preventDefault(); return; }
                    return;
                }
                e.preventDefault();
                const match = form.action.match(/products\/(\d+)/);
                if (!match) return;
                const productId = parseInt(match[1]);
                const card = form.closest('.border');
                const row = form.closest('tr');
                const container = card || row;
                const nameEl = container ? container.querySelector('.font-semibold, .font-medium') : null;
                const productName = nameEl ? nameEl.textContent.trim() : 'Unknown';
                const deleted = await OfflineProducts.deleteOffline(productId, productName);
                if (deleted && container) container.remove();
            });
        });
    </script>

    <script src="/js/pwa/offline-products.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                if (window.OfflineProducts) window.OfflineProducts.renderOfflineList();
            }, 500);

            if (navigator.onLine) {
                fetch('/products/create', { credentials: 'same-origin' }).catch(() => {});
                const categoryOptions = document.querySelectorAll('select[name="category_id"] option[value]');
                if (categoryOptions.length > 0 && window.posDB) {
                    const categories = [];
                    categoryOptions.forEach(opt => {
                        if (opt.value) categories.push({ id: parseInt(opt.value), name: opt.textContent.trim() });
                    });
                    window.posDB.setMeta('categories', categories).catch(() => {});
                }
            }
        });
    </script>
</x-app-layout>
