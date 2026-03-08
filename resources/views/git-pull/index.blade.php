<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-code-branch mr-2"></i> Git Pull — Update Aplikasi
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3"></i>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-green-800">Berhasil!</p>
                            <pre class="mt-2 text-xs text-green-700 bg-green-100 rounded p-3 overflow-x-auto whitespace-pre-wrap">{{ session('success') }}</pre>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-times-circle text-red-500 mt-0.5 mr-3"></i>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-red-800">Terjadi Error</p>
                            <pre class="mt-2 text-xs text-red-700 bg-red-100 rounded p-3 overflow-x-auto whitespace-pre-wrap">{{ session('error') }}</pre>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Server Diagnostics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-stethoscope mr-2 text-gray-500"></i> Server Diagnostics
                    </h3>

                    <!-- Desktop Table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Info</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($diagnostics as $diag)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $diag['label'] }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($diag['status'])
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check mr-1"></i> OK
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-times mr-1"></i> FAIL
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <code class="text-xs bg-gray-100 px-2 py-1 rounded break-all">{{ $diag['value'] }}</code>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500 italic">{{ $diag['help'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="sm:hidden space-y-3">
                        @foreach($diagnostics as $diag)
                        <div class="border rounded-lg p-3 {{ $diag['status'] ? 'border-green-200 bg-green-50/30' : 'border-red-200 bg-red-50/30' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-900">{{ $diag['label'] }}</span>
                                @if($diag['status'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">OK</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">FAIL</span>
                                @endif
                            </div>
                            <code class="text-xs bg-white/70 px-2 py-1 rounded break-all block mb-1">{{ $diag['value'] }}</code>
                            <p class="text-xs text-gray-500 italic">{{ $diag['help'] }}</p>
                        </div>
                        @endforeach
                    </div>

                    @if(!$allOk && !$isHttp)
                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-yellow-800">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <strong>Warning:</strong> Beberapa diagnostik gagal. Git pull mungkin tidak berfungsi.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Git Settings (HTTP mode only) -->
            @if($isHttp)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        <i class="fas fa-cog mr-2 text-gray-500"></i> Git Settings
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Karena <code class="bg-gray-100 px-1 rounded">exec()</code> dinonaktifkan di server, update dilakukan via download HTTP dari API.
                    </p>

                    <form method="POST" action="{{ route('git-pull.settings') }}">
                        @csrf
                        <div class="space-y-4">
                            <!-- Provider -->
                            <div>
                                <label for="git_provider" class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
                                <select id="git_provider" name="git_provider"
                                        class="w-full sm:w-64 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="github" {{ (App\Models\Setting::get('git_provider', 'github') === 'github') ? 'selected' : '' }}>GitHub</option>
                                    <option value="gitlab" {{ (App\Models\Setting::get('git_provider', 'github') === 'gitlab') ? 'selected' : '' }}>GitLab</option>
                                </select>
                            </div>

                            <!-- Repository URL -->
                            <div>
                                <label for="git_repo_url" class="block text-sm font-medium text-gray-700 mb-1">Repository URL</label>
                                <input type="url" id="git_repo_url" name="git_repo_url"
                                       value="{{ App\Models\Setting::get('git_repo_url', '') }}"
                                       placeholder="https://github.com/username/repo-name"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <p class="mt-1 text-xs text-gray-500">URL lengkap ke repository Git Anda</p>
                            </div>

                            <!-- Access Token -->
                            <div>
                                <label for="git_access_token" class="block text-sm font-medium text-gray-700 mb-1">Personal Access Token</label>
                                <input type="password" id="git_access_token" name="git_access_token"
                                       placeholder="{{ App\Models\Setting::get('git_access_token') ? 'Token tersimpan (isi untuk mengganti)' : 'ghp_xxxx atau glpat-xxxx' }}"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <p class="mt-1 text-xs text-gray-500">
                                    GitHub: token dengan scope <code class="bg-gray-100 px-1 rounded">repo</code> —
                                    GitLab: token dengan scope <code class="bg-gray-100 px-1 rounded">read_api</code>
                                    @if(App\Models\Setting::get('git_access_token'))
                                        <br>Token saat ini: <code class="bg-gray-100 px-1 rounded">{{ str_repeat('*', 8) . substr(App\Models\Setting::get('git_access_token'), -4) }}</code>
                                    @endif
                                </p>
                            </div>

                            <div>
                                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
                                    <i class="fas fa-save mr-1"></i> Simpan Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Execute Pull -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-download mr-2 text-gray-500"></i>
                        {{ $isHttp ? 'Update Aplikasi' : 'Execute Git Pull' }}
                    </h3>

                    <div class="bg-gray-50 rounded-lg p-4 mb-4 space-y-2 text-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center">
                            <span class="font-medium text-gray-700 w-32 flex-shrink-0">Direktori:</span>
                            <code class="text-xs bg-white px-2 py-1 rounded border break-all">{{ base_path() }}</code>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center">
                            <span class="font-medium text-gray-700 w-32 flex-shrink-0">Branch:</span>
                            <code class="text-xs bg-white px-2 py-1 rounded border">main</code>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center">
                            <span class="font-medium text-gray-700 w-32 flex-shrink-0">Metode:</span>
                            <code class="text-xs bg-white px-2 py-1 rounded border">
                                {{ $isHttp ? 'Download ZIP via API' : 'git pull origin main' }}
                            </code>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('git-pull.pull') }}" id="pull-form">
                        @csrf
                        <div class="flex items-center space-x-3">
                            <button type="submit"
                                    {{ !$allOk ? 'disabled' : '' }}
                                    onclick="return confirm('Yakin ingin pull update terbaru? Pastikan sudah backup jika ada perubahan lokal.')"
                                    class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-bold py-2.5 px-5 rounded transition-colors text-sm"
                                    id="pull-button">
                                <i class="fas fa-cloud-download-alt mr-2"></i> Pull Update Terbaru
                            </button>

                            @if(!$allOk)
                                <span class="text-sm text-red-600">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $isHttp ? 'Konfigurasi settings terlebih dahulu.' : 'Diagnostik gagal.' }}
                                </span>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Post-Pull Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-tasks mr-2 text-gray-500"></i> Post-Update Actions
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">Jalankan setelah pull untuk menerapkan perubahan database dan konfigurasi.</p>

                    <div class="flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('git-pull.post-action') }}" class="inline">
                            @csrf
                            <input type="hidden" name="action" value="migrate">
                            <button type="submit"
                                    onclick="return confirm('Jalankan migration?')"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
                                <i class="fas fa-database mr-1"></i> Run Migration
                            </button>
                        </form>

                        <form method="POST" action="{{ route('git-pull.post-action') }}" class="inline">
                            @csrf
                            <input type="hidden" name="action" value="cache-clear">
                            <button type="submit"
                                    class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
                                <i class="fas fa-broom mr-1"></i> Clear Cache
                            </button>
                        </form>

                        <form method="POST" action="{{ route('git-pull.post-action') }}" class="inline">
                            @csrf
                            <input type="hidden" name="action" value="optimize">
                            <button type="submit"
                                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
                                <i class="fas fa-rocket mr-1"></i> Optimize
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.getElementById('pull-form')?.addEventListener('submit', function() {
            const btn = document.getElementById('pull-button');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Pulling...';
        });
    </script>
</x-app-layout>
