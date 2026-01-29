self.addEventListener('install', () => {
    self.skipWaiting();
});
self.addEventListener('activate', (e) => {
    e.waitUntil(self.clients.claim());
});
self.addEventListener('fetch', () => {});

self.addEventListener('push', (event) => {
    if (!event.data) return;
    let data;
    try { data = event.data.json(); } catch { return; }
    const title = data.title || 'Nova Notificação';
    const options = {
        body: data.body || '',
        icon: data.icon || '/laravelpwa/icons/icon-192x192.png',
        badge: data.badge || '/laravelpwa/icons/icon-192x192.png',
        data: data.data || {},
        actions: data.actions || [{action:'open', title:'Abrir'}]
    };
    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = (event.notification.data && event.notification.data.url) || '/';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(list => {
            for (const c of list) {
                if (c.url.includes(url) && 'focus' in c) return c.focus();
            }
            return clients.openWindow ? clients.openWindow(url) : null;
        })
    );
});

self.addEventListener('pushsubscriptionchange', (event) => {
    // aqui você pode re-obter VAPID e re-assinar, depois POSTar ao backend
});
