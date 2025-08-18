<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                <i class="fas fa-user-edit mr-2"></i>
                Edit User: {{ $user->name }}
            </h2>
            <a href="{{ route('users.index') }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="glass rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-white/20">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Edit Informasi User</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Perbarui informasi user di bawah ini.
                </p>
            </div>
            
            <form action="{{ route('users.update', $user) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}"
                           required
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $user->email) }}"
                           required
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select id="role" 
                            name="role" 
                            required
                            class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Role</option>
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                            Admin - Akses penuh ke sistem
                        </option>
                        <option value="supervisor" {{ old('role', $user->role) == 'supervisor' ? 'selected' : '' }}>
                            Supervisor - Manajemen produk dan laporan
                        </option>
                        <option value="kasir" {{ old('role', $user->role) == 'kasir' ? 'selected' : '' }}>
                            Kasir - POS dan transaksi
                        </option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Status Akun
                    </label>
                    <div class="flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1"
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Akun aktif
                        </label>
                    </div>
                    @if($user->id === auth()->id())
                        <p class="mt-1 text-sm text-amber-600">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Anda tidak dapat menonaktifkan akun sendiri
                        </p>
                    @endif
                </div>

                <!-- Password Section -->
                <div class="border-t border-white/20 pt-6">
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">
                        Ubah Password (Opsional)
                    </h4>
                    
                    <!-- New Password -->
                    <div class="space-y-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Password Baru
                            </label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Kosongkan jika tidak ingin mengubah password</p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Konfirmasi Password Baru
                            </label>
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- User Info -->
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-300 mb-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Informasi User:
                    </h4>
                    <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <p><strong>Dibuat:</strong> {{ $user->created_at->format('d F Y, H:i') }}</p>
                        <p><strong>Terakhir diperbarui:</strong> {{ $user->updated_at->format('d F Y, H:i') }}</p>
                        @if($user->email_verified_at)
                            <p><strong>Email terverifikasi:</strong> {{ $user->email_verified_at->format('d F Y, H:i') }}</p>
                        @else
                            <p class="text-amber-600"><strong>Email belum terverifikasi</strong></p>
                        @endif
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-white/20">
                    <a href="{{ route('users.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">
                        Batal
                    </a>
                    <button type="submit" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Perbarui User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Disable is_active checkbox if editing own account
        @if($user->id === auth()->id())
            document.getElementById('is_active').disabled = true;
        @endif
    </script>
</x-app-layout>