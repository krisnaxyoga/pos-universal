<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                <i class="fas fa-user mr-2"></i>
                Detail User: {{ $user->name }}
            </h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('users.edit', $user) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
                <a href="{{ route('users.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- User Profile Card -->
        <div class="glass rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-white/20">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Informasi Profil</h3>
            </div>
            
            <div class="p-6">
                <div class="flex items-start space-x-6">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center">
                            <span class="text-white text-2xl font-bold">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- User Info -->
                    <div class="flex-1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Nama Lengkap</label>
                                <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $user->name }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                                <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $user->email }}</p>
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-1">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Terverifikasi
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Belum Terverifikasi
                                    </span>
                                @endif
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Role</label>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                        {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' : 
                                           ($user->role === 'supervisor' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800') }}">
                                        <i class="fas {{ $user->role === 'admin' ? 'fa-crown' : ($user->role === 'supervisor' ? 'fa-user-tie' : 'fa-cash-register') }} mr-1"></i>
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                                <div class="mt-1">
                                    @if($user->is_active)
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
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Bergabung</label>
                                <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $user->created_at->format('d F Y') }}</p>
                                <p class="text-sm text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Terakhir Diperbarui</label>
                                <p class="mt-1 text-lg text-gray-900 dark:text-white">{{ $user->updated_at->format('d F Y, H:i') }}</p>
                                <p class="text-sm text-gray-500">{{ $user->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Permissions -->
        <div class="glass rounded-xl shadow-sm">
            <div class="px-6 py-4 border-b border-white/20">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Hak Akses</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Berikut adalah hak akses yang dimiliki user ini berdasarkan role.
                </p>
            </div>
            
            <div class="p-6">
                @if($user->role === 'admin')
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-gray-900 dark:text-white">Manajemen User (CRUD semua user)</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-gray-900 dark:text-white">Manajemen Produk dan Kategori</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-gray-900 dark:text-white">Akses POS dan Transaksi</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-gray-900 dark:text-white">Laporan dan Analitik</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-gray-900 dark:text-white">Hapus dan Batalkan Transaksi</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-gray-900 dark:text-white">Akses ke semua fitur sistem</span>
                        </div>
                    </div>
                @elseif($user->role === 'supervisor')
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <i class="fas fa-times-circle text-red-500 mr-3"></i>
                            <span class="text-gray-500 dark:text-gray-400">Manajemen User</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-gray-900 dark:text-white">Manajemen Produk dan Kategori</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-gray-900 dark:text-white">Akses POS dan Transaksi</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-gray-900 dark:text-white">Laporan dan Analitik</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-times-circle text-red-500 mr-3"></i>
                            <span class="text-gray-500 dark:text-gray-400">Hapus dan Batalkan Transaksi</span>
                        </div>
                    </div>
                @else
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <i class="fas fa-times-circle text-red-500 mr-3"></i>
                            <span class="text-gray-500 dark:text-gray-400">Manajemen User</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-times-circle text-red-500 mr-3"></i>
                            <span class="text-gray-500 dark:text-gray-400">Manajemen Produk dan Kategori</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span class="text-gray-900 dark:text-white">Akses POS dan Transaksi</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-times-circle text-red-500 mr-3"></i>
                            <span class="text-gray-500 dark:text-gray-400">Laporan dan Analitik</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-times-circle text-red-500 mr-3"></i>
                            <span class="text-gray-500 dark:text-gray-400">Hapus dan Batalkan Transaksi</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        @if($user->id !== auth()->id())
            <div class="glass rounded-xl shadow-sm">
                <div class="px-6 py-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Aksi</h3>
                    
                    <div class="flex items-center space-x-3">
                        <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 rounded-lg font-medium text-sm
                                        {{ $user->is_active ? 'bg-yellow-500 hover:bg-yellow-600 text-white' : 'bg-green-500 hover:bg-green-600 text-white' }}">
                                <i class="fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }} mr-2"></i>
                                {{ $user->is_active ? 'Nonaktifkan User' : 'Aktifkan User' }}
                            </button>
                        </form>
                        
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium text-sm">
                                <i class="fas fa-trash mr-2"></i>
                                Hapus User
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>