<template>
  <div id="pos-app">
    <!-- Mobile Layout -->
    <div class="md:hidden">
      <!-- Mobile Header with Tabs -->
      <div class="bg-white rounded-lg shadow-sm mb-4 overflow-hidden">
        <div class="flex">
          <button 
            @click="activeTab = 'products'"
            :class="activeTab === 'products' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700'"
            class="flex-1 py-3 px-4 text-center font-medium"
          >
            <i class="fas fa-box mr-2"></i>
            Produk
          </button>
          <button 
            @click="activeTab = 'cart'"
            :class="activeTab === 'cart' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700'"
            class="flex-1 py-3 px-4 text-center font-medium relative"
          >
            <i class="fas fa-shopping-cart mr-2"></i>
            Keranjang
            <span v-show="cartItems.length > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
              {{ cartItems.length }}
            </span>
          </button>
          <button 
            @click="showMobileDrafts = true"
            :class="drafts.length > 0 ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700'"
            class="flex-1 py-3 px-4 text-center font-medium relative"
          >
            <i class="fas fa-save mr-2"></i>
            Draft
            <span v-show="drafts.length > 0" class="absolute -top-1 -right-1 bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
              {{ drafts.length }}
            </span>
          </button>
        </div>
      </div>

      <!-- Mobile Products Tab -->
      <div v-show="activeTab === 'products'" class="space-y-4">
        <!-- Mobile Search -->
        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="relative">
            <input 
              type="text" 
              placeholder="Cari produk atau scan barcode..."
              class="w-full pl-12 pr-12 py-4 text-lg border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              v-model="searchQuery"
              @input="filterProducts"
            >
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <i class="fas fa-search text-gray-400 text-lg"></i>
            </div>
          </div>
        </div>

        <!-- Mobile Product Grid -->
        <div class="bg-white rounded-lg shadow-sm p-4">
          <div class="grid grid-cols-2 gap-3">
            <div 
              v-for="product in filteredProducts" 
              :key="product.id"
              @click="addToCart(product)"
              class="border border-gray-200 rounded-xl p-3 cursor-pointer hover:border-blue-500 hover:shadow-md transition-all active:scale-95"
              :class="{ 'opacity-50': product.stock <= 0 }"
            >
              <div class="aspect-square mb-2">
                <img 
                  v-if="product.image" 
                  :src="'/storage/' + product.image" 
                  :alt="product.name"
                  class="w-full h-full object-cover rounded-lg"
                >
                <div v-else class="w-full h-full bg-gray-100 rounded-lg flex items-center justify-center">
                  <i class="fas fa-box text-gray-400 text-2xl"></i>
                </div>
              </div>
              <h3 class="font-medium text-sm text-gray-900 leading-tight mb-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                {{ product.name }}
              </h3>
              <p class="text-xs text-gray-500 mb-2">Stok: {{ product.stock }}</p>
              <p class="text-lg font-bold text-blue-600">
                Rp {{ formatPrice(product.price) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Mobile Cart Tab -->
      <div v-show="activeTab === 'cart'" class="space-y-4">
        <div class="bg-white rounded-lg shadow-sm p-4">
          <h3 class="text-lg font-semibold mb-4 flex items-center">
            <i class="fas fa-shopping-cart mr-2"></i>
            Keranjang Belanja
          </h3>

          <!-- Mobile Cart Items -->
          <div v-if="cartItems.length === 0" class="text-center py-8 text-gray-500">
            <i class="fas fa-shopping-cart text-4xl mb-4"></i>
            <p>Keranjang kosong</p>
          </div>

          <div v-else class="space-y-3">
            <div 
              v-for="(item, index) in cartItems" 
              :key="index"
              class="flex items-center space-x-3 bg-gray-50 rounded-lg p-3"
            >
              <img 
                v-if="item.image" 
                :src="'/storage/' + item.image" 
                :alt="item.name"
                class="w-12 h-12 object-cover rounded-lg"
              >
              <div v-else class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                <i class="fas fa-box text-gray-400"></i>
              </div>
              
              <div class="flex-1">
                <h4 class="font-medium text-sm">{{ item.name }}</h4>
                <p class="text-xs text-gray-500">Rp {{ formatPrice(item.price) }}</p>
              </div>
              
              <div class="flex items-center space-x-2">
                <button 
                  @click="decreaseQuantity(index)"
                  class="w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center text-sm"
                >
                  <i class="fas fa-minus"></i>
                </button>
                <span class="w-8 text-center font-medium">{{ item.quantity }}</span>
                <button 
                  @click="increaseQuantity(index)"
                  class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm"
                >
                  <i class="fas fa-plus"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Mobile Cart Summary -->
          <div v-if="cartItems.length > 0" class="mt-6 pt-4 border-t border-gray-200">
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span>Subtotal:</span>
                <span>Rp {{ formatPrice(subtotal) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Diskon:</span>
                <span>Rp {{ formatPrice(discount) }}</span>
              </div>
              <div class="flex justify-between">
                <span>Pajak:</span>
                <span>Rp {{ formatPrice(tax) }}</span>
              </div>
              <div class="flex justify-between text-lg font-bold border-t pt-2">
                <span>Total:</span>
                <span>Rp {{ formatPrice(total) }}</span>
              </div>
            </div>

            <!-- Mobile Payment Form -->
            <div class="mt-6 pt-4 border-t border-gray-200 space-y-4">
              <!-- Payment Method -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                <select 
                  v-model="paymentMethod"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                >
                  <option value="cash">Tunai</option>
                  <option value="card">Kartu Debit/Kredit</option>
                  <option value="ewallet">E-Wallet</option>
                  <option value="online">Transfer/Online</option>
                </select>
              </div>
              
              <!-- Paid Amount -->
              <div v-if="paymentMethod !== 'online'">
                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Bayar</label>
                <input 
                  type="number" 
                  v-model.number="paidAmount" 
                  :min="total" 
                  step="1000" 
                  :readonly="paymentMethod !== 'cash'"
                  :class="paymentMethod !== 'cash' ? 'bg-gray-100' : ''"
                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                  placeholder="Masukkan jumlah bayar"
                >
                
                <!-- Quick Amount Buttons (for Cash only) -->
                <div v-if="paymentMethod === 'cash'" class="mt-2 grid grid-cols-3 gap-2">
                  <button 
                    @click="paidAmount = total"
                    type="button"
                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-xs font-medium"
                  >
                    Pas
                  </button>
                  <button 
                    @click="paidAmount = Math.ceil(total / 50000) * 50000"
                    type="button"
                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-xs font-medium"
                  >
                    50rb
                  </button>
                  <button 
                    @click="paidAmount = Math.ceil(total / 100000) * 100000"
                    type="button"
                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-xs font-medium"
                  >
                    100rb
                  </button>
                </div>
                
                <!-- Non-cash Payment Info -->
                <div v-if="paymentMethod !== 'cash'" class="mt-2 p-2 bg-blue-50 rounded-lg">
                  <div class="text-xs text-blue-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    <span v-if="paymentMethod === 'card'">Pembayaran dengan kartu debit/kredit</span>
                    <span v-if="paymentMethod === 'ewallet'">Pembayaran dengan e-wallet</span>
                    <span v-if="paymentMethod === 'online'">Pembayaran online akan diarahkan ke halaman iPaymu</span>
                  </div>
                </div>
                
                <!-- Customer Info for Online Payment -->
                <div v-if="paymentMethod === 'online'" class="mt-3 space-y-3 p-3 bg-gray-50 rounded-lg">
                  <div class="text-sm font-medium text-gray-700">Data Pelanggan</div>
                  <div>
                    <input 
                      type="text" 
                      v-model="customerInfo.name" 
                      placeholder="Nama lengkap"
                      class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      required
                    >
                  </div>
                  <div>
                    <input 
                      type="tel" 
                      v-model="customerInfo.phone" 
                      placeholder="Nomor telepon (08xxxxxxxxx)"
                      class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      required
                    >
                  </div>
                  <div>
                    <input 
                      type="email" 
                      v-model="customerInfo.email" 
                      placeholder="Email"
                      class="w-full text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      required
                    >
                  </div>
                </div>
                
                <!-- Customer Info Validation Status for Online Payment -->
                <div v-if="paymentMethod === 'online'" class="mt-2">
                  <div v-if="!isCustomerInfoValid" class="p-2 bg-yellow-50 rounded-lg">
                    <div class="text-xs text-yellow-600">
                      <i class="fas fa-exclamation-triangle mr-1"></i>
                      <span>Mohon lengkapi data pelanggan untuk melanjutkan</span>
                    </div>
                  </div>
                  <div v-else class="p-2 bg-green-50 rounded-lg">
                    <div class="text-xs text-green-600">
                      <i class="fas fa-check-circle mr-1"></i>
                      <span>Data pelanggan lengkap, siap untuk pembayaran online</span>
                    </div>
                  </div>
                </div>
                
                <!-- Change Amount -->
                <div v-if="paidAmount >= total && paymentMethod === 'cash'" class="mt-2 p-2 bg-blue-50 rounded-lg">
                  <div class="text-xs text-blue-600">
                    <span class="font-medium">Kembalian: Rp {{ formatPrice(paidAmount - total) }}</span>
                  </div>
                </div>
                
                <!-- Insufficient Payment Warning -->
                <div v-if="paidAmount > 0 && paidAmount < total && paymentMethod === 'cash'" class="mt-2 p-2 bg-red-50 rounded-lg">
                  <div class="text-xs text-red-600">
                    <span class="font-medium">Kurang: Rp {{ formatPrice(total - paidAmount) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Mobile Action Buttons -->
            <div class="mt-4 space-y-2">
              <button 
                @click="processTransaction()"
                :disabled="!canProcess"
                :class="canProcess ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-400 cursor-not-allowed'"
                class="w-full text-white font-bold py-4 px-6 rounded-xl text-lg transition-colors"
              >
                <i class="fas fa-credit-card mr-2"></i>
                <span v-if="canProcess && paymentMethod === 'online'">Bayar Online</span>
                <span v-else-if="canProcess">Proses Transaksi</span>
                <span v-else>Lengkapi Pembayaran</span>
              </button>
              
              <button 
                @click="showSaveDraftModal = true"
                class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-6 rounded-xl transition-colors"
              >
                <i class="fas fa-save mr-2"></i>
                Simpan Draft
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Desktop Layout -->
    <div class="hidden md:grid md:grid-cols-3 gap-6">
      <!-- Left Panel - Product Search & List -->
      <div class="md:col-span-2">
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <!-- Search -->
            <div class="mb-6">
              <div class="relative">
                <input 
                  type="text" 
                  placeholder="Cari produk atau scan barcode..."
                  class="w-full pl-12 pr-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  v-model="searchQuery"
                  @input="filterProducts"
                >
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <i class="fas fa-search text-gray-400 text-lg"></i>
                </div>
              </div>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
              <div 
                v-for="product in filteredProducts" 
                :key="product.id"
                @click="addToCart(product)"
                class="border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 hover:shadow-md transition-all"
                :class="{ 'opacity-50': product.stock <= 0 }"
              >
                <div class="aspect-square mb-3">
                  <img 
                    v-if="product.image" 
                    :src="'/storage/' + product.image" 
                    :alt="product.name"
                    class="w-full h-full object-cover rounded-lg"
                  >
                  <div v-else class="w-full h-full bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-gray-400 text-3xl"></i>
                  </div>
                </div>
                <h3 class="font-medium text-sm text-gray-900 leading-tight mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                  {{ product.name }}
                </h3>
                <p class="text-xs text-gray-500 mb-2">Stok: {{ product.stock }}</p>
                <p class="text-lg font-bold text-blue-600">
                  Rp {{ formatPrice(product.price) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Panel - Cart & Checkout -->
      <div>
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <h2 class="text-xl font-semibold mb-4">
              <i class="fas fa-shopping-cart mr-2"></i>
              Keranjang
            </h2>

            <!-- Cart Items -->
            <div v-if="cartItems.length === 0" class="text-center py-8 text-gray-500">
              <i class="fas fa-shopping-cart text-4xl mb-4"></i>
              <p>Keranjang kosong</p>
            </div>

            <div v-else class="space-y-3 mb-6">
              <div 
                v-for="(item, index) in cartItems" 
                :key="index"
                class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg"
              >
                <img 
                  v-if="item.image" 
                  :src="'/storage/' + item.image" 
                  :alt="item.name"
                  class="w-12 h-12 object-cover rounded-lg"
                >
                <div v-else class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                  <i class="fas fa-box text-gray-400"></i>
                </div>
                
                <div class="flex-1">
                  <h4 class="font-medium text-sm">{{ item.name }}</h4>
                  <p class="text-xs text-gray-500">Rp {{ formatPrice(item.price) }}</p>
                </div>
                
                <div class="flex items-center space-x-2">
                  <button 
                    @click="decreaseQuantity(index)"
                    class="w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs"
                  >
                    <i class="fas fa-minus"></i>
                  </button>
                  <span class="w-8 text-center font-medium text-sm">{{ item.quantity }}</span>
                  <button 
                    @click="increaseQuantity(index)"
                    class="w-6 h-6 bg-green-500 text-white rounded-full flex items-center justify-center text-xs"
                  >
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Cart Summary -->
            <div v-if="cartItems.length > 0">
              <div class="space-y-2 text-sm border-t pt-4">
                <div class="flex justify-between">
                  <span>Subtotal:</span>
                  <span>Rp {{ formatPrice(subtotal) }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Diskon:</span>
                  <span>Rp {{ formatPrice(discount) }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Pajak:</span>
                  <span>Rp {{ formatPrice(tax) }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold border-t pt-2">
                  <span>Total:</span>
                  <span>Rp {{ formatPrice(total) }}</span>
                </div>
              </div>

              <!-- Payment Form -->
              <div class="mt-6 space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                  <select 
                    v-model="paymentMethod"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  >
                    <option value="cash">Tunai</option>
                    <option value="card">Kartu Debit/Kredit</option>
                    <option value="ewallet">E-Wallet</option>
                    <option value="online">Transfer/Online</option>
                  </select>
                </div>

                <div v-if="paymentMethod !== 'online'">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Bayar</label>
                  <input 
                    type="number" 
                    v-model.number="paidAmount" 
                    :min="total" 
                    step="1000"
                    :readonly="paymentMethod !== 'cash'"
                    :class="paymentMethod !== 'cash' ? 'bg-gray-100' : ''"
                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                  >
                </div>
                
                <!-- Customer Info for Online Payment - Desktop -->
                <div v-if="paymentMethod === 'online'" class="space-y-3">
                  <div class="text-sm font-medium text-gray-700 mb-2">Data Pelanggan</div>
                  <div>
                    <label for="customer-name" class="block text-xs text-gray-600 mb-1">Nama Lengkap</label>
                    <input 
                      type="text" 
                      id="customer-name"
                      v-model="customerInfo.name" 
                      placeholder="Nama lengkap"
                      class="w-full text-sm border border-gray-300 rounded-md px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                      required
                    >
                  </div>
                  <div>
                    <label for="customer-phone" class="block text-xs text-gray-600 mb-1">Nomor Telepon</label>
                    <input 
                      type="tel" 
                      id="customer-phone"
                      v-model="customerInfo.phone" 
                      placeholder="08xxxxxxxxx"
                      class="w-full text-sm border border-gray-300 rounded-md px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                      required
                    >
                  </div>
                  <div>
                    <label for="customer-email" class="block text-xs text-gray-600 mb-1">Email</label>
                    <input 
                      type="email" 
                      id="customer-email"
                      v-model="customerInfo.email" 
                      placeholder="email@example.com"
                      class="w-full text-sm border border-gray-300 rounded-md px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
                      required
                    >
                  </div>
                </div>

                <div v-if="paidAmount >= total && paymentMethod === 'cash'" class="text-sm">
                  <span class="text-gray-600">Kembalian: </span>
                  <span class="font-medium">Rp {{ formatPrice(paidAmount - total) }}</span>
                </div>
              </div>
              
              <!-- Action Buttons -->
              <div class="mt-6 space-y-2">
                <button 
                  @click="processTransaction()"
                  :disabled="!canProcess"
                  class="w-full bg-blue-500 hover:bg-blue-700 disabled:bg-gray-300 text-white font-bold py-3 px-4 rounded"
                >
                  <span v-if="paymentMethod === 'online'">Bayar Online</span>
                  <span v-else>Proses Transaksi</span>
                </button>
                
                <button 
                  @click="showSaveDraftModal = true"
                  class="w-full bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded"
                >
                  <i class="fas fa-save mr-2"></i>
                  Simpan Draft
                </button>
                
                <button 
                  @click="clearCart"
                  class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                >
                  Bersihkan Keranjang
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Debug Panel (Temporary) -->
    <div class="fixed top-4 right-4 bg-yellow-100 p-2 rounded text-xs z-50 border">
      <div>Drafts: {{ drafts.length }}</div>
      <div>Cart: {{ cartItems.length }}</div>
      <div>Show Save Modal: {{ showSaveDraftModal }}</div>
      <div>Show Mobile Drafts: {{ showMobileDrafts }}</div>
      <button @click="console.log('Vue data:', {drafts: drafts, cartItems: cartItems})" class="bg-blue-500 text-white px-2 py-1 rounded text-xs mt-1">Log Data</button>
      <button @click="showSaveDraftModal = true; console.log('Modal opened')" class="bg-green-500 text-white px-2 py-1 rounded text-xs mt-1">Test Modal</button>
      <button @click="testDraft()" class="bg-purple-500 text-white px-2 py-1 rounded text-xs mt-1">Test Draft</button>
    </div>

    <!-- Draft List Sidebar (Desktop) -->
    <div v-show="drafts.length > 0" class="hidden md:block fixed right-4 top-1/2 transform -translate-y-1/2 w-64 bg-white shadow-lg rounded-lg p-4 z-40">
      <h4 class="font-bold text-gray-800 mb-3">
        <i class="fas fa-save mr-2"></i>
        Draft Tersimpan ({{ drafts.length }})
      </h4>
      <div class="space-y-2 max-h-80 overflow-y-auto">
        <div 
          v-for="draft in drafts" 
          :key="draft.id"
          class="p-3 bg-gray-50 rounded-lg border hover:bg-blue-50 hover:border-blue-300 transition-colors"
        >
          <div class="flex justify-between items-start">
            <div @click="loadDraft(draft)" class="flex-1 cursor-pointer">
              <h5 class="font-medium text-sm text-gray-800 truncate" :title="draft.draft_name">
                {{ draft.draft_name }}
              </h5>
              <div class="mt-1 space-y-1">
                <p class="text-xs text-gray-500">
                  <i class="fas fa-shopping-cart mr-1"></i>
                  {{ draft.items ? draft.items.length : 0 }} items
                </p>
                <p class="text-xs font-medium text-blue-600">
                  <i class="fas fa-money-bill mr-1"></i>
                  Rp {{ formatPrice(draft.total) }}
                </p>
                <p class="text-xs text-gray-400">
                  <i class="fas fa-clock mr-1"></i>
                  {{ formatDate(draft.created_at) }}
                </p>
              </div>
            </div>
            <button 
              @click.stop="deleteDraft(draft.id)"
              class="text-red-400 hover:text-red-600 ml-2 p-1 hover:bg-red-50 rounded transition-colors"
              title="Hapus draft"
            >
              <i class="fas fa-trash text-xs"></i>
            </button>
          </div>
        </div>
      </div>
      
      <!-- Mobile Draft Access Button -->
      <div class="mt-3">
        <button 
          @click="showMobileDrafts = true"
          class="w-full text-xs bg-blue-500 hover:bg-blue-600 text-white py-2 px-3 rounded-lg md:hidden"
        >
          <i class="fas fa-mobile-alt mr-1"></i>
          Lihat di Mobile
        </button>
      </div>
    </div>

    <!-- Mobile Draft Modal -->
    <div v-show="showMobileDrafts" class="md:hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showMobileDrafts = false">
      <div class="bg-white rounded-lg w-full max-w-sm mx-4 max-h-96 flex flex-col">
        <div class="flex justify-between items-center p-4 border-b">
          <h3 class="text-lg font-bold">Draft Tersimpan</h3>
          <button @click="showMobileDrafts = false" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-4">
          <div v-if="drafts.length === 0" class="text-center py-8 text-gray-500">
            <i class="fas fa-save text-3xl mb-2"></i>
            <p>Belum ada draft tersimpan</p>
          </div>
          
          <div v-else class="space-y-3">
            <div 
              v-for="draft in drafts" 
              :key="draft.id"
              class="p-3 bg-gray-50 rounded-lg border"
            >
              <div class="flex justify-between items-start">
                <div @click="loadDraft(draft); showMobileDrafts = false" class="flex-1 cursor-pointer">
                  <h5 class="font-medium text-sm text-gray-800">{{ draft.draft_name }}</h5>
                  <div class="mt-1 space-y-1">
                    <p class="text-xs text-gray-500">{{ draft.items ? draft.items.length : 0 }} items</p>
                    <p class="text-xs font-medium text-blue-600">Rp {{ formatPrice(draft.total) }}</p>
                    <p class="text-xs text-gray-400">{{ formatDate(draft.created_at) }}</p>
                  </div>
                </div>
                <button 
                  @click.stop="deleteDraft(draft.id)"
                  class="text-red-400 hover:text-red-600 ml-2 p-1"
                  title="Hapus draft"
                >
                  <i class="fas fa-trash text-xs"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Save Draft Modal -->
    <div v-show="showSaveDraftModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="showSaveDraftModal = false">
      <div class="bg-white rounded-lg p-6 w-full max-w-lg mx-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold">Simpan Draft Transaksi</h3>
          <button @click="showSaveDraftModal = false" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Draft</label>
            <input 
              type="text" 
              v-model="draftName" 
              placeholder="Masukkan nama untuk draft ini..."
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-blue-500"
              @keydown.enter="saveDraft"
            >
          </div>

          <div class="bg-gray-50 p-3 rounded-lg">
            <div class="text-sm text-gray-600">
              <p><strong>Items:</strong> {{ cartItems.length }} produk</p>
              <p><strong>Total:</strong> Rp {{ formatPrice(total) }}</p>
            </div>
          </div>

          <div class="flex space-x-3">
            <button 
              @click="showSaveDraftModal = false"
              class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded"
            >
              Batal
            </button>
            <button 
              @click="saveDraft"
              :disabled="!draftName.trim()"
              class="flex-1 bg-yellow-500 hover:bg-yellow-600 disabled:bg-gray-300 text-white font-bold py-2 px-4 rounded"
            >
              <i class="fas fa-save mr-2"></i>
              Simpan Draft
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PosApp',
  props: {
    products: {
      type: Array,
      default: () => []
    },
    drafts: {
      type: Array,
      default: () => []
    },
    retryTransaction: {
      type: Object,
      default: null
    },
    draftTransaction: {
      type: Object,
      default: null
    },
    csrfToken: {
      type: String,
      required: true
    },
    routes: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      searchQuery: '',
      filteredProducts: [...this.products],
      cartItems: [],
      activeTab: 'products',
      discount: 0,
      tax: 0,
      paymentMethod: 'cash',
      paidAmount: 0,
      // Online payment properties
      showOnlineModal: false,
      showInstructionsModal: false,
      paymentChannels: [],
      selectedChannel: null,
      loadingChannels: false,
      processingPayment: false,
      checkingStatus: false,
      paymentInstructions: null,
      currentTransactionId: null,
      customerInfo: {
        name: '',
        phone: '',
        email: ''
      },
      // Draft functionality
      showSaveDraftModal: false,
      showMobileDrafts: false,
      draftName: ''
    }
  },
  computed: {
    subtotal() {
      return this.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    },
    total() {
      return this.subtotal - this.discount + this.tax;
    },
    canProcess() {
      if (this.cartItems.length === 0) return false;
      
      if (this.paymentMethod === 'cash') {
        return this.paidAmount >= this.total;
      }
      
      // For online payment, need valid customer info
      if (this.paymentMethod === 'online') {
        return this.isCustomerInfoValid;
      }
      
      // For card/ewallet, exact amount is acceptable
      return this.paidAmount === this.total || this.paidAmount >= this.total;
    },
    isCustomerInfoValid() {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      const isValid = this.customerInfo.name.trim() && 
             this.customerInfo.phone.trim() && 
             this.customerInfo.email.trim() && 
             emailRegex.test(this.customerInfo.email);
      
      return isValid;
    }
  },
  watch: {
    discount() {
      this.updatePaidAmount();
    },
    tax() {
      this.updatePaidAmount();
    },
    // Auto-update paid amount when payment method changes
    paymentMethod(newMethod, oldMethod) {
      // For non-cash payments, set exact amount
      if (newMethod !== 'cash') {
        this.paidAmount = this.total;
      }
      // For cash, keep existing amount if sufficient, otherwise set to total
      else if (newMethod === 'cash' && this.paidAmount < this.total) {
        this.paidAmount = this.total;
      }
    }
  },
  methods: {
    filterProducts() {
      if (!this.searchQuery) {
        this.filteredProducts = this.products;
        return;
      }
      
      const query = this.searchQuery.toLowerCase();
      this.filteredProducts = this.products.filter(product => 
        product.name.toLowerCase().includes(query) ||
        product.sku.toLowerCase().includes(query) ||
        (product.barcode && product.barcode.toLowerCase().includes(query))
      );
    },
    formatPrice(price) {
      return new Intl.NumberFormat('id-ID').format(price);
    },
    addToCart(product) {
      if (product.stock <= 0) return;
      
      const existingItem = this.cartItems.find(item => item.id === product.id);
      
      if (existingItem) {
        if (existingItem.quantity < product.stock) {
          existingItem.quantity++;
        }
      } else {
        this.cartItems.push({
          id: product.id,
          name: product.name,
          price: product.price,
          quantity: 1,
          image: product.image,
          stock: product.stock
        });
      }
      
      // Switch to cart tab on mobile
      if (window.innerWidth < 768) {
        this.activeTab = 'cart';
      }
    },
    increaseQuantity(index) {
      const item = this.cartItems[index];
      if (item.quantity < item.stock) {
        item.quantity++;
      }
    },
    decreaseQuantity(index) {
      const item = this.cartItems[index];
      if (item.quantity > 1) {
        item.quantity--;
      } else {
        this.cartItems.splice(index, 1);
        
        // Switch to products tab if cart is empty on mobile
        if (this.cartItems.length === 0 && window.innerWidth < 768) {
          this.activeTab = 'products';
        }
      }
    },
    updatePaidAmount() {
      if (this.paymentMethod !== 'cash') {
        this.paidAmount = this.total;
      }
    },
    clearCart() {
      this.cartItems = [];
      this.discount = 0;
      this.tax = 0;
      this.paidAmount = 0;
      if (window.innerWidth < 768) {
        this.activeTab = 'products';
      }
    },
    async processTransaction() {
      console.log('processTransaction called:', {
        canProcess: this.canProcess,
        paymentMethod: this.paymentMethod,
        cartItems: this.cartItems.length
      });
      
      if (!this.canProcess) {
        alert('Mohon lengkapi informasi pembayaran');
        return;
      }
      
      if (this.paymentMethod === 'online') {
        await this.processOnlinePayment();
        return;
      }
      
      try {
        const data = {
          items: this.cartItems.map(item => ({
            product_id: item.id,
            quantity: item.quantity
          })),
          subtotal: this.subtotal,
          discount: this.discount,
          tax: this.tax,
          total: this.total,
          paid: this.paidAmount,
          payment_method: this.paymentMethod,
          notes: ''
        };
        
        const response = await fetch(this.routes.posTransaction, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken
          },
          body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
          alert('Transaksi berhasil diproses!');
          this.clearCart();
          // Optionally redirect to receipt or transaction details
        } else {
          alert('Error: ' + result.message);
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Terjadi error saat memproses transaksi');
      }
    },
    
    async processOnlinePayment() {
      try {
        const data = {
          transaction_id: this.currentTransactionId,
          customer_name: this.customerInfo.name,
          customer_phone: this.customerInfo.phone,
          customer_email: this.customerInfo.email
        };
        
        // First create the transaction
        const transactionData = {
          items: this.cartItems.map(item => ({
            product_id: item.id,
            quantity: item.quantity
          })),
          subtotal: this.subtotal,
          discount: this.discount,
          tax: this.tax,
          total: this.total,
          paid: this.total,
          payment_method: 'online',
          notes: 'Online payment'
        };
        
        const transactionResponse = await fetch(this.routes.posTransaction, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken
          },
          body: JSON.stringify(transactionData)
        });
        
        const transactionResult = await transactionResponse.json();
        
        if (!transactionResult.success) {
          throw new Error(transactionResult.message);
        }
        
        // Then create payment
        data.transaction_id = transactionResult.transaction.id;
        
        const response = await fetch(this.routes.paymentCreate, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken
          },
          body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success && result.data.redirect_to) {
          window.location.href = result.data.redirect_to;
        } else {
          alert('Error: ' + result.message);
        }
      } catch (error) {
        console.error('Error processing online payment:', error);
        alert('Terjadi error saat memproses pembayaran online: ' + error.message);
      }
    },
    
    // Draft functionality methods
    saveDraft() {
      console.log('saveDraft called');
      console.log('Draft name:', this.draftName);
      console.log('Cart items:', this.cartItems);
      
      if (!this.draftName.trim()) {
        alert('Mohon masukkan nama draft');
        return;
      }
      
      if (this.cartItems.length === 0) {
        alert('Keranjang kosong. Tambahkan produk terlebih dahulu.');
        return;
      }
      
      this.saveDraftAsync();
    },
    
    async saveDraftAsync() {
      try {
        const data = {
          items: this.cartItems.map(item => ({
            product_id: item.id,
            quantity: item.quantity
          })),
          subtotal: this.subtotal,
          discount: this.discount,
          tax: this.tax,
          total: this.total,
          draft_name: this.draftName.trim(),
          notes: 'Saved as draft'
        };
        
        console.log('Sending draft data:', data);
        
        const response = await fetch(this.routes.draftSave, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.csrfToken
          },
          body: JSON.stringify(data)
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        console.log('Save draft result:', result);
        
        if (result.success) {
          // Add to drafts list
          this.drafts.unshift(result.transaction);
          
          // Clear form
          this.draftName = '';
          this.showSaveDraftModal = false;
          this.clearCart();
          
          alert('Draft berhasil disimpan!');
        } else {
          alert('Error: ' + result.message);
        }
      } catch (error) {
        console.error('Error saving draft:', error);
        alert('Terjadi error saat menyimpan draft: ' + error.message);
      }
    },
    
    async loadDraft(draft) {
      try {
        // Clear current cart
        this.clearCart();
        
        // Load draft items to cart
        if (draft.items && draft.items.length > 0) {
          draft.items.forEach(item => {
            const product = this.products.find(p => p.id === item.product_id);
            if (product) {
              this.cartItems.push({
                id: product.id,
                name: product.name,
                price: product.price,
                quantity: item.quantity,
                image: product.image,
                stock: product.stock
              });
            }
          });
        }
        
        // Load other data
        this.discount = draft.discount || 0;
        this.tax = draft.tax || 0;
        
        // Switch to cart tab on mobile
        if (window.innerWidth < 768) {
          this.activeTab = 'cart';
        }
        
        // Show success notification
        alert(`Draft "${draft.draft_name}" berhasil dimuat!`);
        
        console.log('Draft loaded:', draft.draft_name);
      } catch (error) {
        console.error('Error loading draft:', error);
        alert('Terjadi error saat memuat draft');
      }
    },
    
    async deleteDraft(draftId) {
      console.log('deleteDraft called for ID:', draftId);
      
      if (!confirm('Hapus draft ini?')) return;
      
      try {
        const response = await fetch(`${this.routes.draftDelete}/${draftId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': this.csrfToken
          }
        });
        
        console.log('Delete response status:', response.status);
        
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        console.log('Delete draft result:', result);
        
        if (result.success) {
          // Remove from drafts list
          this.drafts = this.drafts.filter(d => d.id !== draftId);
          alert('Draft berhasil dihapus');
          console.log('Draft deleted');
        } else {
          alert('Error: ' + result.message);
        }
      } catch (error) {
        console.error('Error deleting draft:', error);
        alert('Terjadi error saat menghapus draft: ' + error.message);
      }
    },
    
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    
    loadTransactionData(transaction) {
      try {
        // Clear current cart
        this.clearCart();
        
        // Load transaction items to cart
        transaction.items.forEach(item => {
          const product = this.products.find(p => p.id === item.product_id);
          if (product) {
            this.cartItems.push({
              id: product.id,
              name: product.name,
              price: product.price,
              quantity: item.quantity,
              image: product.image,
              stock: product.stock
            });
          }
        });
        
        // Load other transaction data
        this.discount = transaction.discount || 0;
        this.tax = transaction.tax || 0;
        
        // For retry transactions, preserve payment method
        if (transaction.payment_method && !transaction.is_draft) {
          this.paymentMethod = transaction.payment_method;
          this.paidAmount = transaction.total;
        }
        
        // Switch to cart tab on mobile
        if (window.innerWidth < 768) {
          this.activeTab = 'cart';
        }
        
        console.log('Transaction data loaded:', transaction.transaction_number || transaction.draft_name);
      } catch (error) {
        console.error('Error loading transaction data:', error);
        alert('Terjadi error saat memuat data transaksi');
      }
    },
    
    testDraft() {
      console.log('testDraft called');
      alert('Test Draft called! Vue methods are working.');
      this.showSaveDraftModal = true;
    }
  },
  mounted() {
    // Ensure modals are properly hidden on mount
    this.showOnlineModal = false;
    this.showInstructionsModal = false;
    this.showSaveDraftModal = false;
    this.showMobileDrafts = false;
    
    // Load retry transaction if available
    if (this.retryTransaction) {
      console.log('Loading retry transaction:', this.retryTransaction.transaction_number);
      this.loadTransactionData(this.retryTransaction);
    }
    
    // Load draft transaction if available
    if (this.draftTransaction) {
      console.log('Loading draft transaction:', this.draftTransaction.draft_name);
      this.loadTransactionData(this.draftTransaction);
    }
    
    // Add ESC key handler for modal closing
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        if (this.showOnlineModal) {
          this.showOnlineModal = false;
        } else if (this.showInstructionsModal) {
          this.showInstructionsModal = false;
        } else if (this.showSaveDraftModal) {
          this.showSaveDraftModal = false;
        } else if (this.showMobileDrafts) {
          this.showMobileDrafts = false;
        }
      }
    });
    
    console.log('POS app mounted, modals hidden');
  }
}
</script>