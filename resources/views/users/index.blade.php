<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                <i class="fas fa-users mr-2"></i>
                Manajemen User
            </h2>
            <a href="{{ route('users.create') }}" 
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Tambah User
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="glass rounded-xl p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <div class="ml-3">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total User</dt>
                        <dd class="text-lg font-bold text-gray-900 dark:text-white">{{ $users->total() }}</dd>
                    </div>
                </div>
            </div>
            
            <div class="glass rounded-xl p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-crown text-white"></i>
                    </div>
                    <div class="ml-3">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Admin</dt>
                        <dd class="text-lg font-bold text-gray-900 dark:text-white">{{ $users->where('role', 'admin')->count() }}</dd>
                    </div>
                </div>
            </div>
            
            <div class="glass rounded-xl p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-tie text-white"></i>
                    </div>
                    <div class="ml-3">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Supervisor</dt>
                        <dd class="text-lg font-bold text-gray-900 dark:text-white">{{ $users->where('role', 'supervisor')->count() }}</dd>
                    </div>
                </div>
            </div>
            
            <div class="glass rounded-xl p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-cash-register text-white"></i>
                    </div>
                    <div class="ml-3">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kasir</dt>
                        <dd class="text-lg font-bold text-gray-900 dark:text-white">{{ $users->where('role', 'kasir')->count() }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="glass rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-white/20">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Daftar User</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/10">
                    <thead class="bg-white/5">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                User
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Role
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Dibuat
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($users as $user)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center">
                                                <span class="text-white font-medium">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $user->name }}
                                                @if($user->id === auth()->id())
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        (Anda)
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $user->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $user->role === 'admin' ? 'bg-red-100 text-red-800' : 
                                           ($user->role === 'supervisor' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800') }}">
                                        <i class="fas {{ $user->role === 'admin' ? 'fa-crown' : ($user->role === 'supervisor' ? 'fa-user-tie' : 'fa-cash-register') }} mr-1"></i>
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $user->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('users.show', $user) }}" 
                                           class="text-blue-600 hover:text-blue-900 p-1 rounded">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <a href="{{ route('users.edit', $user) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 p-1 rounded">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="text-yellow-600 hover:text-yellow-900 p-1 rounded"
                                                        title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                    <i class="fas {{ $user->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                                </button>
                                            </form>
                                            
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900 p-1 rounded">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-users text-4xl mb-4"></i>
                                    <p>Belum ada user.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-white/20">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>