{{-- Selaras kemajuan antara telefon dan laptop menggunakan kod rahsia. --}}
<div x-data="{
        code: HafalanSync.code(),
        at: HafalanSync.lastSynced(),
        input: '',
        busy: false,
        message: '',
        error: false,
        get pretty() { return HafalanSync.formatCode(this.code); },
        get atLabel() { return this.at ? new Date(this.at).toLocaleString('ms-MY', { dateStyle: 'medium', timeStyle: 'short' }) : 'belum'; },
        async run(fn, ok) {
            this.busy = true; this.message = ''; this.error = false;
            try {
                const changed = await fn();
                this.code = HafalanSync.code();
                this.at = HafalanSync.lastSynced();
                this.message = ok + (changed === true ? ' Kemajuan dari peranti lain digabungkan — memuat semula…' : '');
                if (changed === true) setTimeout(() => location.reload(), 1200);
            } catch (e) {
                this.error = true;
                this.message = e.message || 'Gagal. Semak sambungan internet.';
                this.code = HafalanSync.code();
            } finally { this.busy = false; }
        },
        create() { this.run(() => HafalanSync.create().then(() => false), 'Kod dibuat.'); },
        link() { this.run(() => HafalanSync.link(this.input), 'Peranti disambung.'); },
        now() { this.run(() => HafalanSync.syncNow(), 'Diselaraskan.'); },
        unlink() {
            if (!confirm('Putuskan peranti ini? Kemajuan di peranti ini kekal, tetapi tidak lagi diselaraskan.')) return;
            HafalanSync.unlink(); this.code = null; this.message = '';
        },
        copy() { navigator.clipboard?.writeText(this.pretty); this.message = 'Kod disalin.'; this.error = false; },
     }"
     {{ $attributes->merge(['class' => 'rounded-2xl p-5 border']) }}
     style="background-color: #F5F0EB; border-color: #E2D5C8;">

    <h2 class="font-semibold mb-1" style="color: #3D2C1E;">🔄 Selaraskan Peranti</h2>

    <template x-if="!code">
        <div>
            <p class="text-xs mb-4" style="color: #8B6F5E;">Guna telefon dan laptop? Buat kod di satu peranti, kemudian masukkan kod yang sama di peranti lain. Kemajuan akan digabung — tiada yang hilang.</p>
            <button type="button" @click="create()" :disabled="busy"
                    class="w-full py-3 rounded-xl font-semibold text-white mb-3 disabled:opacity-50" style="background-color: #A6845E;">
                Buat kod baharu
            </button>
            <div class="flex gap-2">
                <input type="text" x-model="input" placeholder="Ada kod? XXXX-XXXX-XXXX" maxlength="16" autocapitalize="characters"
                       class="flex-1 min-w-0 px-3 py-2.5 rounded-xl border text-sm font-mono uppercase outline-none"
                       style="background-color: #FAF7F4; border-color: #C9A882; color: #3D2C1E;">
                <button type="button" @click="link()" :disabled="busy || input.replace(/[^a-z0-9]/gi, '').length !== 12"
                        class="px-4 rounded-xl font-semibold border disabled:opacity-40" style="border-color: #C9A882; color: #A6845E;">
                    Sambung
                </button>
            </div>
        </div>
    </template>

    <template x-if="code">
        <div>
            <p class="text-xs mb-2" style="color: #8B6F5E;">Masukkan kod ini di peranti lain. Simpan kod ini secara peribadi — sesiapa yang ada kod boleh melihat kemajuan anda.</p>
            <button type="button" @click="copy()" class="w-full rounded-xl py-3 mb-2 font-mono text-xl font-bold tracking-widest border-2 border-dashed"
                    style="border-color: #C9A882; color: #3D2C1E; background:#FAF7F4;" x-text="pretty" title="Salin"></button>
            <p class="text-xs mb-3" style="color: #8B6F5E;">Terakhir diselaraskan: <span x-text="atLabel"></span></p>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" @click="now()" :disabled="busy" class="py-2.5 rounded-xl text-sm font-semibold text-white disabled:opacity-50" style="background-color: #A6845E;"
                        x-text="busy ? 'Menyelaras…' : 'Selaraskan sekarang'"></button>
                <button type="button" @click="unlink()" class="py-2.5 rounded-xl text-sm border" style="border-color: #C47B6A; color: #C47B6A;">Putuskan</button>
            </div>
        </div>
    </template>

    <p x-show="message" class="text-xs mt-3" :style="{ color: error ? '#C47B6A' : '#4F7353' }" x-text="message"></p>
</div>
