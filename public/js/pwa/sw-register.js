// Service Worker Registration + Connectivity Monitor
(function () {
    'use strict';

    // Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', async () => {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js');
                console.log('SW registered:', registration.scope);

                // Check for updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            showUpdateBanner();
                        }
                    });
                });
            } catch (e) {
                console.warn('SW registration failed:', e);
            }
        });
    }

    // Connectivity Monitor
    class ConnectivityMonitor {
        constructor() {
            this.isOnline = navigator.onLine;
            this._listeners = [];
            this._heartbeatInterval = null;

            window.addEventListener('online', () => this.check());
            window.addEventListener('offline', () => this._setOffline());

            // Start heartbeat
            this._heartbeatInterval = setInterval(() => this.check(), 30000);

            // Initial check
            if (this.isOnline) {
                this.check();
            } else {
                this._updateUI();
            }
        }

        async check() {
            try {
                const resp = await fetch('/api/health', {
                    method: 'HEAD',
                    cache: 'no-store',
                    signal: AbortSignal.timeout(5000)
                });
                if (resp.ok) {
                    this._setOnline();
                } else {
                    this._setOffline();
                }
            } catch {
                this._setOffline();
            }
        }

        _setOnline() {
            if (!this.isOnline) {
                this.isOnline = true;
                this._updateUI();
                window.dispatchEvent(new CustomEvent('app-online'));
            }
        }

        _setOffline() {
            if (this.isOnline) {
                this.isOnline = false;
                this._updateUI();
                window.dispatchEvent(new CustomEvent('app-offline'));
            }
        }

        _updateUI() {
            const offlineBanner = document.getElementById('offline-banner');
            if (offlineBanner) {
                if (this.isOnline) {
                    offlineBanner.classList.add('hidden');
                    offlineBanner.classList.remove('translate-y-0');
                    offlineBanner.classList.add('-translate-y-full');
                } else {
                    offlineBanner.classList.remove('hidden');
                    offlineBanner.classList.remove('-translate-y-full');
                    offlineBanner.classList.add('translate-y-0');
                }
            }

            // Update body class for CSS hooks
            document.body.classList.toggle('is-offline', !this.isOnline);
            document.body.classList.toggle('is-online', this.isOnline);
        }
    }

    // Show update banner
    function showUpdateBanner() {
        const banner = document.getElementById('sw-update-banner');
        if (banner) {
            banner.classList.remove('hidden');
        }
    }

    // Update pending sync badge
    window.updatePendingSyncBadge = async function () {
        const badge = document.getElementById('pending-sync-count');
        if (!badge) return;

        try {
            if (window.posDB) {
                const txCount = await window.posDB.getPendingCount();
                const productCount = await window.posDB.getPendingProductActionCount();
                const count = txCount + productCount;
                if (count > 0) {
                    badge.textContent = count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        } catch {
            badge.classList.add('hidden');
        }
    };

    // Show/hide sync banner
    window.showSyncBanner = function () {
        const banner = document.getElementById('sync-banner');
        if (banner) banner.classList.remove('hidden');
    };

    window.hideSyncBanner = function () {
        const banner = document.getElementById('sync-banner');
        if (banner) banner.classList.add('hidden');
    };

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        window.connectivityMonitor = new ConnectivityMonitor();

        // Update badge on load
        setTimeout(() => {
            if (typeof window.updatePendingSyncBadge === 'function') {
                window.updatePendingSyncBadge();
            }
        }, 1000);
    }

    // Reload on SW update click
    window.reloadForUpdate = function () {
        if (navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage('skipWaiting');
        }
        window.location.reload();
    };
})();
