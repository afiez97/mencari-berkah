{{-- Ajak pasang app di skrin utama telefon. Tersembunyi bila sudah dipasang atau pelayar tidak menyokong. --}}
<div x-data="{
        canPrompt: !!window.__installPrompt,
        installed: window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true,
        ios: /iphone|ipad|ipod/i.test(navigator.userAgent),
        dismissed: (() => { try { return Number(localStorage.getItem('pwa_dismissed_until') || 0) > Date.now(); } catch { return false; } })(),
        showIosSteps: false,
        get visible() { return !this.installed && !this.dismissed && (this.canPrompt || this.ios); },
        async install() {
            const prompt = window.__installPrompt;
            if (!prompt) return;
            prompt.prompt();
            const { outcome } = await prompt.userChoice;
            window.__installPrompt = null;
            this.canPrompt = false;
            if (outcome === 'accepted') this.installed = true;
        },
        later() {
            try { localStorage.setItem('pwa_dismissed_until', String(Date.now() + 7 * 86400000)); } catch {}
            this.dismissed = true;
        },
     }"
     @hafalan:installable.window="canPrompt = true"
     @hafalan:installed.window="installed = true"
     x-show="visible" x-cloak
     {{ $attributes->merge(['class' => 'rounded-2xl p-4 border']) }}
     style="background-color: #F5F0EB; border-color: #C9A882;">
    <div class="flex items-center gap-3">
        <img src="/icons/icon-192.png" alt="" class="w-12 h-12 rounded-xl shrink-0">
        <div class="flex-1 min-w-0">
            <div class="font-semibold text-sm" style="color: #3D2C1E;">Pasang di telefon</div>
            <div class="text-xs" style="color: #8B6F5E;">Buka terus dari skrin utama, boleh guna tanpa internet.</div>
        </div>
    </div>

    <div x-show="ios && showIosSteps" class="mt-3 text-sm rounded-xl p-3" style="background-color: #FAF7F4; color: #3D2C1E;">
        <ol class="list-decimal ml-5 space-y-1">
            <li>Buka halaman ini dalam <strong>Safari</strong>.</li>
            <li>Tekan butang <strong>Kongsi</strong> (kotak dengan anak panah ⬆️).</li>
            <li>Pilih <strong>Tambah ke Skrin Utama</strong>, kemudian <strong>Tambah</strong>.</li>
        </ol>
    </div>

    <div class="flex gap-2 mt-3">
        <button type="button" x-show="canPrompt" @click="install()"
                class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white" style="background-color: #A6845E;">
            Pasang
        </button>
        <button type="button" x-show="!canPrompt && ios && !showIosSteps" @click="showIosSteps = true"
                class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white" style="background-color: #A6845E;">
            Tunjuk cara
        </button>
        <button type="button" @click="later()"
                class="px-4 py-2.5 rounded-xl text-sm border" style="border-color: #E2D5C8; color: #8B6F5E;">
            Nanti
        </button>
    </div>
</div>
