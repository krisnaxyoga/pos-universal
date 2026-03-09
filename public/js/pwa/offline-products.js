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
         * Replaces the table body with IndexedDB data.
         */
        async renderOfflineList() {
            const isOnline = window.connectivityMonitor
                ? window.connectivityMonitor.isOnline
                : navigator.onLine;

            if (isOnline) return; // Online — use server-rendered content

            try {
                const products = await window.posDB.getProducts();
                if (!products || products.length === 0) return;

                const tbody = document.querySelector('table tbody');
                if (!tbody) return;

                // Show offline notice
                const notice = document.getElementById('offline-product-notice');
                if (notice) notice.classList.remove('hidden');

                // Build table rows from IndexedDB
                tbody.innerHTML = products.map(p => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center">
                                ${p.image
                                    ? `<img src="/${p.image}" class="w-10 h-10 rounded object-cover mr-3" onerror="this.style.display='none'">`
                                    : `<div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center mr-3"><i class="fas fa-box text-gray-400"></i></div>`
                                }
                                <div>
                                    <div class="font-medium text-gray-900">${p.name}</div>
                                    <div class="text-xs text-gray-500">${p.sku || '-'}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">${p.category_name || '-'}</td>
                        <td class="px-4 py-3 text-sm font-medium">Rp ${new Intl.NumberFormat('id-ID').format(p.price)}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="${p.stock <= (p.min_stock || 5) ? 'text-red-600 font-bold' : 'text-gray-700'}">${p.stock}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-medium ${p.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${p.is_active ? 'Aktif' : 'Nonaktif'}
                            </span>
                            ${p._offline_new ? '<span class="ml-1 px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Offline</span>' : ''}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            <span class="italic text-xs">Offline mode</span>
                        </td>
                    </tr>
                `).join('');

                // Hide pagination (not relevant offline)
                const pagination = document.querySelector('nav[role="navigation"]');
                if (pagination) pagination.style.display = 'none';

            } catch (e) {
                console.warn('Could not render offline products:', e);
            }
        }
    };
})();
