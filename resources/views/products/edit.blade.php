<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                <i class="fas fa-edit mr-2"></i>
                Edit Produk: {{ $product->name }}
            </h2>
            <a href="{{ route('products.index') }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="glass rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-white/20">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Edit Informasi Produk</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Perbarui informasi produk di bawah ini.
            </p>
        </div>
        
        <div class="p-6">
            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <!-- Nama Produk -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama Produk <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name', $product->name) }}" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Deskripsi -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Deskripsi
                            </label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-500 @enderror dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- SKU -->
                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                SKU (Kode Produk) <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="sku" 
                                   id="sku" 
                                   value="{{ old('sku', $product->sku) }}" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('sku') border-red-500 @enderror dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('sku')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Barcode -->
                        <div>
                            <label for="barcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Barcode
                            </label>
                            <input type="text" 
                                   name="barcode" 
                                   id="barcode" 
                                   value="{{ old('barcode', $product->barcode) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('barcode') border-red-500 @enderror dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('barcode')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kategori -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Kategori <span class="text-red-500">*</span>
                            </label>
                            <select name="category_id" 
                                    id="category_id" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('category_id') border-red-500 @enderror dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                            {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Current Image Display -->
                        @if($product->image)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Gambar Saat Ini
                                </label>
                                <div class="mt-1">
                                    <img src="{{ asset('storage/' . $product->image) }}" 
                                         alt="{{ $product->name }}"
                                         class="w-32 h-32 object-cover rounded-lg border border-gray-300">
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <!-- Harga Jual -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Harga Jual <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                </div>
                                <input type="number" 
                                       name="price" 
                                       id="price" 
                                       value="{{ old('price', $product->price) }}" 
                                       required 
                                       min="0" 
                                       step="0.01"
                                       class="pl-12 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('price') border-red-500 @enderror dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Harga Beli -->
                        <div>
                            <label for="cost" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Harga Beli/Modal <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                </div>
                                <input type="number" 
                                       name="cost" 
                                       id="cost" 
                                       value="{{ old('cost', $product->cost) }}" 
                                       required 
                                       min="0" 
                                       step="0.01"
                                       class="pl-12 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('cost') border-red-500 @enderror dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </div>
                            @error('cost')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Stok -->
                        <div>
                            <label for="stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Stok Saat Ini <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="stock" 
                                   id="stock" 
                                   value="{{ old('stock', $product->stock) }}" 
                                   required 
                                   min="0"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('stock') border-red-500 @enderror dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('stock')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Minimum Stok -->
                        <div>
                            <label for="min_stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Minimum Stok <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="min_stock" 
                                   id="min_stock" 
                                   value="{{ old('min_stock', $product->min_stock) }}" 
                                   required 
                                   min="0"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('min_stock') border-red-500 @enderror dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            @error('min_stock')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Gambar Baru -->
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                @if($product->image)
                                    Ganti Gambar Produk
                                @else
                                    Gambar Produk
                                @endif
                            </label>
                            <input type="file" 
                                   name="image" 
                                   id="image" 
                                   accept="image/*"
                                   class="mt-1 block w-full text-sm text-gray-500 
                                          file:mr-4 file:py-2 file:px-4 
                                          file:rounded-lg file:border-0 
                                          file:text-sm file:font-semibold 
                                          file:bg-blue-50 file:text-blue-700 
                                          hover:file:bg-blue-100
                                          dark:file:bg-blue-900 dark:file:text-blue-300">
                            <p class="mt-1 text-sm text-gray-500">
                                @if($product->image)
                                    Kosongkan jika tidak ingin mengubah gambar. 
                                @endif
                                Format: JPG, PNG, GIF. Maksimal 2MB.
                            </p>
                            @error('image')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <div class="flex items-center">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" 
                                       name="is_active" 
                                       id="is_active"
                                       value="1" 
                                       {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Produk Aktif
                                </label>
                            </div>
                            @error('is_active')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Product Info -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-300 mb-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Informasi Produk:
                            </h4>
                            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <p><strong>Dibuat:</strong> {{ $product->created_at->format('d F Y, H:i') }}</p>
                                <p><strong>Terakhir diperbarui:</strong> {{ $product->updated_at->format('d F Y, H:i') }}</p>
                                @if($product->price && $product->cost)
                                    <p><strong>Margin:</strong> Rp {{ number_format($product->price - $product->cost, 0, ',', '.') }} 
                                       ({{ number_format((($product->price - $product->cost) / $product->price) * 100, 1) }}%)</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-6 mt-6 border-t border-white/20">
                    <a href="{{ route('products.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">
                        Batal
                    </a>
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Perbarui Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>