const CACHE_NAME = 'kasirpro-v1';
const OFFLINE_URL = '/offline.html';
const STATIC_ASSETS = [
    '/',
    '/dashboard',
    '/login',
    '/build/css/bootstrap.min.css',
    '/build/plugins/fontawesome/css/fontawesome.min.css',
    '/build/plugins/fontawesome/css/all.min.css',
    '/build/js/script.js',
    '/manifest.json'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.filter((name) => name !== CACHE_NAME).map((name) => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(event.request).then((response) => {
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }

                const responseToCache = response.clone();
                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, responseToCache);
                });

                return response;
            });
        })
    );
});

self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-transactions') {
        event.waitUntil(syncPendingTransactions());
    }
});

async function syncPendingTransactions() {
    const pendingTx = await getPendingTransactions();
    
    for (const tx of pendingTx) {
        try {
            const response = await fetch('/api/v1/pos/sale', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${tx.token}`
                },
                body: JSON.stringify(tx.data)
            });

            if (response.ok) {
                await removePendingTransaction(tx.id);
            }
        } catch (error) {
            console.error('Sync failed for transaction:', tx.id, error);
        }
    }
}

async function getPendingTransactions() {
    return [];
}

async function removePendingTransaction(id) {
    return true;
}

self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};
    
    const options = {
        body: data.body || 'New notification',
        icon: '/build/img/logo-192.png',
        badge: '/build/img/logo-192.png',
        data: data.url || '/'
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'KasirPro', options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data)
    );
});
