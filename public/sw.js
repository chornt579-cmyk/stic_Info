/**
 * STIC Info - Progressive Web App Service Worker
 * Official Institutional Platform for STIC Siem Reap
 */

const CACHE_NAME = 'stic-info-cache-v1';
const PRECACHE_ASSETS = [
    '/',
    '/images/stic-logo.png',
    '/manifest.webmanifest'
];

// Install Event: Precaches essential application assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch((err) => {
                console.warn('STIC ServiceWorker precache warning:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// Activate Event: Cleans up obsolete caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event: Network-first for dynamic content, cache fallback for assets
self.addEventListener('fetch', (event) => {
    // Only handle GET requests
    if (event.request.method !== 'GET') return;

    // Skip non-http/https schemes (like chrome-extension)
    if (!event.request.url.startsWith('http')) return;

    event.respondWith(
        fetch(event.request)
            .then((networkResponse) => {
                // If network response is valid, clone and update cache for static assets
                if (networkResponse && networkResponse.status === 200) {
                    const url = event.request.url;
                    if (url.includes('/build/') || url.includes('/images/') || url.includes('fonts.')) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseClone);
                        });
                    }
                }
                return networkResponse;
            })
            .catch(() => {
                // Network failed: attempt to serve from cache
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // Return home cache if navigations fail offline
                    if (event.request.mode === 'navigate') {
                        return caches.match('/');
                    }
                });
            })
    );
});

// Push Notification Event (Browser & Mobile Alerts)
self.addEventListener('push', (event) => {
    let data = { title: 'STIC Info ដំណឹងថ្មី', body: 'មានសេចក្តីជូនដំណឹងថ្មីពីវិទ្យាស្ថាន STIC' };
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: '/images/stic-logo.png',
        badge: '/images/stic-logo.png',
        vibrate: [200, 100, 200],
        data: {
            url: data.url || '/'
        }
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Notification Click Event: Focus or open the target tab
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});

