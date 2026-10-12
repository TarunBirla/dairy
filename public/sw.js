const CACHE_NAME = 'smartdairy-pwa-v1';
const PRECACHE_ASSETS = [
    '/offline.html',
    '/manifest.json',
    '/logo.PNG',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/icons/apple-touch-icon.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Only handle GET requests over http/https
    if (request.method !== 'GET' || !request.url.startsWith('http')) {
        return;
    }

    // Network-first for HTML page navigations (keeps Laravel sessions & CSRF fresh)
    if (request.mode === 'navigate' || (request.headers.get('accept') || '').includes('text/html')) {
        event.respondWith(
            fetch(request).catch(() => {
                return caches.match('/offline.html');
            })
        );
        return;
    }

    // Cache-first with background refresh for static assets (icons, images, fonts, scripts, styles)
    const isStaticAsset = /\.(png|jpg|jpeg|svg|gif|webp|ico|woff|woff2|ttf|css|js)(\?.*)?$/i.test(request.url);
    if (isStaticAsset) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                const fetchPromise = fetch(request)
                    .then((networkResponse) => {
                        if (networkResponse && networkResponse.status === 200) {
                            const responseClone = networkResponse.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                        }
                        return networkResponse;
                    })
                    .catch(() => cachedResponse);

                return cachedResponse || fetchPromise;
            })
        );
    }
});
