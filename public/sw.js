// Minimal service worker: enables PWA installability. No caching —
// this app is dynamic (auth, admin CRUD), so we deliberately don't
// intercept or cache requests, just pass everything through to the network.
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));
self.addEventListener('fetch', () => {});
