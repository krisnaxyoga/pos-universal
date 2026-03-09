// IndexedDB Helper for POS Offline
(function () {
    'use strict';

    const DB_NAME = 'pos_offline_db';
    const DB_VERSION = 3;

    class PosDB {
        constructor() {
            this.db = null;
        }

        open() {
            return new Promise((resolve, reject) => {
                if (this.db) { resolve(this.db); return; }

                const request = indexedDB.open(DB_NAME, DB_VERSION);

                request.onupgradeneeded = (event) => {
                    const db = event.target.result;

                    // Products store
                    if (!db.objectStoreNames.contains('products')) {
                        const productStore = db.createObjectStore('products', { keyPath: 'id' });
                        productStore.createIndex('barcode', 'barcode', { unique: false });
                        productStore.createIndex('name', 'name', { unique: false });
                        productStore.createIndex('category_id', 'category_id', { unique: false });
                    }

                    // Offline transactions store
                    if (!db.objectStoreNames.contains('offline_transactions')) {
                        const txStore = db.createObjectStore('offline_transactions', { keyPath: 'offline_id' });
                        txStore.createIndex('status', 'status', { unique: false });
                        txStore.createIndex('created_at', 'created_at', { unique: false });
                    }

                    // App meta store
                    if (!db.objectStoreNames.contains('app_meta')) {
                        db.createObjectStore('app_meta', { keyPath: 'key' });
                    }

                    // Offline product actions store (v2)
                    if (!db.objectStoreNames.contains('offline_product_actions')) {
                        const paStore = db.createObjectStore('offline_product_actions', { keyPath: 'action_id' });
                        paStore.createIndex('status', 'status', { unique: false });
                        paStore.createIndex('action_type', 'action_type', { unique: false });
                    }

                    // Transaction history cache store (v3)
                    if (!db.objectStoreNames.contains('transactions_cache')) {
                        const txCacheStore = db.createObjectStore('transactions_cache', { keyPath: 'id' });
                        txCacheStore.createIndex('payment_method', 'payment_method', { unique: false });
                        txCacheStore.createIndex('status', 'status', { unique: false });
                        txCacheStore.createIndex('created_at', 'created_at', { unique: false });
                    }
                };

                request.onsuccess = (event) => {
                    this.db = event.target.result;
                    resolve(this.db);
                };

                request.onerror = (event) => {
                    console.error('IndexedDB open error:', event.target.error);
                    reject(event.target.error);
                };
            });
        }

        // ─── Products ───

        async saveProducts(products) {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('products', 'readwrite');
                const store = tx.objectStore('products');

                store.clear();
                const now = new Date().toISOString();
                products.forEach(p => {
                    store.put({
                        id: p.id,
                        name: p.name,
                        sku: p.sku || '',
                        barcode: p.barcode || '',
                        description: p.description || '',
                        price: parseFloat(p.price) || 0,
                        cost: parseFloat(p.cost) || 0,
                        stock: parseInt(p.stock) || 0,
                        min_stock: parseInt(p.min_stock) || 0,
                        category_id: p.category_id,
                        category_name: p.category?.name || p.category_name || '',
                        image: p.image || '',
                        is_active: p.is_active !== false,
                        synced_at: now
                    });
                });

                tx.oncomplete = () => resolve();
                tx.onerror = (e) => reject(e.target.error);
            });
        }

        async getProducts() {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('products', 'readonly');
                const store = tx.objectStore('products');
                const request = store.getAll();
                request.onsuccess = () => resolve(request.result);
                request.onerror = (e) => reject(e.target.error);
            });
        }

        async getProduct(id) {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('products', 'readonly');
                const store = tx.objectStore('products');
                const request = store.get(id);
                request.onsuccess = () => resolve(request.result);
                request.onerror = (e) => reject(e.target.error);
            });
        }

        async putProduct(product) {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('products', 'readwrite');
                const store = tx.objectStore('products');
                store.put(product);
                tx.oncomplete = () => resolve();
                tx.onerror = (e) => reject(e.target.error);
            });
        }

        async removeProduct(id) {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('products', 'readwrite');
                const store = tx.objectStore('products');
                store.delete(id);
                tx.oncomplete = () => resolve();
                tx.onerror = (e) => reject(e.target.error);
            });
        }

        async decrementLocalStock(productId, quantity) {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('products', 'readwrite');
                const store = tx.objectStore('products');
                const request = store.get(productId);
                request.onsuccess = () => {
                    const product = request.result;
                    if (product) {
                        product.stock = Math.max(0, product.stock - quantity);
                        store.put(product);
                    }
                    resolve();
                };
                request.onerror = (e) => reject(e.target.error);
            });
        }

        // ─── Offline Transactions ───

        async queueTransaction(transactionData) {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('offline_transactions', 'readwrite');
                const store = tx.objectStore('offline_transactions');
                store.put(transactionData);
                tx.oncomplete = () => resolve();
                tx.onerror = (e) => reject(e.target.error);
            });
        }

        async getPendingTransactions() {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('offline_transactions', 'readonly');
                const store = tx.objectStore('offline_transactions');
                const index = store.index('status');
                const request = index.getAll('pending');
                request.onsuccess = () => resolve(request.result);
                request.onerror = (e) => reject(e.target.error);
            });
        }

        async getAllTransactions() {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('offline_transactions', 'readonly');
                const store = tx.objectStore('offline_transactions');
                const request = store.getAll();
                request.onsuccess = () => resolve(request.result);
                request.onerror = (e) => reject(e.target.error);
            });
        }

        async markTransactionSynced(offlineId, serverTransactionId) {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('offline_transactions', 'readwrite');
                const store = tx.objectStore('offline_transactions');
                const request = store.get(offlineId);
                request.onsuccess = () => {
                    const data = request.result;
                    if (data) {
                        data.status = 'synced';
                        data.synced_at = new Date().toISOString();
                        data.server_transaction_id = serverTransactionId;
                        store.put(data);
                    }
                    resolve();
                };
                request.onerror = (e) => reject(e.target.error);
            });
        }

        async markTransactionFailed(offlineId, errorMessage) {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('offline_transactions', 'readwrite');
                const store = tx.objectStore('offline_transactions');
                const request = store.get(offlineId);
                request.onsuccess = () => {
                    const data = request.result;
                    if (data) {
                        data.status = 'failed';
                        data.error_message = errorMessage;
                        data.retry_count = (data.retry_count || 0) + 1;
                        store.put(data);
                    }
                    resolve();
                };
                request.onerror = (e) => reject(e.target.error);
            });
        }

        async getPendingCount() {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('offline_transactions', 'readonly');
                const store = tx.objectStore('offline_transactions');
                const index = store.index('status');
                const request = index.count('pending');
                request.onsuccess = () => resolve(request.result);
                request.onerror = (e) => reject(e.target.error);
            });
        }

        async deleteTransaction(offlineId) {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('offline_transactions', 'readwrite');
                const store = tx.objectStore('offline_transactions');
                store.delete(offlineId);
                tx.oncomplete = () => resolve();
                tx.onerror = (e) => reject(e.target.error);
            });
        }

        // ─── Offline Product Actions ───

        async queueProductAction(action) {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('offline_product_actions', 'readwrite');
                const store = tx.objectStore('offline_product_actions');
                store.put(action);
                tx.oncomplete = () => resolve();
                tx.onerror = (e) => reject(e.target.error);
            });
        }

        async getPendingProductActions() {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('offline_product_actions', 'readonly');
                const store = tx.objectStore('offline_product_actions');
                const index = store.index('status');
                const request = index.getAll('pending');
                request.onsuccess = () => resolve(request.result);
                request.onerror = (e) => reject(e.target.error);
            });
        }

        async markProductActionSynced(actionId) {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('offline_product_actions', 'readwrite');
                const store = tx.objectStore('offline_product_actions');
                const request = store.get(actionId);
                request.onsuccess = () => {
                    const data = request.result;
                    if (data) {
                        data.status = 'synced';
                        data.synced_at = new Date().toISOString();
                        store.put(data);
                    }
                    resolve();
                };
                request.onerror = (e) => reject(e.target.error);
            });
        }

        async markProductActionFailed(actionId, errorMessage) {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('offline_product_actions', 'readwrite');
                const store = tx.objectStore('offline_product_actions');
                const request = store.get(actionId);
                request.onsuccess = () => {
                    const data = request.result;
                    if (data) {
                        data.status = 'failed';
                        data.error_message = errorMessage;
                        store.put(data);
                    }
                    resolve();
                };
                request.onerror = (e) => reject(e.target.error);
            });
        }

        async getPendingProductActionCount() {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('offline_product_actions', 'readonly');
                const store = tx.objectStore('offline_product_actions');
                const index = store.index('status');
                const request = index.count('pending');
                request.onsuccess = () => resolve(request.result);
                request.onerror = (e) => reject(e.target.error);
            });
        }

        // ─── Transactions Cache ───

        async saveTransactionsCache(transactions) {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('transactions_cache', 'readwrite');
                const store = tx.objectStore('transactions_cache');
                store.clear();
                transactions.forEach(t => store.put(t));
                tx.oncomplete = () => resolve();
                tx.onerror = (e) => reject(e.target.error);
            });
        }

        async getTransactionsCache() {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('transactions_cache', 'readonly');
                const store = tx.objectStore('transactions_cache');
                const request = store.getAll();
                request.onsuccess = () => resolve(request.result);
                request.onerror = (e) => reject(e.target.error);
            });
        }

        async getTransactionById(id) {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('transactions_cache', 'readonly');
                const store = tx.objectStore('transactions_cache');
                const request = store.get(id);
                request.onsuccess = () => resolve(request.result);
                request.onerror = (e) => reject(e.target.error);
            });
        }

        async getBonTransactions() {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('transactions_cache', 'readonly');
                const store = tx.objectStore('transactions_cache');
                const index = store.index('payment_method');
                const request = index.getAll('bon');
                request.onsuccess = () => resolve(request.result);
                request.onerror = (e) => reject(e.target.error);
            });
        }

        // ─── App Meta ───

        async setMeta(key, value) {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('app_meta', 'readwrite');
                const store = tx.objectStore('app_meta');
                store.put({ key, value });
                tx.oncomplete = () => resolve();
                tx.onerror = (e) => reject(e.target.error);
            });
        }

        async getMeta(key) {
            const db = await this.open();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('app_meta', 'readonly');
                const store = tx.objectStore('app_meta');
                const request = store.get(key);
                request.onsuccess = () => resolve(request.result?.value ?? null);
                request.onerror = (e) => reject(e.target.error);
            });
        }
    }

    // Export globally
    window.PosDB = PosDB;
    window.posDB = new PosDB();
    window.posDB.open().catch(e => console.warn('IndexedDB init failed:', e));
})();
