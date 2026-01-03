// Service Worker for RenzBilliard PWA
const CACHE_NAME = 'renz-billiard-v1';

// Install event - cache basic assets
self.addEventListener('install', (event) => {
    console.log('Service Worker installing...');
    self.skipWaiting();
});

// Activate event
self.addEventListener('activate', (event) => {
    console.log('Service Worker activated');
    event.waitUntil(clients.claim());
});

// Fetch event - network first strategy
self.addEventListener('fetch', (event) => {
    event.respondWith(
        fetch(event.request)
            .catch(() => {
                // If network fails, could return cached version
                return caches.match(event.request);
            })
    );
});
