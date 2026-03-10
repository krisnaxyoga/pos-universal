// Offline Product Management
(function () {
    'use strict';

    window.OfflineProducts = {
        /**
         * Intercept product form submission (create/edit) when offline.
         * @param {HTMLFormElement} form
         * @param {string} actionType - 'create' or 'update'
         * @param {number|null} productId - product ID for updates
         */
        interceptForm(form, actionType, productId) {
            form.addEventListener('submit', async (e) => {
                const isOnline = window.connectivityMonitor
                    ? window.connectivityMonitor.isOnline
                    : navigator.onLine;

                if (isOnline) return; // Let normal form submission proceed

                e.preventDefault();

                // Gather form data (excluding image - can't store file offline reliably)
                const formData = new FormData(form);
                const data = {};
                for (const [key, value] of formData.entries()) {
                    if (key === 'image' || key === '_token' || key === '_method') continue;
                    data[key] = value;
                }

                // Check for image - warn user
                const imageInput = form.querySelector('input[type="file"]');
                const hasImage = imageInput && imageInput.files && imageInput.files.length > 0;
                if (hasImage) {
                    alert('Gambar produk tidak bisa disimpan dalam mode offline. Gambar akan diabaikan dan bisa ditambahkan nanti saat online.');
                }

                const action = {
                    action_id: crypto.randomUUID(),
                    action_type: actionType, // 'create', 'update', 'delete'
                    product_id: productId,
                    data: data,
                    status: 'pending',
                    created_at: new Date().toISOString(),
                    synced_at: null,
                    error_message: null
                };

                try {
                    await window.posDB.queueProductAction(action);

                    // Also update local IndexedDB products store
                    if (actionType === 'create') {
                        // Use a temporary negative ID for new products
                        const tempId = -Date.now();
                        await window.posDB.putProduct({
                            id: tempId,
                            name: data.name || '',
                            sku: data.sku || '',
                            barcode: data.barcode || '',
                            description: data.description || '',
                            price: parseFloat(data.price) || 0,
                            cost: parseFloat(data.cost) || 0,
                            stock: parseInt(data.stock) || 0,
                            min_stock: parseInt(data.min_stock) || 0,
                            category_id: parseInt(data.category_id) || null,
                            category_name: '',
                            image: '',
                            is_active: data.is_active === '1',
                            synced_at: null,
                            _offline_new: true
                        });
                    } else if (actionType === 'update' && productId) {
                        const existing = await window.posDB.getProduct(productId);
                        if (existing) {
                            Object.assign(existing, {
                                name: data.name || existing.name,
                                sku: data.sku || existing.sku,
                                barcode: data.barcode || existing.barcode,
                                description: data.description || existing.description,
                                price: parseFloat(data.price) || existing.price,
                                cost: parseFloat(data.cost) || existing.cost,
                                stock: parseInt(data.stock) ?? existing.stock,
                                min_stock: parseInt(data.min_stock) ?? existing.min_stock,
                                category_id: parseInt(data.category_id) || existing.category_id,
                                is_active: data.is_active === '1',
                                synced_at: null
                            });
                            await window.posDB.putProduct(existing);
                        }
                    }

                    if (typeof window.updatePendingSyncBadge === 'function') {
                        window.updatePendingSyncBadge();
                    }

                    // Show success and redirect
                    const msg = actionType === 'create'
                        ? 'Produk disimpan offline. Akan disinkronkan saat online.'
                        : 'Perubahan disimpan offline. Akan disinkronkan saat online.';
                    alert(msg);
                    window.location.href = '/products';

                } catch (error) {
                    console.error('Failed to queue product action:', error);
                    alert('Gagal menyimpan offline. Coba lagi.');
                }
            });
        },

        /**
         * Handle product delete when offline.
         * @param {number} productId
         * @param {string} productName
         */
        async deleteOffline(productId, productName) {
            if (!confirm(`Hapus produk "${productName}" (offline)?`)) return false;

            try {
                const action = {
                    action_id: crypto.randomUUID(),
                    action_type: 'delete',
                    product_id: productId,
                    data: { name: productName },
                    status: 'pending',
                    created_at: new Date().toISOString(),
                    synced_at: null,
                    error_message: null
                };

                await window.posDB.queueProductAction(action);
                await window.posDB.removeProduct(productId);

                if (typeof window.updatePendingSyncBadge === 'function') {
                    window.updatePendingSyncBadge();
                }

                alert('Produk dihapus offline. Akan disinkronkan saat online.');
                return true;
            } catch (error) {
                console.error('Failed to queue delete:', error);
                alert('Gagal menghapus offline.');
                return false;
            }
        },

        /**
         * Render offline product list when page is served from cache.
         * Replaces both mobile card view and desktop table with IndexedDB data.
         */
        async renderOfflineList() {
            const isOnline = window.connectivityMonitor
                ? window.connectivityMonitor.isOnline
                : navigator.onLine;

            if (isOnline) return; // Online — use server-rendered content

            try {
                const products = await window.posDB.getProducts();

                // Show offline notice
                const notice = document.getElementById('offline-product-notice');
                if (notice) notice.classList.remove('hidden');

                const fmt = (n) => new Intl.NumberFormat('id-ID').format(n);
                const imgHtml = (p, size) => p.image
                    ? `<img src="/${p.image}" class="h-${size} w-${size} rounded-lg object-cover" onerror="this.style.display='none'">`
                    : `<div class="h-${size} w-${size} rounded-lg bg-gray-200 flex items-center justify-center"><svg class="h-${Math.floor(size/2)} w-${Math.floor(size/2)} text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>`;
                const offlineBadge = (p) => p._offline_new ? '<span class="ml-1 px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">Offline</span>' : '';
                const statusBadge = (p) => p.is_active
                    ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>'
                    : '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Nonaktif</span>';
                const lowStock = (p) => p.stock <= (p.min_stock || 5);

                // === Mobile Card View ===
                const mobileContainer = document.getElementById('product-cards-mobile');
                if (mobileContainer) {
                    if (!products || products.length === 0) {
                        mobileContainer.innerHTML = `
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-box-open text-3xl mb-2 block"></i>
                                <p>Belum ada produk (offline).</p>
                            </div>`;
                    } else {
                        mobileContainer.innerHTML = products.map(p => `
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 h-14 w-14">${imgHtml(p, 14)}</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <h3 class="text-sm font-semibold text-gray-900 truncate">${p.name}</h3>
                                                <p class="text-xs text-gray-500">${p.sku || '-'}</p>
                                            </div>
                                            ${statusBadge(p)}${offlineBadge(p)}
                                        </div>
                                        <div class="mt-2 text-sm">
                                            <span class="font-semibold text-gray-900">Rp ${fmt(p.price)}</span>
                                            <span class="text-xs text-gray-400 ml-1">(Modal: Rp ${fmt(p.cost)})</span>
                                        </div>
                                        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                                            <span><i class="fas fa-layer-group mr-1"></i>${p.category_name || '-'}</span>
                                            <span class="${lowStock(p) ? 'text-red-600 font-bold' : ''}">
                                                <i class="fas fa-cubes mr-1"></i>Stok: ${p.stock}
                                                ${lowStock(p) ? '<i class="fas fa-exclamation-triangle ml-1"></i>' : ''}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-400 italic">
                                    <i class="fas fa-wifi-slash mr-1"></i>Mode offline — aksi terbatas
                                </div>
                            </div>
                        `).join('');
                    }
                }

                // === Desktop Table View ===
                const tbody = document.querySelector('table tbody');
                if (tbody) {
                    if (!products || products.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada produk (offline).</td></tr>';
                    } else {
                        tbody.innerHTML = products.map(p => `
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">${imgHtml(p, 10)}</div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">${p.name}</div>
                                            <div class="text-sm text-gray-500">${p.sku || '-'}</div>
                                            ${p.barcode ? `<div class="text-xs text-gray-400">${p.barcode}</div>` : ''}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${p.category_name || '-'}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Rp ${fmt(p.price)}</div>
                                    <div class="text-xs text-gray-500">Modal: Rp ${fmt(p.cost)}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">Stok: ${p.stock}</div>
                                    ${lowStock(p) ? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Stok Menipis</span>' : ''}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    ${statusBadge(p)}${offlineBadge(p)}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="italic text-xs">Offline mode</span>
                                </td>
                            </tr>
                        `).join('');
                    }
                }

                // Hide pagination (not relevant offline)
                const pagination = document.querySelector('nav[role="navigation"]');
                if (pagination) pagination.style.display = 'none';

            } catch (e) {
                console.warn('Could not render offline products:', e);
            }
        }
    };
})();
