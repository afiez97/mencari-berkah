import './bootstrap';
import Alpine from 'alpinejs';
import './hafalan.js';
import './kemajuan.js';

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

// Nota: jangan guna `x-show` bersama `:style` teks pada elemen yang sama (teks menulis semula
// `display:none`) — guna sintaks objek `:style="{ color: … }"` untuk kes itu.
// Alpine menggantikan seluruh atribut `style` bila `:style` memulangkan teks — saiz fon,
// warna latar bar kemajuan dsb. dalam `style` statik hilang. Gabungkan statik + dinamik
// supaya `style="font-size:9rem" :style="'color:' + x"` berfungsi seperti dijangka.
function mergeStaticStyles(root) {
    root.querySelectorAll('[style][\\:style], [style][x-bind\\:style]').forEach((el) => {
        const attr = el.hasAttribute(':style') ? ':style' : 'x-bind:style';
        const expr = el.getAttribute(attr);
        const staticStyle = el.getAttribute('style').trim().replace(/;?$/, ';');
        el.setAttribute(attr, `((v) => typeof v === 'string' ? ${JSON.stringify(staticStyle)} + v : v)(${expr})`);
    });
    root.querySelectorAll('template').forEach((t) => mergeStaticStyles(t.content));
}
mergeStaticStyles(document);

window.Alpine = Alpine;
Alpine.start();
