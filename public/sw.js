// Service worker — app boleh dipasang di telefon dan digunakan tanpa internet.
//
// Halaman      : network-first (sentiasa terkini bila online), simpanan sebagai sandaran offline.
// Aset & bunyi : cache-first (fail Vite ber-hash dan sebutan huruf tidak berubah).
// Naikkan VERSION bila strategi cache berubah.

const VERSION = 'v6';
const CACHE = `hijaiyah-${VERSION}`;

const CORE_PAGES = [
    '/', '/pelan', '/belajar', '/hafalan', '/hafalan/sesi?group=all',
    '/kuiz', '/statistik', '/keputusan', '/offline.html', '/manifest.json',
    '/icons/icon-192.png', '/icons/icon-512.png',
];

// Fail sebutan (suara AI + Lingua Libre) — senarai dari pelayan supaya sentiasa sepadan.
async function audioFiles() {
    try {
        return await (await fetch('/audio/senarai.json', { cache: 'no-store' })).json();
    } catch {
        return [];
    }
}

async function viteAssets() {
    try {
        const manifest = await (await fetch('/build/manifest.json', { cache: 'no-store' })).json();
        return Object.values(manifest).flatMap(e => [e.file, ...(e.css || [])]).map(f => `/build/${f}`);
    } catch {
        return [];
    }
}

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        const cache = await caches.open(CACHE);
        await cache.addAll([...CORE_PAGES, ...(await viteAssets())]);

        // Tambahan — jangan gagalkan pemasangan jika ada yang tidak dapat dimuat.
        const extras = [
            ...Array.from({ length: 29 }, (_, i) => `/belajar/${i + 1}`),
            ...(await audioFiles()),
            '/baris', '/kredit', '/kilat', '/latihan',
        ];
        await Promise.allSettled(extras.map(url => cache.add(url)));

        await self.skipWaiting();
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(keys.filter(k => k.startsWith('hijaiyah-') && k !== CACHE).map(k => caches.delete(k)));
        await self.clients.claim();
    })());
});

async function networkFirst(request) {
    const cache = await caches.open(CACHE);
    try {
        const response = await fetch(request);
        if (response.ok) cache.put(request, response.clone());
        return response;
    } catch {
        const cached = await cache.match(request);
        if (cached) return cached;
        if (request.mode === 'navigate') return cache.match('/offline.html');
        return Response.error();
    }
}

async function cacheFirst(request) {
    const cache = await caches.open(CACHE);
    const cached = await cache.match(request);
    if (cached) return cached;
    const response = await fetch(request);
    if (response.ok || response.type === 'opaque') cache.put(request, response.clone());
    return response;
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    if (url.origin === location.origin) {
        // Fail kalendar sentiasa dijana baharu dan dimuat turun, bukan dipaparkan.
        if (url.pathname === '/pelan.ics') return;
        // API (sync, rakaman guru) sentiasa terus ke pelayan.
        if (url.pathname.startsWith('/api/') || url.pathname === '/audio/senarai.json') return;

        // Rakaman rasmi: nama fail berubah setiap kali dirakam semula, jadi selamat di-cache.
        if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/')
            || url.pathname.startsWith('/storage/audio/') || url.pathname.startsWith('/audio/')
            || url.pathname === '/tts') {
            event.respondWith(cacheFirst(request));
            return;
        }
        event.respondWith(networkFirst(request));
        return;
    }

    // Fon Google (Amiri / Inter).
    if (url.hostname === 'fonts.googleapis.com' || url.hostname === 'fonts.gstatic.com') {
        event.respondWith(cacheFirst(request));
    }
});
