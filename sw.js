const CACHE_VERSION = 'czn-v1';
const STATIC_ASSETS = [
  '/style/siteWideStyle.css',
  '/style/carousel.css',
  '/components/favicon/android-chrome-192x192.png',
  '/components/favicon/android-chrome-512x512.png',
  '/components/favicon/apple-touch-icon.png',
  '/components/favicon/favicon-32x32.png',
  '/components/favicon/favicon-16x16.png',
  '/offline'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_VERSION).then(cache => cache.addAll(STATIC_ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE_VERSION).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  const { request } = event;

  // Skip non-GET requests (form submissions, etc.)
  if (request.method !== 'GET') return;

  // Skip cross-origin requests (CDN assets, analytics)
  if (!request.url.startsWith(self.location.origin)) return;

  // Static assets: cache-first
  if (request.url.match(/\.(css|js|png|ico|jpg|jpeg|svg|woff2?)(\?|$)/)) {
    event.respondWith(
      caches.match(request).then(cached => cached || fetch(request).then(response => {
        const clone = response.clone();
        caches.open(CACHE_VERSION).then(cache => cache.put(request, clone));
        return response;
      }))
    );
    return;
  }

  // Pages: network-first with offline fallback
  event.respondWith(
    fetch(request).then(response => {
      const clone = response.clone();
      caches.open(CACHE_VERSION).then(cache => cache.put(request, clone));
      return response;
    }).catch(() => caches.match(request).then(cached => cached || caches.match('/offline')))
  );
});
