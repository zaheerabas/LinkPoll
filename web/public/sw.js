/* LinkPoll service worker — enables install + device notifications.
   Kept deliberately simple for shared hosting (no offline caching of the API). */
const VERSION = 'lp-v1';

self.addEventListener('install', (e) => { self.skipWaiting(); });
self.addEventListener('activate', (e) => { e.waitUntil(self.clients.claim()); });

/* The page asks the SW to show a notification (works while a tab is open,
   including a backgrounded tab). */
self.addEventListener('message', (e) => {
  const d = e.data || {};
  if (d.type === 'notify' && self.registration && self.registration.showNotification) {
    self.registration.showNotification(d.title || 'LinkPoll', {
      body: d.body || '',
      icon: 'icon-192.png',
      badge: 'icon-192.png',
      tag: d.tag || undefined,
      data: { url: d.url || './' }
    });
  }
});

/* Future-proof: if you later add Web Push (VAPID), pushed payloads show here too. */
self.addEventListener('push', (e) => {
  let data = {};
  try { data = e.data ? e.data.json() : {}; } catch (_) {}
  e.waitUntil(self.registration.showNotification(data.title || 'LinkPoll', {
    body: data.body || '', icon: 'icon-192.png', badge: 'icon-192.png',
    data: { url: data.url || './' }
  }));
});

/* Clicking a notification focuses an open tab (or opens the app) at the target. */
self.addEventListener('notificationclick', (e) => {
  e.notification.close();
  const url = (e.notification.data && e.notification.data.url) || './';
  e.waitUntil((async () => {
    const all = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
    for (const c of all) { if ('focus' in c) { c.focus(); if ('navigate' in c && url) { try { await c.navigate(url); } catch (_) {} } return; } }
    if (self.clients.openWindow) return self.clients.openWindow(url);
  })());
});
