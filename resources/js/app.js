import './bootstrap';
import Alpine from 'alpinejs';
import './hafalan.js';

// Service worker hanya untuk build production — dalam `npm run dev` ia akan cache fail Vite yang sentiasa berubah.
if (import.meta.env.PROD && 'serviceWorker' in navigator) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));
}

// Simpan prompt pemasangan awal — ia boleh berlaku sebelum Alpine mula.
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    window.__installPrompt = e;
    window.dispatchEvent(new Event('hafalan:installable'));
});
window.addEventListener('appinstalled', () => {
    window.__installPrompt = null;
    window.dispatchEvent(new Event('hafalan:installed'));
});

window.Alpine = Alpine;
Alpine.start();
