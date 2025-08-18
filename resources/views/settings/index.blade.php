<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pengaturan Aplikasi') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- App Configuration Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">Konfigurasi Aplikasi</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- App Name -->
                            <div class="md:col-span-2">
                                <x-input-label for="app_name" :value="__('Nama Aplikasi')" />
                                <x-text-input id="app_name" class="block mt-1 w-full" type="text" name="app_name" 
                                            :value="old('app_name', $settings->has('app_name') ? $settings->get('app_name')->value : config('app.name'))" required />
                                <x-input-error :messages="$errors->get('app_name')" class="mt-2" />
                                <p class="mt-1 text-sm text-gray-500">Nama yang akan ditampilkan di header dan login page</p>
                            </div>

                            <!-- App Logo -->
                            <div class="md:col-span-2">
                                <x-input-label for="app_logo" :value="__('Logo Aplikasi')" />
                                
                                @php $currentLogo = $settings->has('app_logo') ? $settings->get('app_logo')->value : null; @endphp
                                @if($currentLogo && Storage::disk('public')->exists($currentLogo))
                                    <div class="mt-2 mb-4">
                                        <div class="flex items-center space-x-4">
                                            <img src="{{ Storage::url($currentLogo) }}" alt="Current Logo" class="h-16 w-16 object-contain border rounded">
                                            <div>
                                                <p class="text-sm text-gray-600">Logo saat ini</p>
                                                <button type="button" onclick="removeLogo()" 
                                                        class="text-sm text-red-600 hover:text-red-800">
                                                    Hapus Logo
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <input type="file" id="app_logo" name="app_logo" accept="image/*"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <x-input-error :messages="$errors->get('app_logo')" class="mt-2" />
                                <p class="mt-1 text-sm text-gray-500">Format: JPEG, PNG, JPG, GIF, SVG. Maksimal 2MB</p>
                                
                                <!-- Logo Preview -->
                                <div id="logo-preview" class="mt-4 hidden">
                                    <img id="preview-image" src="" alt="Preview" class="h-16 w-16 object-contain border rounded">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Company Information Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">Informasi Perusahaan</h3>
                        <p class="text-sm text-gray-600 mb-4">Informasi ini akan ditampilkan pada struk pembayaran</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Company Name -->
                            <div class="md:col-span-2">
                                <x-input-label for="company_name" :value="__('Nama Perusahaan')" />
                                <x-text-input id="company_name" class="block mt-1 w-full" type="text" name="company_name" 
                                            :value="old('company_name', $settings->has('company_name') ? $settings->get('company_name')->value : 'Your Company Name')" required />
                                <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                            </div>

                            <!-- Company Phone -->
                            <div>
                                <x-input-label for="company_phone" :value="__('Nomor Telepon')" />
                                <x-text-input id="company_phone" class="block mt-1 w-full" type="text" name="company_phone" 
                                            :value="old('company_phone', $settings->has('company_phone') ? $settings->get('company_phone')->value : 'Your Phone Number')" required />
                                <x-input-error :messages="$errors->get('company_phone')" class="mt-2" />
                            </div>

                            <!-- Receipt Footer -->
                            <div>
                                <x-input-label for="receipt_footer" :value="__('Footer Struk')" />
                                <x-text-input id="receipt_footer" class="block mt-1 w-full" type="text" name="receipt_footer" 
                                            :value="old('receipt_footer', $settings->has('receipt_footer') ? $settings->get('receipt_footer')->value : 'Terima kasih atas kunjungan Anda!')" />
                                <x-input-error :messages="$errors->get('receipt_footer')" class="mt-2" />
                            </div>

                            <!-- Company Address -->
                            <div class="md:col-span-2">
                                <x-input-label for="company_address" :value="__('Alamat Perusahaan')" />
                                <textarea id="company_address" name="company_address" rows="3" 
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('company_address', $settings->has('company_address') ? $settings->get('company_address')->value : 'Your Company Address') }}</textarea>
                                <x-input-error :messages="$errors->get('company_address')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- iPaymu Configuration Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">Konfigurasi iPaymu</h3>
                        <p class="text-sm text-gray-600 mb-4">Pengaturan untuk payment gateway iPaymu</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- iPaymu VA -->
                            <div>
                                <x-input-label for="ipaymu_va" :value="__('Virtual Account (VA)')" />
                                <x-text-input id="ipaymu_va" class="block mt-1 w-full" type="text" name="ipaymu_va" 
                                            :value="old('ipaymu_va', $settings->has('ipaymu_va') ? $settings->get('ipaymu_va')->value : '')" />
                                <x-input-error :messages="$errors->get('ipaymu_va')" class="mt-2" />
                            </div>

                            <!-- iPaymu API Key -->
                            <div>
                                <x-input-label for="ipaymu_api_key" :value="__('API Key')" />
                                <x-text-input id="ipaymu_api_key" class="block mt-1 w-full" type="password" name="ipaymu_api_key" 
                                            :value="old('ipaymu_api_key', $settings->has('ipaymu_api_key') ? $settings->get('ipaymu_api_key')->value : '')" />
                                <x-input-error :messages="$errors->get('ipaymu_api_key')" class="mt-2" />
                            </div>

                            <!-- Environment -->
                            <div>
                                <x-input-label for="ipaymu_environment" :value="__('Environment')" />
                                <select id="ipaymu_environment" name="ipaymu_environment" 
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="sandbox" {{ old('ipaymu_environment', $settings->has('ipaymu_environment') ? $settings->get('ipaymu_environment')->value : 'sandbox') == 'sandbox' ? 'selected' : '' }}>
                                        Sandbox (Testing)
                                    </option>
                                    <option value="production" {{ old('ipaymu_environment', $settings->has('ipaymu_environment') ? $settings->get('ipaymu_environment')->value : 'sandbox') == 'production' ? 'selected' : '' }}>
                                        Production (Live)
                                    </option>
                                </select>
                                <x-input-error :messages="$errors->get('ipaymu_environment')" class="mt-2" />
                            </div>

                            <!-- Test Connection Button -->
                            <div class="flex items-end">
                                <button type="button" onclick="testIpaymuConnection()" 
                                        class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                    Test Koneksi
                                </button>
                            </div>

                            <!-- Test Result -->
                            <div id="test-result" class="md:col-span-2 hidden">
                                <div id="test-message" class="p-4 rounded-md"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-end">
                            <x-primary-button>
                                {{ __('Simpan Pengaturan') }}
                            </x-primary-button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Logo preview
        document.getElementById('app_logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-image').src = e.target.result;
                    document.getElementById('logo-preview').classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                document.getElementById('logo-preview').classList.add('hidden');
            }
        });

        // Remove logo function
        function removeLogo() {
            if (confirm('Yakin ingin menghapus logo?')) {
                fetch('{{ route("settings.remove-logo") }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal menghapus logo: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error occurred while removing logo');
                });
            }
        }

        // Test iPaymu connection
        function testIpaymuConnection() {
            const va = document.getElementById('ipaymu_va').value;
            const apiKey = document.getElementById('ipaymu_api_key').value;
            const environment = document.getElementById('ipaymu_environment').value;
            
            if (!va || !apiKey) {
                alert('VA dan API Key wajib diisi untuk test koneksi');
                return;
            }

            const resultDiv = document.getElementById('test-result');
            const messageDiv = document.getElementById('test-message');
            
            // Show loading
            resultDiv.classList.remove('hidden');
            messageDiv.className = 'p-4 rounded-md bg-blue-50 text-blue-800';
            messageDiv.textContent = 'Testing koneksi iPaymu...';

            fetch('{{ route("settings.test-ipaymu") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ipaymu_va: va,
                    ipaymu_api_key: apiKey,
                    ipaymu_environment: environment
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.className = 'p-4 rounded-md bg-green-50 text-green-800';
                    messageDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + data.message;
                } else {
                    messageDiv.className = 'p-4 rounded-md bg-red-50 text-red-800';
                    messageDiv.innerHTML = '<i class="fas fa-times-circle mr-2"></i>' + data.message;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.className = 'p-4 rounded-md bg-red-50 text-red-800';
                messageDiv.innerHTML = '<i class="fas fa-times-circle mr-2"></i>Error occurred while testing connection';
            });
        }

        // Show/hide API key
        function toggleApiKey() {
            const apiKeyInput = document.getElementById('ipaymu_api_key');
            if (apiKeyInput.type === 'password') {
                apiKeyInput.type = 'text';
            } else {
                apiKeyInput.type = 'password';
            }
        }
    </script>
</x-app-layout>