// Offline Sync Manager for POS
(function () {
    'use strict';

    class SyncManager {
        constructor() {
            this.isSyncing = false;

            // Listen for online event
            window.addEventListener('app-online', () => this.syncAll());

            // Also sync on page load if online
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    setTimeout(() => this.syncAll(), 2000);
                });
            }
        }

        async syncAll() {
            if (this.isSyncing) return;
            if (!window.posDB) return;

            const isOnline = window.connectivityMonitor
                ? window.connectivityMonitor.isOnline
                : navigator.onLine;
            if (!isOnline) return;

            let pending;
            let pendingProducts;
            try {
                pending = await window.posDB.getPendingTransactions();
                pendingProducts = await window.posDB.getPendingProductActions();
            } catch {
                return;
            }

            const hasPendingTx = pending && pending.length > 0;
            const hasPendingProducts = pendingProducts && pendingProducts.length > 0;
            if (!hasPendingTx && !hasPendingProducts) return;

            this.isSyncing = true;
            if (typeof window.showSyncBanner === 'function') window.showSyncBanner();

            try {
                // Get fresh CSRF token
                const csrfToken = await this.refreshCsrfToken();
                if (!csrfToken) {
                    this.showNotification('Sesi telah berakhir. Silakan login ulang. Transaksi offline Anda tetap aman.', 'warning');
                    return;
                }

                // Sync pending transactions
                if (hasPendingTx) {
                    let successCount = 0;
                    let failCount = 0;

                    for (const tx of pending) {
                        try {
                            const result = await this.syncSingle(tx, csrfToken);

                            if (result.success) {
                                await window.posDB.markTransactionSynced(
                                    tx.offline_id,
                                    result.transaction?.id
                                );
                                successCount++;
                            } else {
                                await window.posDB.markTransactionFailed(tx.offline_id, result.message || 'Unknown error');
                                failCount++;
                            }
                        } catch (error) {
                            console.error('Sync error for', tx.offline_id, error);
                            tx.retry_count = (tx.retry_count || 0) + 1;

                            if (tx.retry_count >= 5) {
                                await window.posDB.markTransactionFailed(
                                    tx.offline_id,
                                    'Gagal sync setelah 5 percobaan: ' + error.message
                                );
                                failCount++;
                            }
                            break; // Stop syncing, retry later
                        }
                    }

                    if (successCount > 0 || failCount > 0) {
                        let msg = '';
                        if (successCount > 0) msg += `${successCount} transaksi berhasil disinkronkan. `;
                        if (failCount > 0) msg += `${failCount} transaksi gagal.`;
                        this.showNotification(msg.trim(), failCount > 0 ? 'warning' : 'success');
                    }
                }

                // Sync product actions (create/update/delete)
                await this.syncProductActions(csrfToken);

                // Refresh products from server
                await this.refreshProducts();

                // Refresh transactions cache
                await this.refreshTransactionsCache();

            } finally {
                this.isSyncing = false;
                if (typeof window.hideSyncBanner === 'function') window.hideSyncBanner();
                if (typeof window.updatePendingSyncBadge === 'function') window.updatePendingSyncBadge();
            }
        }

        async syncSingle(tx, csrfToken) {
            const syncUrl = (typeof routes !== 'undefined' && routes.syncTransaction)
                ? routes.syncTransaction
                : '/api/pos/sync-transaction';

            const payload = {
                items: tx.items.map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity,
                })),
                subtotal: tx.subtotal,
                discount: tx.discount || 0,
                tax: tx.tax || 0,
                total: tx.total,
                paid: tx.paid,
                payment_method: tx.payment_method,
                customer_info: tx.customer_info,
                _offline_id: tx.offline_id,
                _offline_created_at: tx.created_at,
            };

            const response = await fetch(syncUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            if (response.status === 401 || response.status === 419) {
                throw new Error('Session expired');
            }

            return await response.json();
        }

        async syncProductActions(csrfToken) {
            let pending;
            try {
                pending = await window.posDB.getPendingProductActions();
            } catch {
                return;
            }

            if (!pending || pending.length === 0) return;

            const syncUrl = (typeof routes !== 'undefined' && routes.syncProduct)
                ? routes.syncProduct
                : '/api/pos/sync-product';

            let successCount = 0;
            let failCount = 0;

            for (const action of pending) {
                try {
                    const payload = {
                        action_type: action.action_type,
                        action_id: action.action_id,
                        product_id: action.product_id,
                        data: action.data,
                    };

                    const response = await fetch(syncUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    if (response.status === 401 || response.status === 419) {
                        throw new Error('Session expired');
                    }

                    const result = await response.json();

                    if (result.success) {
                        await window.posDB.markProductActionSynced(action.action_id);
                        successCount++;
                    } else {
                        await window.posDB.markProductActionFailed(action.action_id, result.message || 'Unknown error');
                        failCount++;
                    }
                } catch (error) {
                    console.error('Product sync error for', action.action_id, error);
                    await window.posDB.markProductActionFailed(action.action_id, error.message);
                    if (error.message === 'Session expired') break;
                    failCount++;
                }
            }

            if (successCount > 0 || failCount > 0) {
                let msg = '';
                if (successCount > 0) msg += `${successCount} produk berhasil disinkronkan. `;
                if (failCount > 0) msg += `${failCount} produk gagal sync.`;
                this.showNotification(msg.trim(), failCount > 0 ? 'warning' : 'success');
            }
        }

        async refreshCsrfToken() {
            try {
                const csrfUrl = (typeof routes !== 'undefined' && routes.csrfToken)
                    ? routes.csrfToken
                    : '/api/csrf-token';

                const response = await fetch(csrfUrl, {
                    headers: { 'Accept': 'application/json' },
                });

                if (response.status === 401) return null;
                if (!response.ok) return null;

                const data = await response.json();
                const token = data.token;

                // Update meta tag
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', token);

                // Update global variable
                if (typeof csrfToken !== 'undefined') {
                    window.csrfToken = token;
                    // Try to update the local scope variable too
                    try { csrfToken = token; } catch {}
                }

                return token;
            } catch (error) {
                console.error('Failed to refresh CSRF token:', error);
                return null;
            }
        }

        async refreshProducts() {
            try {
                const productsUrl = (typeof routes !== 'undefined' && routes.syncProducts)
                    ? routes.syncProducts
                    : '/api/pos/products';

                const response = await fetch(productsUrl, {
                    headers: { 'Accept': 'application/json' },
                });

                if (response.ok) {
                    const freshProducts = await response.json();
                    await window.posDB.saveProducts(freshProducts);

                    // Update in-memory products array if on POS page
                    if (typeof products !== 'undefined' && Array.isArray(products)) {
                        products.length = 0;
                        freshProducts.forEach(p => products.push(p));
                    }
                }
            } catch (e) {
                console.warn('Could not refresh products:', e);
            }
        }

        async refreshTransactionsCache() {
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
                console.warn('Could not refresh transactions cache:', e);
            }
        }

        showNotification(message, type = 'info') {
            const colors = {
                success: 'bg-green-500',
                warning: 'bg-yellow-500 text-yellow-900',
                error: 'bg-red-500',
                info: 'bg-blue-500',
            };

            const icons = {
                success: 'fa-check-circle',
                warning: 'fa-exclamation-triangle',
                error: 'fa-exclamation-circle',
                info: 'fa-info-circle',
            };

            const el = document.createElement('div');
            el.className = `fixed top-20 right-4 z-[101] ${colors[type] || colors.info} text-white px-4 py-3 rounded-lg shadow-lg max-w-sm transition-opacity`;
            el.innerHTML = `<i class="fas ${icons[type] || icons.info} mr-2"></i>${message}
                <button onclick="this.parentElement.remove()" class="ml-3 opacity-70 hover:opacity-100"><i class="fas fa-times"></i></button>`;
            document.body.appendChild(el);

            setTimeout(() => {
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            }, 6000);
        }
    }

    // Initialize
    window.SyncManager = SyncManager;
    window.syncManager = new SyncManager();
})();
