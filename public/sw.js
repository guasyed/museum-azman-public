const CACHE_NAME = 'museum-azman-v3';

self.addEventListener('install', (event) => {
  // Activate updated worker immediately.
  event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const cacheKeys = await caches.keys();
    await Promise.all(cacheKeys
      .filter((key) => key !== CACHE_NAME)
      .map((key) => caches.delete(key)));
    await self.clients.claim();
  })());
});

self.addEventListener('fetch', (event) => {
  const { request } = event;

  if (request.method !== 'GET') {
    return;
  }

  const requestUrl = new URL(request.url);
  if (requestUrl.origin !== self.location.origin) {
    return;
  }

  const acceptsHtml = request.headers.get('accept')?.includes('text/html');
  const isDocumentRequest = request.mode === 'navigate' || request.destination === 'document' || acceptsHtml;
  const isAuthOrSettingsPath = /^\/(login|register|forgot-password|reset-password|profile|settings)(\/|$)/.test(requestUrl.pathname);

  // Never cache HTML pages and auth/settings routes so CSRF tokens stay fresh.
  if (isDocumentRequest || isAuthOrSettingsPath) {
    event.respondWith(fetch(request));
    return;
  }

  event.respondWith((async () => {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(request);
    if (cached) {
      return cached;
    }

    const response = await fetch(request);
    if (response && response.status === 200 && response.type === 'basic') {
      cache.put(request, response.clone());
    }

    return response;
  })());
});
