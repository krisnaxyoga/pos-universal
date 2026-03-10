const CACHE_VERSION = 'v5';
const SHELL_CACHE = `pos-shell-${CACHE_VERSION}`;
const CDN_CACHE = `pos-cdn-${CACHE_VERSION}`;
const IMAGE_CACHE = `pos-images-${CACHE_VERSION}`;
const PAGE_CACHE = `pos-pages-${CACHE_VERSION}`;

const CDN_URLS = [
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js',
];

const PWA_SCRIPTS = [
    '/js/pwa/idb-helper.js',
    '/js/pwa/offline-products.js',
    '/js/pwa/offline-sync.js',
    '/js/pwa/offline-transactions.js',
    '/js/pwa/sw-register.js',
];

// Install: precache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        (async () => {
            // Cache Vite build assets + PWA scripts
            try {
                const manifestResponse = await fetch('/build/manifest.json');
                const manifest = await manifestResponse.json();
                const assetUrls = Object.values(manifest)
                    .filter(entry => entry.file)
                    .map(entry => `/build/${entry.file}`);

                const shellCache = await caches.open(SHELL_CACHE);
                await shellCache.addAll(['/offline', ...assetUrls, ...PWA_SCRIPTS]);
            } catch (e) {
                console.warn('SW: Could not precache build assets:', e);
                const shellCache = await caches.open(SHELL_CACHE);
                try {
                    await shellCache.addAll(['/offline', ...PWA_SCRIPTS]);
                } catch (e2) {
                    await shellCache.addAll(['/offline']);
                }
            }

            // Cache CDN assets
            try {
                const cdnCache = await caches.open(CDN_CACHE);
                await cdnCache.addAll(CDN_URLS);
            } catch (e) {
                console.warn('SW: Could not precache CDN assets:', e);
            }

            self.skipWaiting();
        })()
    );
});

// Activate: clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(key => !key.endsWith(CACHE_VERSION))
                    .map(key => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

// Fetch handler
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Skip non-GET requests (POSTs handled by offline-sync.js)
    if (event.request.method !== 'GET') return;

    // Skip chrome-extension and other non-http(s)
    if (!url.protocol.startsWith('http')) return;

    // Vite build assets: Cache-First (content-hashed filenames)
    if (url.pathname.startsWith('/build/assets/')) {
        event.respondWith(cacheFirst(event.request, SHELL_CACHE));
        return;
    }

    // PWA scripts: Cache-First (precached during install)
    if (url.pathname.startsWith('/js/pwa/')) {
        event.respondWith(cacheFirst(event.request, SHELL_CACHE));
        return;
    }

    // CDN resources: Cache-First
    if (url.hostname !== location.hostname) {
        event.respondWith(cacheFirst(event.request, CDN_CACHE));
        return;
    }

    // Product images & logo images: Cache-First
    if (url.pathname.startsWith('/images/')) {
        event.respondWith(cacheFirst(event.request, IMAGE_CACHE));
        return;
    }

    // Key app pages: Network-First (cache on visit for offline)
    if (url.pathname === '/pos' || url.pathname === '/dashboard' ||
        url.pathname === '/products' || url.pathname === '/products/create' ||
        url.pathname.match(/^\/products\/\d+\/edit$/) ||
        url.pathname === '/transactions' || url.pathname.match(/^\/transactions\/\d+$/) ||
        url.pathname === '/bon' || url.pathname.match(/^\/pos\/receipt\/\d+$/)) {
        event.respondWith(networkFirst(event.request, PAGE_CACHE));
        return;
    }

    // Offline fallback page: Cache-First
    if (url.pathname === '/offline') {
        event.respondWith(cacheFirst(event.request, SHELL_CACHE));
        return;
    }

    // API/JSON requests: Network-Only
    if (url.pathname.startsWith('/api/') || event.request.headers.get('Accept')?.includes('application/json')) {
        return; // Let browser handle normally
    }

    // Everything else: Network-First with caching
    event.respondWith(networkFirstWithCache(event.request));
});

// Cache-First strategy
async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch (e) {
        return new Response('Offline', { status: 503 });
    }
}

// Network-First strategy
async function networkFirst(request, cacheName) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch (e) {
        const cached = await caches.match(request);
        if (cached) return cached;
        return caches.match('/offline');
    }
}

// Network-First with caching (for misc resources)
async function networkFirstWithCache(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(SHELL_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (e) {
        const cached = await caches.match(request);
        if (cached) return cached;
        return caches.match('/offline');
    }
}

// Listen for messages from client
self.addEventListener('message', (event) => {
    if (event.data === 'skipWaiting') {
        self.skipWaiting();
    }
});
