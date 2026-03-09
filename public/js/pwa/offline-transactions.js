// Offline Transaction, Bon, and Receipt rendering
(function () {
    'use strict';

    window.OfflineTransactions = {
        /**
         * Cache transactions from server into IndexedDB.
         * Call this on page load when online.
         */
        async cacheFromServer() {
            const isOnline = window.connectivityMonitor
                ? window.connectivityMonitor.isOnline
                : navigator.onLine;

            if (!isOnline || !window.posDB) return;

            try {
                const url = (typeof routes !== 'undefined' && routes.syncTransactions)
                    ? routes.syncTransactions
                    : '/api/pos/transactions';

                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                });

                if (response.ok) {
                    const transactions = await response.json();
                    await window.posDB.saveTransactionsCache(transactions);
                    await window.posDB.setMeta('transactions_cached_at', new Date().toISOString());
                }
            } catch (e) {
                console.warn('Could not cache transactions:', e);
            }
        },

        /**
         * Render offline transaction list replacing the server-rendered table.
         */
        async renderTransactionList() {
            const isOnline = window.connectivityMonitor
                ? window.connectivityMonitor.isOnline
                : navigator.onLine;

            if (isOnline) return;

            try {
                const transactions = await window.posDB.getTransactionsCache();
                if (!transactions || transactions.length === 0) return;

                // Sort by created_at desc
                transactions.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

                const notice = document.getElementById('offline-tx-notice');
                if (notice) notice.classList.remove('hidden');

                // Update cached time
                const cachedAt = await window.posDB.getMeta('transactions_cached_at');
                const timeEl = document.getElementById('offline-tx-time');
                if (timeEl && cachedAt) {
                    timeEl.textContent = new Date(cachedAt).toLocaleString('id-ID');
                }

                const tbody = document.querySelector('table tbody');
                if (!tbody) return;

                // Hide filters (not functional offline)
                const filterForm = document.querySelector('form[method="GET"]');
                if (filterForm) filterForm.closest('.mb-6').style.display = 'none';

                tbody.innerHTML = transactions.map(t => {
                    const date = new Date(t.created_at);
                    const statusBadge = this._statusBadge(t.status);
                    const paymentLabel = this._paymentLabel(t.payment_method);
                    const bonBadge = t.payment_method === 'bon'
                        ? (t.bon_paid_at
                            ? '<span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Lunas</span>'
                            : '<span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Belum Lunas</span>')
                        : '';

                    return `<tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${t.transaction_number}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${t.user_name}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">Rp ${new Intl.NumberFormat('id-ID').format(t.total)}</div>
                            <div class="text-xs text-gray-500">${t.items_count} item</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${paymentLabel}</div>
                            ${bonBadge}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">${statusBadge}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">${date.toLocaleDateString('id-ID')}</div>
                            <div class="text-xs text-gray-500">${date.toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'})}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="OfflineTransactions.showDetail(${t.id})" class="text-indigo-600 hover:text-indigo-900">Detail</button>
                        </td>
                    </tr>`;
                }).join('');

                // Hide pagination
                const pagination = document.querySelector('nav[role="navigation"]');
                if (pagination) pagination.style.display = 'none';

                // Update summary stats
                const statsContainer = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-3.gap-4');
                if (statsContainer) {
                    const completed = transactions.filter(t => t.status === 'completed');
                    const totalSales = completed.reduce((sum, t) => sum + parseFloat(t.total), 0);
                    const avg = completed.length > 0 ? totalSales / completed.length : 0;

                    statsContainer.innerHTML = `
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="text-sm text-blue-600">Total Transaksi (cached)</div>
                            <div class="text-lg font-semibold text-blue-900">${transactions.length}</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="text-sm text-green-600">Total Penjualan</div>
                            <div class="text-lg font-semibold text-green-900">Rp ${new Intl.NumberFormat('id-ID').format(totalSales)}</div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="text-sm text-purple-600">Rata-rata Transaksi</div>
                            <div class="text-lg font-semibold text-purple-900">Rp ${new Intl.NumberFormat('id-ID').format(avg)}</div>
                        </div>
                    `;
                }
            } catch (e) {
                console.warn('Could not render offline transactions:', e);
            }
        },

        /**
         * Render offline bon list.
         */
        async renderBonList() {
            const isOnline = window.connectivityMonitor
                ? window.connectivityMonitor.isOnline
                : navigator.onLine;

            if (isOnline) return;

            try {
                const bonTx = await window.posDB.getBonTransactions();
                if (!bonTx || bonTx.length === 0) return;

                bonTx.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

                const notice = document.getElementById('offline-bon-notice');
                if (notice) notice.classList.remove('hidden');

                const cachedAt = await window.posDB.getMeta('transactions_cached_at');
                const timeEl = document.getElementById('offline-bon-time');
                if (timeEl && cachedAt) {
                    timeEl.textContent = new Date(cachedAt).toLocaleString('id-ID');
                }

                // Update summary cards
                const unpaid = bonTx.filter(t => !t.bon_paid_at);
                const paid = bonTx.filter(t => t.bon_paid_at);
                const totalUnpaid = unpaid.reduce((s, t) => s + parseFloat(t.total), 0);
                const totalPaid = paid.reduce((s, t) => s + parseFloat(t.total), 0);

                const summaryCards = document.querySelector('.grid.grid-cols-1.sm\\:grid-cols-3');
                if (summaryCards) {
                    summaryCards.innerHTML = `
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3 sm:p-4">
                            <div class="text-xs sm:text-sm text-red-600 font-medium">Total Belum Lunas</div>
                            <div class="text-lg sm:text-2xl font-bold text-red-700">Rp ${new Intl.NumberFormat('id-ID').format(totalUnpaid)}</div>
                            <div class="text-xs text-red-500">${unpaid.length} transaksi</div>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4">
                            <div class="text-xs sm:text-sm text-green-600 font-medium">Total Sudah Lunas</div>
                            <div class="text-lg sm:text-2xl font-bold text-green-700">Rp ${new Intl.NumberFormat('id-ID').format(totalPaid)}</div>
                            <div class="text-xs text-green-500">${paid.length} transaksi</div>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
                            <div class="text-xs sm:text-sm text-blue-600 font-medium">Total Semua Bon</div>
                            <div class="text-lg sm:text-2xl font-bold text-blue-700">Rp ${new Intl.NumberFormat('id-ID').format(totalUnpaid + totalPaid)}</div>
                            <div class="text-xs text-blue-500">${bonTx.length} transaksi</div>
                        </div>
                    `;
                }

                // Hide filters
                const filterForm = document.querySelector('form[method="GET"]');
                if (filterForm) filterForm.closest('.mb-6').style.display = 'none';

                // Desktop table
                const tbody = document.querySelector('.hidden.md\\:block table tbody');
                if (tbody) {
                    tbody.innerHTML = bonTx.map(t => {
                        const date = new Date(t.created_at);
                        const customerName = t.customer_info?.name || '-';
                        const customerPhone = t.customer_info?.phone || '';
                        const isPaid = !!t.bon_paid_at;

                        return `<tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-mono text-blue-600">${t.transaction_number}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium">${customerName}</div>
                                <div class="text-xs text-gray-500">${customerPhone}</div>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium">Rp ${new Intl.NumberFormat('id-ID').format(t.total)}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">${date.toLocaleDateString('id-ID')} ${date.toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'})}</td>
                            <td class="px-4 py-3 text-sm">
                                ${isPaid
                                    ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">LUNAS</span>'
                                    : '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">BELUM LUNAS</span>'
                                }
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">${t.user_name}</td>
                            <td class="px-4 py-3 text-sm">
                                <button onclick="OfflineTransactions.showDetail(${t.id})" class="text-blue-600 hover:text-blue-800 text-xs">Detail</button>
                            </td>
                        </tr>`;
                    }).join('');
                }

                // Mobile cards
                const mobileContainer = document.querySelector('.md\\:hidden.space-y-3');
                if (mobileContainer) {
                    mobileContainer.innerHTML = bonTx.map(t => {
                        const date = new Date(t.created_at);
                        const customerName = t.customer_info?.name || '-';
                        const customerPhone = t.customer_info?.phone || '';
                        const isPaid = !!t.bon_paid_at;

                        return `<div class="border border-gray-200 rounded-lg p-4 ${isPaid ? 'bg-green-50/30' : 'bg-white'}">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-mono text-blue-600">${t.transaction_number}</span>
                                ${isPaid
                                    ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">LUNAS</span>'
                                    : '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">BELUM LUNAS</span>'
                                }
                            </div>
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 min-w-0 mr-3">
                                    <div class="font-medium text-sm text-gray-900 truncate">${customerName}</div>
                                    ${customerPhone ? `<div class="text-xs text-gray-500">${customerPhone}</div>` : ''}
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="text-sm font-bold text-gray-900">Rp ${new Intl.NumberFormat('id-ID').format(t.total)}</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                                <span><i class="fas fa-calendar mr-1"></i>${date.toLocaleDateString('id-ID')} ${date.toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'})}</span>
                                <span><i class="fas fa-user mr-1"></i>${t.user_name}</span>
                            </div>
                            <div class="flex items-center space-x-2 pt-2 border-t border-gray-100">
                                <button onclick="OfflineTransactions.showDetail(${t.id})" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium py-2 px-3 rounded transition-colors text-center">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </button>
                            </div>
                        </div>`;
                    }).join('');
                }

                // Hide pagination
                const pagination = document.querySelector('nav[role="navigation"]');
                if (pagination) pagination.style.display = 'none';

                // Hide pay modal (can't pay offline)
                const payModal = document.getElementById('pay-modal');
                if (payModal) payModal.remove();

            } catch (e) {
                console.warn('Could not render offline bon:', e);
            }
        },

        /**
         * Render offline transaction detail.
         */
        async renderTransactionDetail(transactionId) {
            const isOnline = window.connectivityMonitor
                ? window.connectivityMonitor.isOnline
                : navigator.onLine;

            if (isOnline) return;

            try {
                const t = await window.posDB.getTransactionById(transactionId);
                if (!t) return;

                const notice = document.getElementById('offline-detail-notice');
                if (notice) notice.classList.remove('hidden');

                // The page is already server-rendered and cached by SW,
                // so the content should be there. Just show offline notice.
            } catch (e) {
                console.warn('Could not render offline detail:', e);
            }
        },

        /**
         * Show transaction detail in a modal (used from offline list).
         */
        async showDetail(transactionId) {
            try {
                const t = await window.posDB.getTransactionById(transactionId);
                if (!t) {
                    alert('Detail transaksi tidak tersedia offline.');
                    return;
                }

                const date = new Date(t.created_at);
                const paymentLabel = this._paymentLabel(t.payment_method);

                const itemsHtml = t.items.map(item => `
                    <tr>
                        <td class="px-4 py-2 text-sm">${item.product_name}</td>
                        <td class="px-4 py-2 text-sm text-right">Rp ${new Intl.NumberFormat('id-ID').format(item.product_price)}</td>
                        <td class="px-4 py-2 text-sm text-center">${item.quantity}</td>
                        <td class="px-4 py-2 text-sm text-right font-medium">Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                    </tr>
                `).join('');

                let bonInfo = '';
                if (t.payment_method === 'bon') {
                    const isPaid = !!t.bon_paid_at;
                    bonInfo = `
                        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                            <h4 class="text-sm font-medium mb-2"><i class="fas fa-file-invoice-dollar mr-1"></i> Bon/Hutang</h4>
                            <p class="text-sm">Status: ${isPaid
                                ? '<span class="text-green-700 font-medium">LUNAS</span>'
                                : '<span class="text-red-700 font-medium">BELUM LUNAS</span>'
                            }</p>
                            ${t.customer_info ? `<p class="text-sm">Pelanggan: ${t.customer_info.name || '-'}</p>
                            <p class="text-sm">Telepon: ${t.customer_info.phone || '-'}</p>` : ''}
                        </div>
                    `;
                }

                // Remove existing modal
                const existing = document.getElementById('offline-detail-modal');
                if (existing) existing.remove();

                const modal = document.createElement('div');
                modal.id = 'offline-detail-modal';
                modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                modal.innerHTML = `
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-bold">Detail Transaksi (Offline)</h3>
                                <button onclick="document.getElementById('offline-detail-modal').remove()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <div class="text-xs text-gray-500">No. Transaksi</div>
                                    <div class="text-sm font-semibold">${t.transaction_number}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Status</div>
                                    <div class="text-sm">${this._statusBadge(t.status)}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Total</div>
                                    <div class="text-sm font-bold">Rp ${new Intl.NumberFormat('id-ID').format(t.total)}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Tanggal</div>
                                    <div class="text-sm">${date.toLocaleDateString('id-ID')} ${date.toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'})}</div>
                                </div>
                            </div>

                            <table class="min-w-full divide-y divide-gray-200 mb-4">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Harga</th>
                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">${itemsHtml}</tbody>
                            </table>

                            <div class="border-t pt-3 space-y-1">
                                <div class="flex justify-between text-sm">
                                    <span>Subtotal</span>
                                    <span>Rp ${new Intl.NumberFormat('id-ID').format(t.subtotal)}</span>
                                </div>
                                ${t.discount > 0 ? `<div class="flex justify-between text-sm text-red-600"><span>Diskon</span><span>-Rp ${new Intl.NumberFormat('id-ID').format(t.discount)}</span></div>` : ''}
                                ${t.tax > 0 ? `<div class="flex justify-between text-sm"><span>Pajak</span><span>Rp ${new Intl.NumberFormat('id-ID').format(t.tax)}</span></div>` : ''}
                                <div class="flex justify-between font-bold border-t pt-2">
                                    <span>Total</span>
                                    <span>Rp ${new Intl.NumberFormat('id-ID').format(t.total)}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Metode Bayar</span>
                                    <span>${paymentLabel}</span>
                                </div>
                                ${t.payment_method !== 'bon' ? `<div class="flex justify-between text-sm"><span>Bayar</span><span>Rp ${new Intl.NumberFormat('id-ID').format(t.paid)}</span></div>` : ''}
                                ${t.change > 0 ? `<div class="flex justify-between text-sm text-green-600"><span>Kembalian</span><span>Rp ${new Intl.NumberFormat('id-ID').format(t.change)}</span></div>` : ''}
                            </div>

                            ${bonInfo}

                            <div class="mt-4 text-xs text-gray-400">
                                Kasir: ${t.user_name} &bull; ${date.toLocaleString('id-ID')}
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);

                // Close on backdrop click
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) modal.remove();
                });

            } catch (e) {
                console.warn('Could not show detail:', e);
                alert('Gagal memuat detail transaksi.');
            }
        },

        /**
         * Render offline receipt from IndexedDB.
         */
        async renderOfflineReceipt(transactionId) {
            const isOnline = window.connectivityMonitor
                ? window.connectivityMonitor.isOnline
                : navigator.onLine;

            if (isOnline) return; // Online — use server-rendered

            try {
                const t = await window.posDB.getTransactionById(transactionId);
                if (!t) return;

                // Receipt page is standalone, replace body content
                const date = new Date(t.created_at);
                const paymentLabel = this._paymentLabel(t.payment_method);

                const itemsHtml = t.items.map(item => `
                    <div class="item">
                        <div class="item-name">${item.product_name}</div>
                        <div class="item-details">
                            <span>${item.quantity} x Rp ${new Intl.NumberFormat('id-ID').format(item.product_price)}</span>
                            <span>Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</span>
                        </div>
                    </div>
                `).join('');

                document.body.innerHTML = `
                    <div class="header">
                        <div class="store-name">Struk Offline</div>
                        <div style="font-size:10px; color:#666;">Data dari cache lokal</div>
                    </div>
                    <div class="transaction-info">
                        <div><strong>No. Transaksi:</strong> ${t.transaction_number}</div>
                        <div><strong>Kasir:</strong> ${t.user_name}</div>
                        <div><strong>Tanggal:</strong> ${date.toLocaleString('id-ID')}</div>
                    </div>
                    <div class="items">${itemsHtml}</div>
                    <div class="totals">
                        <div class="total-row"><span>Subtotal:</span><span>Rp ${new Intl.NumberFormat('id-ID').format(t.subtotal)}</span></div>
                        ${t.discount > 0 ? `<div class="total-row"><span>Diskon:</span><span>- Rp ${new Intl.NumberFormat('id-ID').format(t.discount)}</span></div>` : ''}
                        ${t.tax > 0 ? `<div class="total-row"><span>Pajak:</span><span>Rp ${new Intl.NumberFormat('id-ID').format(t.tax)}</span></div>` : ''}
                        <div class="total-row total-final"><span>TOTAL:</span><span>Rp ${new Intl.NumberFormat('id-ID').format(t.total)}</span></div>
                    </div>
                    <div class="payment-info">
                        <div class="total-row"><span>Metode Bayar:</span><span>${paymentLabel}</span></div>
                        ${t.payment_method === 'bon'
                            ? `<div class="total-row" style="font-weight:bold;text-align:center;margin:8px 0;padding:4px;border:1px dashed #000;">
                                <span style="width:100%;text-align:center;">STATUS: ${t.bon_paid_at ? 'LUNAS' : 'BELUM LUNAS'}</span>
                              </div>
                              ${t.customer_info ? `<div style="margin:5px 0;font-size:11px;">
                                <div><strong>Pelanggan:</strong> ${t.customer_info.name || '-'}</div>
                                <div><strong>Telepon:</strong> ${t.customer_info.phone || '-'}</div>
                              </div>` : ''}`
                            : `<div class="total-row"><span>Bayar:</span><span>Rp ${new Intl.NumberFormat('id-ID').format(t.paid)}</span></div>
                               ${t.change > 0 ? `<div class="total-row"><span>Kembalian:</span><span>Rp ${new Intl.NumberFormat('id-ID').format(t.change)}</span></div>` : ''}`
                        }
                    </div>
                    <div class="footer">
                        <div style="color:#c00; margin-bottom:5px;">[Struk Offline - Data dari cache]</div>
                        <div>${date.toLocaleString('id-ID')}</div>
                    </div>
                `;
            } catch (e) {
                console.warn('Could not render offline receipt:', e);
            }
        },

        // ─── Helpers ───

        _statusBadge(status) {
            const map = {
                completed: '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Selesai</span>',
                pending: '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>',
                cancelled: '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Batal</span>',
                failed: '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Gagal</span>',
                refunded: '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Refund</span>',
            };
            return map[status] || `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">${status}</span>`;
        },

        _paymentLabel(method) {
            const map = { cash: 'Tunai', card: 'Kartu', ewallet: 'E-Wallet', bon: 'Bon/Hutang', online: 'Online/Transfer' };
            return map[method] || method;
        }
    };
})();
