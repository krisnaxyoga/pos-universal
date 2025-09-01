<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                <i class="fas fa-box mr-2"></i>
                Detail Produk: {{ $product->name }}
            </h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('products.edit', $product) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
                <a href="{{ route('products.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Product Details Card -->
        <div class="glass rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-white/20">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Informasi Produk</h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Product Image -->
                    <div class="lg:col-span-1">
                        @if($product->image)
                            <div class="aspect-square">
                                <img src="{{ asset('storage/' . $product->image) }}" 
                                     alt="{{ $product->name }}"
                                     class="w-full h-full object-cover rounded-lg border border-gray-200 dark:border-gray-600">
                            </div>
                        @else
                            <div class="aspect-square bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-image text-4xl text-gray-400 mb-2"></i>
                                    <p class="text-gray-500 dark:text-gray-400">Tidak ada gambar</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Product Info -->
                    <div class="lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Nama Produk</label>
                                <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $product->name }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Kategori</label>
                                <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $product->category->name }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">SKU</label>
                                <p class="mt-1 text-lg text-gray-900 dark:text-white font-mono">{{ $product->sku }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Barcode</label>
                                <p class="mt-1 text-lg text-gray-900 dark:text-white font-mono">
                                    {{ $product->barcode ?: '-' }}
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Harga Jual</label>
                                <p class="mt-1 text-lg font-bold text-green-600 dark:text-green-400">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Harga Modal</label>
                                <p class="mt-1 text-lg text-gray-900 dark:text-white">
                                    Rp {{ number_format($product->cost, 0, ',', '.') }}
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Stok Saat Ini</label>
                                <p class="mt-1 text-lg text-gray-900 dark:text-white">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                        {{ $product->stock <= $product->min_stock ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $product->stock }} unit
                                    </span>
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Minimum Stok</label>
                                <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $product->min_stock }} unit</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                                <div class="mt-1">
                                    @if($product->is_active)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Nonaktif
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Margin Keuntungan</label>
                                <p class="mt-1 text-lg text-gray-900 dark:text-white">
                                    <span class="text-blue-600 dark:text-blue-400 font-semibold">
                                        Rp {{ number_format($product->price - $product->cost, 0, ',', '.') }}
                                    </span>
                                    <span class="text-sm text-gray-500 ml-2">
                                        ({{ number_format((($product->price - $product->cost) / $product->price) * 100, 1) }}%)
                                    </span>
                                </p>
                            </div>
                            
                            @if($product->description)
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Deskripsi</label>
                                    <p class="mt-1 text-gray-900 dark:text-white">{{ $product->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Alert -->
        @if($product->stock <= $product->min_stock)
            <div class="glass rounded-xl shadow-sm border-l-4 border-red-500">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-red-800 dark:text-red-300">
                                Peringatan Stok Menipis
                            </h3>
                            <p class="text-red-700 dark:text-red-400">
                                Stok produk ini sudah mencapai batas minimum. Segera lakukan restocking untuk menghindari kehabisan stok.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Barcode Section -->
        <div class="glass rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-white/20">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        <i class="fas fa-barcode mr-2"></i>
                        Barcode Produk
                    </h3>
                    <div class="flex items-center space-x-2">
                        @if(!$product->barcode)
                            <button onclick="generateBarcode({{ $product->id }})" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-lg text-sm">
                                <i class="fas fa-plus mr-1"></i>
                                Generate Barcode
                            </button>
                        @else
                            <button onclick="regenerateBarcode({{ $product->id }})" 
                                    class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-3 rounded-lg text-sm">
                                <i class="fas fa-sync mr-1"></i>
                                Regenerate
                            </button>
                            <a href="{{ route('products.print-barcode', $product) }}" 
                               target="_blank"
                               class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg text-sm">
                                <i class="fas fa-print mr-1"></i>
                                Print
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                @if($product->barcode)
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Barcode Display -->
                        <div class="text-center">
                            <div id="barcode-image" class="bg-white p-4 rounded-lg inline-block">
                                @if($product->barcode_image)
                                    <img src="{{ $product->barcode_image }}" 
                                         alt="Barcode: {{ $product->barcode }}"
                                         class="mx-auto">
                                @endif
                            </div>
                            <p class="mt-2 text-lg font-mono text-gray-900 dark:text-white">{{ $product->barcode }}</p>
                        </div>
                        
                        <!-- Barcode Info -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Kode Barcode</label>
                                <div class="mt-1 flex items-center">
                                    <input type="text" 
                                           value="{{ $product->barcode }}" 
                                           readonly
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-gray-900 bg-gray-50 font-mono">
                                    <button onclick="copyBarcode('{{ $product->barcode }}')" 
                                            class="ml-2 px-3 py-2 bg-gray-500 hover:bg-gray-700 text-white rounded-md">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Format Barcode</label>
                                <p class="mt-1 text-gray-900 dark:text-white">EAN-13</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Aktif
                                </span>
                            </div>
                            
                            <div class="pt-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Barcode ini dapat digunakan untuk scanning di sistem POS atau aplikasi scanner barcode lainnya.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-barcode text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Barcode Belum Dibuat</h4>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">
                            Generate barcode untuk produk ini agar dapat discanning di sistem POS.
                        </p>
                        <button onclick="generateBarcode({{ $product->id }})" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg">
                            <i class="fas fa-plus mr-2"></i>
                            Generate Barcode Sekarang
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Product Statistics -->
        <div class="glass rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-white/20">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Informasi Tambahan</h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Dibuat</label>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $product->created_at->format('d F Y, H:i') }}</p>
                        <p class="text-sm text-gray-500">{{ $product->created_at->diffForHumans() }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Terakhir Diperbarui</label>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $product->updated_at->format('d F Y, H:i') }}</p>
                        <p class="text-sm text-gray-500">{{ $product->updated_at->diffForHumans() }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Total Nilai Stok</label>
                        <p class="mt-1 text-lg font-semibold text-blue-600 dark:text-blue-400">
                            Rp {{ number_format($product->stock * $product->cost, 0, ',', '.') }}
                        </p>
                        <p class="text-sm text-gray-500">Berdasarkan harga modal</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="glass rounded-xl shadow-sm">
            <div class="px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Aksi</h3>
                
                <div class="flex items-center space-x-3">
                    <a href="{{ route('products.edit', $product) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium text-sm">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Produk
                    </a>
                    
                    @if(!$product->transactionItems()->count())
                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium text-sm">
                                <i class="fas fa-trash mr-2"></i>
                                Hapus Produk
                            </button>
                        </form>
                    @else
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <i class="fas fa-info-circle mr-1"></i>
                            Produk tidak dapat dihapus karena sudah pernah ditransaksikan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Barcode JavaScript -->
    <script>
        // Generate barcode for product
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
                if (data.success) {
                    // Reload the page to show the generated barcode
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat generate barcode');
            });
        }

        // Regenerate barcode for product
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
                    if (data.success) {
                        // Reload the page to show the new barcode
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat regenerate barcode');
                });
            }
        }

        // Copy barcode to clipboard
        function copyBarcode(barcode) {
            navigator.clipboard.writeText(barcode).then(function() {
                // Show toast or alert
                const button = event.target.closest('button');
                const originalContent = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i>';
                button.classList.add('bg-green-500');
                
                setTimeout(() => {
                    button.innerHTML = originalContent;
                    button.classList.remove('bg-green-500');
                }, 2000);
            }).catch(function() {
                alert('Gagal menyalin barcode ke clipboard');
            });
        }
    </script>
</x-app-layout>