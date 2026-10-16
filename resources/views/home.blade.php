<x-layouts.app title="Hafal Huruf Hijaiyah">

<script>window._letters = @json($letters);</script>

<div x-data="{
    letters: window._letters,
    sm2: HafalanSRS.load(),
    week: Kemajuan.week(),
    sessions: Kemajuan.sessionsToday(),
    speed: Kemajuan.speedStats(),
    baris: (() => { try { return JSON.parse(localStorage.getItem('arabic_baris') || '{}'); } catch { return {}; } })(),
    reminderTimes: ['08:00', '13:00', '21:00'],
    reminderSet: (() => { try { return !!localStorage.getItem('hafalan_reminder_set'); } catch { return false; } })(),

    get greeting() {
        const h = new Date().getHours();
        return h < 12 ? 'Selamat pagi' : h < 15 ? 'Selamat tengah hari' : h < 19 ? 'Selamat petang' : 'Selamat malam';
    },
    level(id) { return Kemajuan.levelOf(this.sm2[id]); },
    get due() { return this.letters.filter(l => this.sm2[l.id] && this.sm2[l.id].nextReview <= Date.now()).length; },
    get unseen() { return this.letters.filter(l => !this.sm2[l.id]).length; },
    get known() { return this.letters.filter(l => ['ingat', 'hafal'].includes(this.level(l.id))).length; },
    get started() { return this.letters.length - this.unseen; },
    get ctaHint() {
        if (!this.started) return 'Mula dengan 2 huruf pertama';
        const parts = [];
        if (this.unseen && this.due <= 8) parts.push(Math.min(2, this.unseen) + ' huruf baru');
        if (this.due) parts.push(this.due + ' ulang kaji');
        return parts.length ? parts.join(' · ') : 'Latihan huruf yang paling lemah';
    },
    get barisDone() { return ['a', 'i', 'u', 'campur'].filter(k => (this.baris[k] ?? 0) >= 90).length; },
    get bestSpeed() {
        const b = this.speed.best;
        return b ? { ...b, letter: this.letters.find(l => l.id === b.id) } : null;
    },
    get reminderUrl() {
        return '{{ route('reminders.ics') }}?' + this.reminderTimes.map(t => 'masa[]=' + encodeURIComponent(t)).join('&');
    },
    markReminder() { try { localStorage.setItem('hafalan_reminder_set', '1'); } catch {} setTimeout(() => this.reminderSet = true, 800); },
}" class="max-w-xl mx-auto">

    <p class="text-sm mb-1" style="color:#8B6F5E;" x-text="greeting + ' 👋'"></p>
    <h1 class="text-2xl font-bold mb-5" style="color:#3D2C1E;" x-text="started ? 'Jom sambung 5 minit' : 'Jom mula belajar hijaiyah'"></h1>

    {{-- Satu tindakan utama --}}
    <a href="{{ route('kilat') }}" class="block rounded-3xl p-6 mb-4 text-white shadow-sm transition active:scale-[0.99]" style="background:linear-gradient(135deg,#A6845E,#8C6B47);">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-2xl font-bold">⚡ Mula 5 minit</div>
                <div class="text-sm opacity-90 mt-1" x-text="ctaHint"></div>
            </div>
            <div class="w-14 h-14 rounded-full flex items-center justify-center text-2xl" style="background:rgba(255,255,255,0.2);">▶</div>
        </div>
        <div x-show="sessions" class="text-xs mt-3 opacity-90" x-text="'Hari ini: ' + sessions + ' sesi ✓'"></div>
    </a>

    {{-- Sasaran mingguan (bukan streak) --}}
    <div class="rounded-2xl p-4 mb-4 flex items-center justify-between gap-3" style="background:#F5F0EB;">
        <div>
            <div class="text-xs font-bold" style="color:#8B6F5E;">MINGGU INI</div>
            <div class="text-sm" style="color:#3D2C1E;" x-text="week.reached ? 'Sasaran tercapai 🏆' : (week.done + ' / ' + week.target + ' hari')"></div>
        </div>
        <div class="flex gap-1.5">
            <template x-for="(d, i) in week.days" :key="i">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold"
                     :style="d.done ? 'background:#7A9E7E;color:#fff' : (d.today ? 'border:2px solid #C9A882;color:#A6845E' : 'background:#E2D5C8;color:#B8A090')" x-text="d.label"></div>
            </template>
        </div>
    </div>

    {{-- Bukti kemajuan: kelajuan --}}
    <template x-if="bestSpeed">
        <div class="rounded-2xl p-4 mb-4 flex items-center gap-3" style="background:#EDE0D0;">
            <span class="font-arabic text-4xl" style="line-height:1.5;" :style="'color:' + bestSpeed.letter.color" x-text="bestSpeed.letter.arabic"></span>
            <div class="text-sm" style="color:#3D2C1E;">⚡ Awak kenal <strong x-text="bestSpeed.letter.name"></strong> dalam <strong x-text="(bestSpeed.now / 1000).toFixed(1) + 's'"></strong>
                <span style="color:#8B6F5E;" x-text="'(minggu lepas ' + (bestSpeed.then / 1000).toFixed(1) + 's)'"></span></div>
        </div>
    </template>

    {{-- Jalan ke Jawi --}}
    <div class="rounded-2xl p-4 mb-4" style="background:#F5F0EB;">
        <div class="text-xs font-bold mb-3" style="color:#8B6F5E;">🧭 JALAN KE JAWI</div>
        <div class="space-y-3">
            <a href="{{ route('learn') }}" class="flex items-center gap-3">
                <span class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-sm font-bold text-white" :style="'background:' + (known === 29 ? '#7A9E7E' : '#A6845E')">1</span>
                <div class="flex-1">
                    <div class="flex justify-between text-sm"><span class="font-semibold" style="color:#3D2C1E;">Huruf hijaiyah</span><span style="color:#8B6F5E;" x-text="known + '/29 diingat'"></span></div>
                    <div class="h-2 rounded-full mt-1" style="background:#E2D5C8;">
                        <div class="h-2 rounded-full" style="background:#F0D9A8;" :style="'width:' + (started / 29 * 100) + '%'">
                            <div class="h-2 rounded-full" style="background:#7A9E7E;" :style="'width:' + (started ? known / started * 100 : 0) + '%'"></div>
                        </div>
                    </div>
                </div>
            </a>
            <a href="{{ route('baris') }}" class="flex items-center gap-3" :class="known < 20 && 'opacity-60'">
                <span class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-sm font-bold" :style="known >= 20 ? 'background:#A6845E;color:#fff' : 'background:#E2D5C8;color:#8B6F5E'">2</span>
                <div class="flex-1">
                    <div class="flex justify-between text-sm"><span class="font-semibold" style="color:#3D2C1E;">Baris & suku kata <bdi class="font-arabic whitespace-nowrap" dir="rtl">بَ بِ بُ</bdi></span>
                        <span class="shrink-0 ml-2" style="color:#8B6F5E;" x-text="known >= 20 ? barisDone + '/4' : '🔒 20 huruf'"></span></div>
                    <div class="h-2 rounded-full mt-1" style="background:#E2D5C8;"><div class="h-2 rounded-full" style="background:#7A9E7E;" :style="'width:' + (barisDone / 4 * 100) + '%'"></div></div>
                </div>
            </a>
            <div class="flex items-center gap-3 opacity-60">
                <span class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-sm font-bold" style="background:#E2D5C8;color:#8B6F5E;">3</span>
                <div class="flex-1 flex justify-between text-sm"><span class="font-semibold" style="color:#3D2C1E;">6 huruf Jawi <bdi class="font-arabic whitespace-nowrap" dir="rtl">چ ڠ ڤ ݢ ۏ ڽ</bdi></span><span style="color:#8B6F5E;">🔒</span></div>
            </div>
            <div class="flex items-center gap-3 opacity-60">
                <span class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-sm font-bold" style="background:#E2D5C8;color:#8B6F5E;">4</span>
                <div class="flex-1 flex justify-between text-sm"><span class="font-semibold" style="color:#3D2C1E;">Baca perkataan Jawi <bdi class="font-arabic" dir="rtl">بوکو</bdi></span><span style="color:#8B6F5E;">🔒</span></div>
            </div>
        </div>
    </div>

    {{-- Koleksi huruf: warna = keluarga, bar = tahap --}}
    <div class="rounded-2xl p-4 mb-4" style="background:#F5F0EB;">
        <div class="flex justify-between items-center mb-3">
            <div class="text-xs font-bold" style="color:#8B6F5E;">🎴 KOLEKSI HURUF</div>
            <div class="text-xs" style="color:#8B6F5E;" x-text="started + '/29 dikumpul'"></div>
        </div>
        <div class="grid grid-cols-6 sm:grid-cols-8 gap-2" style="direction:rtl;">
            <template x-for="l in letters" :key="l.id">
                <a :href="'/belajar/' + l.id" class="rounded-xl pt-1 pb-1.5 text-center border" style="background:#FFFFFF;border-color:#E2D5C8;"
                   :title="l.name + ' — ' + Kemajuan.LEVELS[level(l.id)].label">
                    <div class="font-arabic text-2xl" style="line-height:1.6;" :style="level(l.id) === 'baru' ? 'color:#D9CBBB' : 'color:' + l.color" x-text="l.arabic"></div>
                    <div class="flex gap-0.5 px-1.5" style="direction:ltr;">
                        <template x-for="n in 3" :key="n">
                            <div class="flex-1 h-1 rounded-full" :style="'background:' + (Kemajuan.LEVELS[level(l.id)].rank >= n ? '#7A9E7E' : '#E2D5C8')"></div>
                        </template>
                    </div>
                </a>
            </template>
        </div>
        <div class="flex flex-wrap gap-x-3 gap-y-1 mt-3 text-[11px]" style="color:#8B6F5E;">
            @foreach($legend as $f)
                <span><span class="font-arabic text-base" style="color: {{ $f['color'] }};">{{ $f['sample'] }}</span> {{ str_replace('Keluarga ', '', $f['label']) }}</span>
            @endforeach
        </div>
        <p class="text-[11px] mt-1" style="color:#B8A090;">Bar bawah: ▬ kenal · ▬▬ ingat · ▬▬▬ hafal</p>
    </div>

    {{-- Peringatan: elak terlupa --}}
    <template x-if="!reminderSet">
        <div class="rounded-2xl p-4 mb-4 border-2 border-dashed" style="border-color:#C9A882;">
            <div class="font-semibold text-sm mb-1" style="color:#3D2C1E;">⏰ Jangan terlupa — pasang peringatan</div>
            <p class="text-xs mb-3" style="color:#8B6F5E;">Peringatan 5 minit di kalendar telefon, setiap hari. Ubah masa ikut rutin awak:</p>
            <div class="flex gap-2 mb-3">
                <template x-for="(t, i) in reminderTimes" :key="i">
                    <input type="time" x-model="reminderTimes[i]" class="flex-1 min-w-0 px-1 py-2 rounded-xl border text-xs text-center" style="background:#FAF7F4;border-color:#E2D5C8;color:#3D2C1E;">
                </template>
            </div>
            <a :href="reminderUrl" @click="markReminder()" class="block w-full py-3 rounded-xl text-center font-semibold text-white" style="background:#A6845E;">Tambah ke kalendar</a>
        </div>
    </template>

    <x-nasihat tag="home" class="mb-4" />
    <x-pasang-app class="mb-4" />

</div>

</x-layouts.app>
