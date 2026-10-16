<x-layouts.app title="Pelan 21 Hari">

<script>
    window._plan = @json($plan);
</script>

<div x-data="{
    days: window._plan,
    settings: HafalanPlan.settings(),
    done: HafalanPlan.done(),
    form: { start: HafalanPlan.today(), time: '21:00' },
    selected: null,

    init() {
        if (this.settings) this.form = { start: this.settings.start, time: this.settings.time };
        this.selected = this.current ? this.current.day : this.days.length;
    },

    get current() { return HafalanPlan.currentDay(this.days); },
    get doneCount() { return this.days.filter(d => HafalanPlan.isDone(d, this.done)).length; },
    get behind() {
        const cal = HafalanPlan.calendarDay();
        return this.current && cal ? Math.max(0, cal - this.current.day) : 0;
    },
    get sel() { return this.days[this.selected - 1]; },
    get icsUrl() {
        return '{{ route('plan.ics') }}?' + new URLSearchParams({ mula: this.form.start, masa: this.form.time });
    },

    begin() {
        HafalanPlan.start(this.form.start, this.form.time);
        this.settings = HafalanPlan.settings();
        this.selected = this.current ? this.current.day : 1;
    },
    saveTime() {
        if (this.settings) {
            HafalanPlan.start(this.settings.start, this.form.time);
            this.settings = HafalanPlan.settings();
        }
    },
    resetPlan() {
        if (confirm('Padam pelan dan mula semula? (Kemajuan hafalan huruf tidak terjejas.)')) {
            HafalanPlan.reset();
            this.settings = null;
            this.done = {};
            this.selected = 1;
        }
    },

    status(day) {
        if (HafalanPlan.isDone(day, this.done)) return 'done';
        if (this.current && day.day === this.current.day) return 'current';
        const date = HafalanPlan.dateOf(day.day);
        if (date && HafalanPlan.today() > date.toLocaleDateString('sv-SE')) return 'late';
        return 'upcoming';
    },
    cellStyle(day) {
        const base = this.selected === day.day ? 'box-shadow: 0 0 0 2px #A6845E;' : '';
        return base + ({
            done:     'background-color: rgba(122,158,126,0.18); border-color: #7A9E7E;',
            current:  'background-color: #EDE0D0; border-color: #C9A882; border-width: 2px;',
            late:     'background-color: rgba(196,123,106,0.10); border-color: #C47B6A;',
            upcoming: 'background-color: #F5F0EB; border-color: #E2D5C8;',
        })[this.status(day)];
    },
    dateLabel(n, opts = { day: 'numeric', month: 'short' }) {
        const d = HafalanPlan.dateOf(n);
        return d ? d.toLocaleDateString('ms-MY', opts) : '';
    },
}">

    {{-- ── Belum mula ───────────────────────────────────────────── --}}
    <template x-if="!settings">
        <div>
            <h1 class="text-2xl font-bold mb-1" style="color: #3D2C1E;">Pelan 21 Hari</h1>
            <p class="text-sm mb-6" style="color: #8B6F5E;">Untuk pemula dewasa — 15 hingga 20 minit sehari. Anda kenal semua 29 huruf (termasuk Hamzah) dalam 8 hari, kukuhkan dalam 2 minggu, dan kekal dalam 3 minggu.</p>

            <div class="space-y-3 mb-6">
                <div class="rounded-xl p-4 border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                    <div class="text-xs font-semibold mb-1" style="color: #A6845E;">HARI 1–8 · KENAL HURUF</div>
                    <p class="text-sm" style="color: #3D2C1E;">3–5 huruf baru sehari, ikut keluarga bentuk. Setiap hari ulang kaji huruf semalam.</p>
                    <div class="font-arabic text-2xl mt-2" style="color: #C9A882; direction: rtl;">ا ب ت ث · ج ح خ · د ذ ر ز · …</div>
                </div>
                <div class="rounded-xl p-4 border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                    <div class="text-xs font-semibold mb-1" style="color: #A6845E;">HARI 9–14 · KUKUHKAN</div>
                    <p class="text-sm" style="color: #3D2C1E;">Latih telinga, bentuk bersambung, dan huruf yang serupa. Ujian hari ke-14: sasaran 90%.</p>
                </div>
                <div class="rounded-xl p-4 border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                    <div class="text-xs font-semibold mb-1" style="color: #A6845E;">HARI 15–21 · KEKALKAN</div>
                    <p class="text-sm" style="color: #3D2C1E;">Ulang kaji ringan 10–15 minit. Ujian akhir hari ke-21: sasaran 95%.</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <label class="block">
                    <span class="text-xs font-semibold" style="color: #8B6F5E;">Tarikh mula</span>
                    <input type="date" x-model="form.start"
                           class="mt-1 w-full px-3 py-2.5 rounded-xl border text-sm outline-none"
                           style="background-color: #F5F0EB; border-color: #C9A882; color: #3D2C1E;">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold" style="color: #8B6F5E;">Masa belajar harian</span>
                    <input type="time" x-model="form.time"
                           class="mt-1 w-full px-3 py-2.5 rounded-xl border text-sm outline-none"
                           style="background-color: #F5F0EB; border-color: #C9A882; color: #3D2C1E;">
                </label>
            </div>
            <p class="text-xs mb-6" style="color: #8B6F5E;">Pilih masa yang sama setiap hari, contohnya selepas Isyak. Tabiat lebih mudah terbentuk pada masa yang tetap.</p>

            <button @click="begin()"
                    class="w-full py-4 rounded-xl font-bold text-white text-lg transition-opacity hover:opacity-90"
                    style="background-color: #A6845E; min-height: 56px;">
                Mula Pelan →
            </button>
        </div>
    </template>

    {{-- ── Pelan aktif ──────────────────────────────────────────── --}}
    <template x-if="settings">
        <div>
            <div class="flex items-end justify-between mb-2">
                <div>
                    <h1 class="text-2xl font-bold" style="color: #3D2C1E;">Pelan 21 Hari</h1>
                    <p class="text-xs" style="color: #8B6F5E;" x-text="'Mula ' + dateLabel(1, { day: 'numeric', month: 'long' }) + ' · tamat ' + dateLabel(21, { day: 'numeric', month: 'long' })"></p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold" style="color: #A6845E;" x-text="doneCount + '/21'"></div>
                    <div class="text-xs" style="color: #8B6F5E;">hari siap</div>
                </div>
            </div>
            <div class="w-full rounded-full h-2 mb-5" style="background-color: #E2D5C8;">
                <div class="h-2 rounded-full transition-all duration-500" style="background-color: #7A9E7E;" :style="'width:' + (doneCount / 21 * 100) + '%'"></div>
            </div>

            <div x-show="behind > 0" class="rounded-xl p-3 mb-4 text-sm" style="background-color: rgba(196,123,106,0.12); color: #8B4A3C;">
                Anda <strong x-text="behind + ' hari'"></strong> di belakang jadual — tak mengapa. Sambung dari <strong x-text="'Hari ' + current?.day"></strong>; jangan langkau hari.
            </div>

            <div x-show="!current" class="rounded-2xl p-6 mb-6 text-center border-2" style="background-color: rgba(122,158,126,0.15); border-color: #7A9E7E;">
                <div class="text-5xl mb-2">🎉</div>
                <h2 class="text-xl font-bold mb-1" style="color: #3D2C1E;">Tahniah — pelan selesai!</h2>
                <p class="text-sm mb-4" style="color: #8B6F5E;">Teruskan sesi Hafalan harian (5 minit) supaya huruf kekal. Langkah seterusnya: baris (fathah, kasrah, dammah).</p>
                <a href="{{ route('baris') }}" class="inline-block px-6 py-3 rounded-xl font-semibold text-white" style="background-color: #7A9E7E;">Mula Baris & Suku Kata →</a>
            </div>

            {{-- Butiran hari dipilih --}}
            <template x-if="sel">
                <div class="rounded-2xl p-5 mb-6 border-2" style="background-color: #F5F0EB;" :style="'border-color:' + (status(sel) === 'done' ? '#7A9E7E' : '#C9A882')">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold" style="color: #A6845E;" x-text="'HARI ' + sel.day + ' · ' + sel.phase.toUpperCase()"></span>
                        <span class="text-xs" style="color: #8B6F5E;" x-text="dateLabel(sel.day, { weekday: 'long', day: 'numeric', month: 'short' })"></span>
                    </div>
                    <h2 class="text-xl font-bold mb-3" style="color: #3D2C1E;" x-text="sel.title"></h2>

                    <template x-if="sel.letters.length">
                        <div class="flex flex-wrap gap-5 justify-center mb-4" style="direction: rtl;">
                            <template x-for="(l, i) in sel.letters" :key="i">
                                <button @click="speakArabic(l)" class="text-center hover:opacity-80">
                                    <div class="font-arabic text-5xl" style="color: #A6845E; line-height: 1.6;" x-text="l"></div>
                                    <div class="text-xs mt-2" style="color: #8B6F5E;" x-text="sel.names[i]"></div>
                                </button>
                            </template>
                        </div>
                    </template>

                    <div class="rounded-xl p-3 mb-4 text-sm" style="background-color: #EDE0D0; color: #3D2C1E; border-left: 3px solid #C9A882;">
                        💡 <span x-text="sel.tip"></span>
                    </div>

                    <div class="space-y-2">
                        <a :href="sel.sesi_url" class="flex items-center gap-3 rounded-xl p-3 border transition-all hover:opacity-90"
                           style="background-color: #FAF7F4; border-color: #E2D5C8;">
                            <span class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold text-white" style="background-color: #A6845E;">1</span>
                            <div class="flex-1">
                                <div class="font-semibold text-sm" style="color: #3D2C1E;">Sesi Hafalan</div>
                                <div class="text-xs" style="color: #8B6F5E;" x-text="sel.letters.length ? 'Huruf baru hari ini + ulang kaji' : 'Habiskan kad yang perlu diulang'"></div>
                            </div>
                            <span style="color: #C9A882;">→</span>
                        </a>
                        <a :href="sel.quiz_url" class="flex items-center gap-3 rounded-xl p-3 border transition-all hover:opacity-90"
                           style="background-color: #FAF7F4; border-color: #E2D5C8;">
                            <span class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold text-white" style="background-color: #A6845E;">2</span>
                            <div class="flex-1">
                                <div class="font-semibold text-sm" style="color: #3D2C1E;" x-text="sel.quiz_label"></div>
                                <div class="text-xs" style="color: #8B6F5E;">
                                    <span x-show="!done[sel.day]" x-text="sel.target ? 'Sasaran ' + sel.target + '% — hari siap bila lulus' : 'Hari ini siap selepas kuiz dihabiskan'"></span>
                                    <span x-show="done[sel.day]" x-text="'Skor terbaik ' + done[sel.day]?.percentage + '%' + (sel.target && done[sel.day]?.percentage < sel.target ? ' — cuba lagi, sasaran ' + sel.target + '%' : ' ✓')"></span>
                                </div>
                            </div>
                            <span style="color: #C9A882;">→</span>
                        </a>
                    </div>
                </div>
            </template>

            <x-nasihat tag="plan" class="mb-6" />

            {{-- Kalendar --}}
            <h2 class="font-semibold mb-2" style="color: #3D2C1E;">Kalendar</h2>
            <div class="grid grid-cols-7 gap-1.5 mb-2">
                <template x-for="day in days" :key="day.day">
                    <button @click="selected = day.day"
                            class="rounded-lg border p-1 text-center transition-all"
                            :style="cellStyle(day)" style="min-height: 64px;">
                        <div class="text-[10px]" style="color: #8B6F5E;" x-text="dateLabel(day.day)"></div>
                        <div class="text-xs font-bold" style="color: #3D2C1E;" x-text="'H' + day.day"></div>
                        <div class="font-arabic text-sm leading-tight" style="color: #A6845E; direction: rtl;"
                             x-text="day.letters.length ? day.letters.join('') : (status(day) === 'done' ? '✓' : ({ 'Kukuhkan': '◆', 'Kekalkan': '↺' })[day.phase])"></div>
                    </button>
                </template>
            </div>
            <div class="flex flex-wrap gap-3 text-[11px] mb-6" style="color: #8B6F5E;">
                <span><span class="inline-block w-2.5 h-2.5 rounded-sm align-middle mr-1" style="background:#7A9E7E"></span>Siap</span>
                <span><span class="inline-block w-2.5 h-2.5 rounded-sm align-middle mr-1" style="background:#C9A882"></span>Hari ini</span>
                <span><span class="inline-block w-2.5 h-2.5 rounded-sm align-middle mr-1" style="background:#C47B6A"></span>Tertinggal</span>
                <span>◆ Kukuhkan · ↺ Kekalkan</span>
            </div>

            <x-pasang-app class="mb-4" />

            {{-- Peringatan kalendar --}}
            <div class="rounded-2xl p-5 mb-6 border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                <h2 class="font-semibold mb-1" style="color: #3D2C1E;">📅 Peringatan harian</h2>
                <p class="text-xs mb-3" style="color: #8B6F5E;">Muat turun dan buka fail ini. Google Calendar, iPhone atau Outlook akan menambah 21 acara (20 minit setiap satu) dengan peringatan 5 minit sebelum.</p>
                <div class="flex items-end gap-3">
                    <label class="block">
                        <span class="text-xs" style="color: #8B6F5E;">Masa</span>
                        <input type="time" x-model="form.time" @change="saveTime()"
                               class="mt-1 px-3 py-2 rounded-xl border text-sm outline-none"
                               style="background-color: #FAF7F4; border-color: #C9A882; color: #3D2C1E;">
                    </label>
                    <a :href="icsUrl"
                       class="flex-1 py-2.5 rounded-xl font-semibold text-white text-center transition-opacity hover:opacity-90"
                       style="background-color: #A6845E;">
                        Tambah ke Kalendar
                    </a>
                </div>
            </div>

            <button @click="resetPlan()" class="w-full py-3 rounded-xl text-sm border" style="color: #C47B6A; border-color: #C47B6A;">
                Mula semula pelan
            </button>
        </div>
    </template>

</div>

</x-layouts.app>
