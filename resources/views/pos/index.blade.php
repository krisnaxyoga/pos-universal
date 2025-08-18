<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
            <i class="fas fa-cash-register mr-2"></i>
            POS - Point of Sale
        </h2>
    </x-slot>

    <!-- Include FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <div class="container mx-auto p-4">
        <!-- Mobile Tab Navigation -->
        <div class="md:hidden mb-4">
            <div class="flex bg-white rounded-lg shadow-md overflow-hidden">
                <button 
                    id="tab-products" 
                    onclick="switchTab('products')"
                    class="flex-1 py-3 px-4 text-center font-medium bg-blue-500 text-white"
                >
                    <i class="fas fa-box mr-2"></i>Produk
                </button>
                <button 
                    id="tab-cart" 
                    onclick="switchTab('cart')"
                    class="flex-1 py-3 px-4 text-center font-medium bg-gray-100 text-gray-700 relative"
                >
                    <i class="fas fa-shopping-cart mr-2"></i>Keranjang
                    <span id="mobile-cart-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Products Panel -->
            <div id="products-panel" class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <!-- Search Bar -->
                    <div class="mb-6">
                        <div class="relative">
                            <input 
                                type="text" 
                                id="search-input"
                                placeholder="Cari produk atau scan barcode..."
                                class="w-full pl-12 pr-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400 text-lg"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Product Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($products as $product)
                        <div 
                            class="product-card border border-gray-200 rounded-lg p-3 cursor-pointer hover:border-blue-500 hover:shadow-md transition-all {{ $product->stock <= 0 ? 'opacity-50' : '' }}"
                            data-id="{{ $product->id }}"
                            data-name="{{ $product->name }}"
                            data-price="{{ $product->price }}"
                            data-stock="{{ $product->stock }}"
                            data-image="{{ $product->image }}"
                            onclick="addProductToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }}, {{ $product->stock }}, '{{ $product->image }}')"
                        >
                            <div class="aspect-square mb-3">
                                @if($product->image)
                                    <img 
                                        src="{{ asset('storage/' . $product->image) }}" 
                                        alt="{{ $product->name }}"
                                        class="w-full h-full object-cover rounded-lg"
                                    >
                                @else
                                    <div class="w-full h-full bg-gray-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-box text-gray-400 text-2xl sm:text-3xl"></i>
                                    </div>
                                @endif
                            </div>
                            <h3 class="font-medium text-xs sm:text-sm text-gray-900 leading-tight mb-2 line-clamp-2">
                                {{ $product->name }}
                            </h3>
                            <p class="text-xs text-gray-500 mb-2">Stok: {{ $product->stock }}</p>
                            <p class="text-sm sm:text-lg font-bold text-blue-600">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Cart Panel -->
            <div id="cart-panel" class="hidden md:block">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                    <h2 class="text-xl font-semibold mb-4 flex items-center">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Keranjang
                        <span id="cart-count" class="ml-2 bg-red-500 text-white text-xs rounded-full px-2 py-1 hidden">0</span>
                    </h2>

                    <!-- Cart Items -->
                    <div id="cart-items" class="space-y-3 mb-6 max-h-80 overflow-y-auto">
                        <div id="empty-cart" class="text-center py-8 text-gray-500">
                            <i class="fas fa-shopping-cart text-4xl mb-4"></i>
                            <p>Keranjang kosong</p>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div id="cart-summary" class="hidden">
                        <div class="space-y-2 text-sm border-t pt-4 mb-4">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span id="subtotal-amount">Rp 0</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Pajak (10%):</span>
                                <span id="tax-amount">Rp 0</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t pt-2">
                                <span>Total:</span>
                                <span id="total-amount">Rp 0</span>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                                <select id="payment-method" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="cash">Tunai</option>
                                    <option value="card">Kartu Debit/Kredit</option>
                                    <option value="ewallet">E-Wallet</option>
                                    <option value="online">Transfer/Online</option>
                                </select>
                            </div>

                            <div id="cash-payment" class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Bayar</label>
                                    <input 
                                        type="number" 
                                        id="paid-amount"
                                        min="0" 
                                        step="1000"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Masukkan jumlah bayar"
                                    >
                                </div>
                                <div id="change-display" class="hidden p-2 bg-blue-50 rounded-lg">
                                    <div class="text-sm text-blue-600">
                                        <span class="font-medium">Kembalian: <span id="change-amount">Rp 0</span></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Info for Online Payment -->
                            <div id="customer-form" class="space-y-3 hidden">
                                <div class="text-sm font-medium text-gray-700 mb-2">Data Pelanggan</div>
                                <input type="text" id="customer-name" placeholder="Nama lengkap" class="w-full text-sm border border-gray-300 rounded-md px-3 py-2">
                                <input type="tel" id="customer-phone" placeholder="Nomor telepon" class="w-full text-sm border border-gray-300 rounded-md px-3 py-2">
                                <input type="email" id="customer-email" placeholder="Email" class="w-full text-sm border border-gray-300 rounded-md px-3 py-2">
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="mt-6 space-y-2">
                            <button 
                                id="process-transaction-btn"
                                onclick="processTransaction()"
                                class="w-full bg-blue-500 hover:bg-blue-700 disabled:bg-gray-300 text-white font-bold py-3 px-4 rounded transition-colors"
                                disabled
                            >
                                Proses Transaksi
                            </button>
                            
                            <button 
                                onclick="showDraftModal()"
                                class="w-full bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded transition-colors"
                            >
                                <i class="fas fa-save mr-2"></i>
                                Simpan Draft
                            </button>
                            
                            <button 
                                onclick="clearAllCart()"
                                class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors"
                            >
                                Bersihkan Keranjang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Cart Panel -->
        <div id="mobile-cart-panel" class="md:hidden hidden">
            <div class="bg-white rounded-lg shadow-md p-4">
                <h2 class="text-lg font-semibold mb-4 flex items-center">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Keranjang Belanja
                </h2>

                <!-- Mobile Cart Items -->
                <div id="mobile-cart-items" class="space-y-3 mb-4 max-h-60 overflow-y-auto">
                    <div id="mobile-empty-cart" class="text-center py-8 text-gray-500">
                        <i class="fas fa-shopping-cart text-3xl mb-2"></i>
                        <p>Keranjang kosong</p>
                    </div>
                </div>

                <!-- Mobile Cart Summary -->
                <div id="mobile-cart-summary" class="hidden">
                    <div class="space-y-2 text-sm border-t pt-4 mb-4">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span id="mobile-subtotal">Rp 0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pajak (10%):</span>
                            <span id="mobile-tax">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t pt-2">
                            <span>Total:</span>
                            <span id="mobile-total">Rp 0</span>
                        </div>
                    </div>

                    <!-- Mobile Payment Form -->
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                            <select id="mobile-payment-method" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="cash">Tunai</option>
                                <option value="card">Kartu Debit/Kredit</option>
                                <option value="ewallet">E-Wallet</option>
                                <option value="online">Transfer/Online</option>
                            </select>
                        </div>

                        <div id="mobile-cash-payment">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Bayar</label>
                            <input 
                                type="number" 
                                id="mobile-paid-amount"
                                min="0" 
                                step="1000"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Masukkan jumlah bayar"
                            >
                            <div id="mobile-change-display" class="hidden mt-2 p-2 bg-blue-50 rounded-lg">
                                <div class="text-sm text-blue-600">
                                    <span class="font-medium">Kembalian: <span id="mobile-change-amount">Rp 0</span></span>
                                </div>
                            </div>
                        </div>

                        <div id="mobile-customer-form" class="space-y-2 hidden">
                            <div class="text-sm font-medium text-gray-700">Data Pelanggan</div>
                            <input type="text" id="mobile-customer-name" placeholder="Nama lengkap" class="w-full text-sm border border-gray-300 rounded-md px-3 py-2">
                            <input type="tel" id="mobile-customer-phone" placeholder="Nomor telepon" class="w-full text-sm border border-gray-300 rounded-md px-3 py-2">
                            <input type="email" id="mobile-customer-email" placeholder="Email" class="w-full text-sm border border-gray-300 rounded-md px-3 py-2">
                        </div>
                    </div>
                    
                    <!-- Mobile Action Buttons -->
                    <div class="mt-4 space-y-2">
                        <button 
                            id="mobile-process-btn"
                            onclick="processTransaction()"
                            class="w-full bg-blue-500 hover:bg-blue-700 disabled:bg-gray-300 text-white font-bold py-3 px-4 rounded transition-colors"
                            disabled
                        >
                            Proses Transaksi
                        </button>
                        
                        <div class="grid grid-cols-2 gap-2">
                            <button 
                                onclick="showDraftModal()"
                                class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded transition-colors"
                            >
                                <i class="fas fa-save mr-1"></i>
                                Draft
                            </button>
                            
                            <button 
                                onclick="clearAllCart()"
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors"
                            >
                                <i class="fas fa-trash mr-1"></i>
                                Bersih
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Draft List Panel -->
        @if(count($drafts) > 0)
        <div class="mt-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">
                    <i class="fas fa-save mr-2"></i>
                    Draft Tersimpan ({{ count($drafts) }})
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($drafts as $draft)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:bg-blue-50 transition-colors">
                        <div class="flex justify-between items-start">
                            <div class="flex-1 cursor-pointer" onclick="loadDraftToCart({{ $draft->id }})">
                                <h5 class="font-medium text-sm text-gray-800 mb-2">{{ $draft->draft_name }}</h5>
                                <div class="space-y-1 text-xs text-gray-500">
                                    <p><i class="fas fa-shopping-cart mr-1"></i> {{ $draft->items->count() }} items</p>
                                    <p><i class="fas fa-money-bill mr-1"></i> Rp {{ number_format($draft->total, 0, ',', '.') }}</p>
                                    <p><i class="fas fa-clock mr-1"></i> {{ $draft->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                            <button 
                                onclick="deleteDraftById({{ $draft->id }})"
                                class="text-red-400 hover:text-red-600 ml-2 p-1 hover:bg-red-50 rounded transition-colors"
                                title="Hapus draft"
                            >
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Save Draft Modal -->
    <div id="draft-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Simpan Draft Transaksi</h3>
                <button onclick="hideDraftModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Draft</label>
                    <input 
                        type="text" 
                        id="draft-name-input"
                        placeholder="Masukkan nama untuk draft ini..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                    >
                </div>

                <div class="bg-gray-50 p-3 rounded-lg">
                    <div class="text-sm text-gray-600">
                        <p><strong>Items:</strong> <span id="draft-item-count">0</span> produk</p>
                        <p><strong>Total:</strong> <span id="draft-total-amount">Rp 0</span></p>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button 
                        onclick="hideDraftModal()"
                        class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition-colors"
                    >
                        Batal
                    </button>
                    <button 
                        onclick="saveDraftTransaction()"
                        class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition-colors"
                    >
                        <i class="fas fa-save mr-2"></i>
                        Simpan Draft
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let cartItems = [];
        let activeTab = 'products';
        let products = @json($products);
        let csrfToken = '{{ csrf_token() }}';
        let routes = {
            posTransaction: '{{ route("pos.transaction") }}',
            draftSave: '{{ route("pos.draft.save") }}',
            draftLoad: '{{ route("pos.draft.load", ":id") }}'.replace(':id', ''),
            draftDelete: '{{ url("pos/draft") }}'
        };

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            setDefaultPaymentMethod();
            updateCartDisplay();
            
            // Load retry transaction if available
            @if($retryTransaction)
                loadTransactionData(@json($retryTransaction));
            @endif
            
            // Load draft transaction if available
            @if($draftTransaction)
                loadTransactionData(@json($draftTransaction));
            @endif
        });

        function setDefaultPaymentMethod() {
            // Set default payment method to cash
            const desktopPayment = document.getElementById('payment-method');
            const mobilePayment = document.getElementById('mobile-payment-method');
            
            if (desktopPayment) {
                desktopPayment.value = 'cash';
            }
            if (mobilePayment) {
                mobilePayment.value = 'cash';
            }
        }

        function initializeEventListeners() {
            // Search functionality
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    filterProducts(this.value.toLowerCase());
                });
            }

            // Payment method changes
            setupPaymentMethodListeners();
            
            // Paid amount changes
            setupPaidAmountListeners();
            
            // Customer form changes
            setupCustomerFormListeners();
        }

        function setupPaymentMethodListeners() {
            const desktopPayment = document.getElementById('payment-method');
            const mobilePayment = document.getElementById('mobile-payment-method');
            
            [desktopPayment, mobilePayment].forEach(element => {
                if (element) {
                    element.addEventListener('change', function() {
                        handlePaymentMethodChange(this.value, element.id.includes('mobile'));
                    });
                }
            });
        }

        function setupPaidAmountListeners() {
            const desktopPaid = document.getElementById('paid-amount');
            const mobilePaid = document.getElementById('mobile-paid-amount');
            
            [desktopPaid, mobilePaid].forEach(element => {
                if (element) {
                    element.addEventListener('input', function() {
                        updateChangeCalculation();
                        updateProcessButtons();
                    });
                }
            });
        }

        function setupCustomerFormListeners() {
            // Desktop customer form fields
            const desktopFields = [
                'customer-name',
                'customer-phone', 
                'customer-email'
            ];
            
            // Mobile customer form fields
            const mobileFields = [
                'mobile-customer-name',
                'mobile-customer-phone',
                'mobile-customer-email'
            ];
            
            // Add listeners to all customer form fields
            [...desktopFields, ...mobileFields].forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('input', function() {
                        updateProcessButtons();
                    });
                }
            });
        }

        function handlePaymentMethodChange(paymentMethod, isMobile) {
            const prefix = isMobile ? 'mobile-' : '';
            const cashSection = document.getElementById(`${prefix}cash-payment`);
            const customerSection = document.getElementById(`${prefix}customer-form`);
            const paidInput = document.getElementById(`${prefix}paid-amount`);
            
            if (cashSection && customerSection) {
                if (paymentMethod === 'online') {
                    cashSection.classList.add('hidden');
                    customerSection.classList.remove('hidden');
                } else {
                    cashSection.classList.remove('hidden');
                    customerSection.classList.add('hidden');
                    
                    if (paidInput) {
                        if (paymentMethod === 'cash') {
                            paidInput.readOnly = false;
                            paidInput.classList.remove('bg-gray-100');
                        } else {
                            paidInput.readOnly = true;
                            paidInput.classList.add('bg-gray-100');
                            paidInput.value = calculateTotal();
                        }
                    }
                }
            }
            updateProcessButtons();
            
            // Also trigger validation when customer form becomes visible
            if (paymentMethod === 'online') {
                // Trigger validation update after form is shown
                setTimeout(() => {
                    updateProcessButtons();
                }, 100);
            }
        }

        function switchTab(tab) {
            activeTab = tab;
            const productsPanel = document.getElementById('products-panel');
            const cartPanel = document.getElementById('mobile-cart-panel');
            const tabProducts = document.getElementById('tab-products');
            const tabCart = document.getElementById('tab-cart');
            
            if (tab === 'products') {
                productsPanel.classList.remove('hidden');
                cartPanel.classList.add('hidden');
                tabProducts.className = 'flex-1 py-3 px-4 text-center font-medium bg-blue-500 text-white';
                tabCart.className = 'flex-1 py-3 px-4 text-center font-medium bg-gray-100 text-gray-700 relative';
            } else {
                productsPanel.classList.add('hidden');
                cartPanel.classList.remove('hidden');
                tabProducts.className = 'flex-1 py-3 px-4 text-center font-medium bg-gray-100 text-gray-700';
                tabCart.className = 'flex-1 py-3 px-4 text-center font-medium bg-blue-500 text-white relative';
            }
        }

        function filterProducts(query) {
            const productCards = document.querySelectorAll('.product-card');
            productCards.forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const visible = name.includes(query);
                card.style.display = visible ? 'block' : 'none';
            });
        }

        function addProductToCart(id, name, price, stock, image) {
            if (stock <= 0) {
                alert('Stok produk habis');
                return;
            }
            
            const existingItem = cartItems.find(item => item.id === id);
            
            if (existingItem) {
                if (existingItem.quantity < stock) {
                    existingItem.quantity++;
                } else {
                    alert('Jumlah melebihi stok yang tersedia');
                    return;
                }
            } else {
                cartItems.push({
                    id: id,
                    name: name,
                    price: price,
                    quantity: 1,
                    stock: stock,
                    image: image
                });
            }
            
            updateCartDisplay();
            
            // Switch to cart tab on mobile after adding item
            if (window.innerWidth < 768) {
                switchTab('cart');
            }
        }

        function increaseCartQuantity(index) {
            const item = cartItems[index];
            if (item.quantity < item.stock) {
                item.quantity++;
                updateCartDisplay();
            } else {
                alert('Jumlah melebihi stok yang tersedia');
            }
        }

        function decreaseCartQuantity(index) {
            const item = cartItems[index];
            if (item.quantity > 1) {
                item.quantity--;
            } else {
                cartItems.splice(index, 1);
            }
            updateCartDisplay();
        }

        function removeCartItem(index) {
            cartItems.splice(index, 1);
            updateCartDisplay();
        }

        function clearAllCart() {
            if (cartItems.length > 0 && confirm('Apakah Anda yakin ingin menghapus semua item dari keranjang?')) {
                cartItems = [];
                
                // Reset payment fields
                const paidInputs = ['paid-amount', 'mobile-paid-amount'];
                paidInputs.forEach(id => {
                    const input = document.getElementById(id);
                    if (input) {
                        input.value = '';
                    }
                });
                
                // Reset customer info
                const customerFields = ['customer-name', 'customer-phone', 'customer-email', 
                                      'mobile-customer-name', 'mobile-customer-phone', 'mobile-customer-email'];
                customerFields.forEach(id => {
                    const field = document.getElementById(id);
                    if (field) {
                        field.value = '';
                    }
                });
                
                updateCartDisplay();
            }
        }

        function updateCartDisplay() {
            updateDesktopCart();
            updateMobileCart();
            updateCartBadges();
            updateChangeCalculation();
            updateProcessButtons();
        }

        function updateDesktopCart() {
            const cartContainer = document.getElementById('cart-items');
            const cartSummary = document.getElementById('cart-summary');
            
            if (!cartContainer) return;
            
            // Always clear the container first
            cartContainer.innerHTML = '';
            
            if (cartItems.length === 0) {
                if (cartSummary) cartSummary.classList.add('hidden');
                cartContainer.innerHTML = '<div id="empty-cart" class="text-center py-8 text-gray-500"><i class="fas fa-shopping-cart text-4xl mb-4"></i><p>Keranjang kosong</p></div>';
                return;
            }
            
            if (cartSummary) cartSummary.classList.remove('hidden');
            
            let cartHTML = '';
            cartItems.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                cartHTML += `
                    <div class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg">
                        ${item.image ? 
                            `<img src="/storage/${item.image}" alt="${item.name}" class="w-12 h-12 object-cover rounded-lg">` :
                            `<div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center"><i class="fas fa-box text-gray-400"></i></div>`
                        }
                        <div class="flex-1">
                            <h4 class="font-medium text-sm">${item.name}</h4>
                            <p class="text-xs text-gray-500">Rp ${formatPrice(item.price)} x ${item.quantity}</p>
                            <p class="text-sm font-medium text-blue-600">Rp ${formatPrice(itemTotal)}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="decreaseCartQuantity(${index})" class="w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="w-8 text-center font-medium text-sm">${item.quantity}</span>
                            <button onclick="increaseCartQuantity(${index})" class="w-6 h-6 bg-green-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-green-600">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            
            cartContainer.innerHTML = cartHTML;
            updateCartTotals();
        }

        function updateMobileCart() {
            const mobileContainer = document.getElementById('mobile-cart-items');
            const mobileSummary = document.getElementById('mobile-cart-summary');
            
            if (!mobileContainer) return;
            
            // Always clear the container first
            mobileContainer.innerHTML = '';
            
            if (cartItems.length === 0) {
                if (mobileSummary) mobileSummary.classList.add('hidden');
                mobileContainer.innerHTML = '<div id="mobile-empty-cart" class="text-center py-8 text-gray-500"><i class="fas fa-shopping-cart text-3xl mb-2"></i><p>Keranjang kosong</p></div>';
                return;
            }
            
            if (mobileSummary) mobileSummary.classList.remove('hidden');
            
            let mobileHTML = '';
            cartItems.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                mobileHTML += `
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                        ${item.image ? 
                            `<img src="/storage/${item.image}" alt="${item.name}" class="w-10 h-10 object-cover rounded-lg">` :
                            `<div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center"><i class="fas fa-box text-gray-400 text-sm"></i></div>`
                        }
                        <div class="flex-1">
                            <h4 class="font-medium text-sm">${item.name}</h4>
                            <p class="text-xs text-gray-500">Rp ${formatPrice(item.price)} x ${item.quantity}</p>
                            <p class="text-sm font-medium text-blue-600">Rp ${formatPrice(itemTotal)}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="decreaseCartQuantity(${index})" class="w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="w-6 text-center font-medium text-sm">${item.quantity}</span>
                            <button onclick="increaseCartQuantity(${index})" class="w-6 h-6 bg-green-500 text-white rounded-full flex items-center justify-center text-xs">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            
            mobileContainer.innerHTML = mobileHTML;
            updateMobileCartTotals();
        }

        function updateCartBadges() {
            const desktopBadge = document.getElementById('cart-count');
            const mobileBadge = document.getElementById('mobile-cart-badge');
            
            [desktopBadge, mobileBadge].forEach(badge => {
                if (badge) {
                    if (cartItems.length > 0) {
                        badge.textContent = cartItems.length;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
            });
        }

        function updateCartTotals() {
            const subtotal = calculateSubtotal();
            const tax = calculateTax(subtotal);
            const total = subtotal + tax;
            
            const subtotalEl = document.getElementById('subtotal-amount');
            const taxEl = document.getElementById('tax-amount');
            const totalEl = document.getElementById('total-amount');
            
            if (subtotalEl) subtotalEl.textContent = 'Rp ' + formatPrice(subtotal);
            if (taxEl) taxEl.textContent = 'Rp ' + formatPrice(tax);
            if (totalEl) totalEl.textContent = 'Rp ' + formatPrice(total);
        }

        function updateMobileCartTotals() {
            const subtotal = calculateSubtotal();
            const tax = calculateTax(subtotal);
            const total = subtotal + tax;
            
            const mobileSubtotal = document.getElementById('mobile-subtotal');
            const mobileTax = document.getElementById('mobile-tax');
            const mobileTotal = document.getElementById('mobile-total');
            
            if (mobileSubtotal) mobileSubtotal.textContent = 'Rp ' + formatPrice(subtotal);
            if (mobileTax) mobileTax.textContent = 'Rp ' + formatPrice(tax);
            if (mobileTotal) mobileTotal.textContent = 'Rp ' + formatPrice(total);
        }

        function updateChangeCalculation() {
            const total = calculateTotal();
            
            // Set default paid amount to total if empty and payment method is cash
            const desktopPaymentMethod = document.getElementById('payment-method')?.value || 'cash';
            const mobilePaymentMethod = document.getElementById('mobile-payment-method')?.value || 'cash';
            
            const desktopPaidInput = document.getElementById('paid-amount');
            const mobilePaidInput = document.getElementById('mobile-paid-amount');
            
            // Auto-fill paid amount for non-cash payments
            if (desktopPaidInput && desktopPaymentMethod !== 'cash') {
                desktopPaidInput.value = total;
            }
            if (mobilePaidInput && mobilePaymentMethod !== 'cash') {
                mobilePaidInput.value = total;
            }
            
            // Desktop change calculation
            const paidAmount = parseFloat(desktopPaidInput?.value) || 0;
            const changeDisplay = document.getElementById('change-display');
            const changeAmount = document.getElementById('change-amount');
            
            if (changeDisplay && changeAmount) {
                if (paidAmount >= total && paidAmount > 0 && desktopPaymentMethod === 'cash') {
                    changeAmount.textContent = 'Rp ' + formatPrice(paidAmount - total);
                    changeDisplay.classList.remove('hidden');
                } else {
                    changeDisplay.classList.add('hidden');
                }
            }
            
            // Mobile change calculation
            const mobilePaidAmount = parseFloat(mobilePaidInput?.value) || 0;
            const mobileChangeDisplay = document.getElementById('mobile-change-display');
            const mobileChangeAmount = document.getElementById('mobile-change-amount');
            
            if (mobileChangeDisplay && mobileChangeAmount) {
                if (mobilePaidAmount >= total && mobilePaidAmount > 0 && mobilePaymentMethod === 'cash') {
                    mobileChangeAmount.textContent = 'Rp ' + formatPrice(mobilePaidAmount - total);
                    mobileChangeDisplay.classList.remove('hidden');
                } else {
                    mobileChangeDisplay.classList.add('hidden');
                }
            }
        }

        function updateProcessButtons() {
            const canProcess = validateCurrentTransaction();
            
            const desktopBtn = document.getElementById('process-transaction-btn');
            const mobileBtn = document.getElementById('mobile-process-btn');
            
            [desktopBtn, mobileBtn].forEach(btn => {
                if (btn) {
                    btn.disabled = !canProcess;
                    btn.textContent = canProcess ? 'Proses Transaksi' : 'Lengkapi Pembayaran';
                }
            });
        }

        function validateCurrentTransaction() {
            if (cartItems.length === 0) return false;
            
            const isMobile = window.innerWidth < 768;
            const paymentMethod = document.getElementById(isMobile ? 'mobile-payment-method' : 'payment-method')?.value || 'cash';
            
            if (paymentMethod === 'online') {
                const prefix = isMobile ? 'mobile-' : '';
                const name = document.getElementById(`${prefix}customer-name`)?.value || '';
                const phone = document.getElementById(`${prefix}customer-phone`)?.value || '';
                const email = document.getElementById(`${prefix}customer-email`)?.value || '';
                return name.trim() && phone.trim() && email.trim();
            }
            
            if (paymentMethod === 'cash') {
                const prefix = isMobile ? 'mobile-' : '';
                const paidAmountInput = document.getElementById(`${prefix}paid-amount`);
                const paidAmount = parseFloat(paidAmountInput?.value) || 0;
                const total = calculateTotal();
                
                // Enable button if paid amount is entered and >= total
                return paidAmount > 0 && paidAmount >= total;
            }
            
            // For card and ewallet, just need items in cart
            return cartItems.length > 0;
        }

        function calculateSubtotal() {
            return cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        }

        function calculateTax(subtotal) {
            return subtotal * 0.1; // 10% tax
        }

        function calculateTotal() {
            const subtotal = calculateSubtotal();
            return subtotal + calculateTax(subtotal);
        }

        function formatPrice(price) {
            return new Intl.NumberFormat('id-ID').format(Math.round(price));
        }

        async function processTransaction() {
            if (!validateCurrentTransaction()) {
                alert('Lengkapi data pembayaran terlebih dahulu');
                return;
            }
            
            const isMobile = window.innerWidth < 768;
            const prefix = isMobile ? 'mobile-' : '';
            
            const paymentMethod = document.getElementById(`${prefix}payment-method`)?.value || 'cash';
            const paidAmount = parseFloat(document.getElementById(`${prefix}paid-amount`)?.value) || calculateTotal();
            
            const transactionData = {
                items: cartItems.map(item => ({
                    product_id: item.id,
                    quantity: item.quantity
                })),
                subtotal: calculateSubtotal(),
                discount: 0,
                tax: calculateTax(calculateSubtotal()),
                total: calculateTotal(),
                paid: paidAmount,
                payment_method: paymentMethod
            };
            
            if (paymentMethod === 'online') {
                transactionData.customer_info = {
                    name: document.getElementById(`${prefix}customer-name`)?.value,
                    phone: document.getElementById(`${prefix}customer-phone`)?.value,
                    email: document.getElementById(`${prefix}customer-email`)?.value
                };
            }
            
            try {
                const response = await fetch(routes.posTransaction, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(transactionData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Clear cart without confirmation since transaction is successful
                    cartItems = [];
                    updateCartDisplay();
                    
                    if (result.redirect && result.payment_url) {
                        // For online payments, redirect to payment page
                        alert('Transaksi online berhasil dibuat! Anda akan diarahkan ke halaman pembayaran.');
                        window.location.href = result.payment_url;
                    } else {
                        // For other payment methods, go to receipt
                        alert('Transaksi berhasil diproses!');
                        if (result.transaction) {
                            window.location.href = `/pos/receipt/${result.transaction.id}`;
                        }
                    }
                } else {
                    alert(result.message || 'Terjadi kesalahan saat memproses transaksi');
                }
            } catch (error) {
                console.error('Transaction error:', error);
                alert('Terjadi kesalahan jaringan. Silakan coba lagi.');
            }
        }

        function showDraftModal() {
            if (cartItems.length === 0) {
                alert('Keranjang kosong. Tambahkan produk terlebih dahulu.');
                return;
            }
            
            document.getElementById('draft-item-count').textContent = cartItems.length;
            document.getElementById('draft-total-amount').textContent = 'Rp ' + formatPrice(calculateTotal());
            const modal = document.getElementById('draft-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('draft-name-input').focus();
        }

        function hideDraftModal() {
            const modal = document.getElementById('draft-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('draft-name-input').value = '';
        }

        async function saveDraftTransaction() {
            const draftName = document.getElementById('draft-name-input').value.trim();
            
            if (!draftName) {
                alert('Masukkan nama draft');
                return;
            }
            
            if (cartItems.length === 0) {
                alert('Keranjang kosong');
                return;
            }
            
            const draftData = {
                items: cartItems.map(item => ({
                    product_id: item.id,
                    quantity: item.quantity
                })),
                subtotal: calculateSubtotal(),
                discount: 0,
                tax: calculateTax(calculateSubtotal()),
                total: calculateTotal(),
                draft_name: draftName
            };
            
            try {
                const response = await fetch(routes.draftSave, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(draftData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Draft berhasil disimpan!');
                    
                    // Clear cart without confirmation since draft is saved
                    cartItems = [];
                    updateCartDisplay();
                    hideDraftModal();
                    location.reload(); // Reload to show new draft
                } else {
                    alert(result.message || 'Terjadi kesalahan saat menyimpan draft');
                }
            } catch (error) {
                console.error('Save draft error:', error);
                alert('Terjadi kesalahan jaringan. Silakan coba lagi.');
            }
        }

        async function loadDraftToCart(draftId) {
            try {
                const response = await fetch(`${routes.draftLoad}${draftId}`);
                const result = await response.json();
                
                if (result.success && result.draft) {
                    loadTransactionData(result.draft);
                    alert('Draft berhasil dimuat!');
                } else {
                    alert('Gagal memuat draft');
                }
            } catch (error) {
                console.error('Load draft error:', error);
                alert('Terjadi kesalahan saat memuat draft');
            }
        }

        async function deleteDraftById(draftId) {
            if (!confirm('Apakah Anda yakin ingin menghapus draft ini?')) return;
            
            try {
                const response = await fetch(`${routes.draftDelete}/${draftId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Draft berhasil dihapus!');
                    location.reload(); // Reload to update draft list
                } else {
                    alert(result.message || 'Gagal menghapus draft');
                }
            } catch (error) {
                console.error('Delete draft error:', error);
                alert('Terjadi kesalahan saat menghapus draft');
            }
        }

        function loadTransactionData(transaction) {
            cartItems = [];
            
            if (transaction.items) {
                transaction.items.forEach(item => {
                    const product = item.product || item;
                    cartItems.push({
                        id: product.id,
                        name: product.name || item.product_name,
                        price: product.price || item.product_price,
                        quantity: item.quantity,
                        stock: product.stock || 999,
                        image: product.image
                    });
                });
            }
            
            updateCartDisplay();
        }
    </script>

    @vite(['resources/js/app.js'])
</x-app-layout>