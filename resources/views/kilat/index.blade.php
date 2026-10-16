<x-layouts.app title="5 Minit">

<script>
    window._kilat = {
        letters: @json($letters),
        lookalikes: @json($lookalikes),
        soundalikes: @json($soundalikes),
    };
</script>

<div x-data="kilatSession()" x-init="start()" class="max-w-md mx-auto">

    {{-- ── Sedang berjalan ─────────────────────────────────────────── --}}
    <template x-if="!done && step">
        <div>
            <div class="flex items-center gap-3 mb-6">
                <a href="{{ route('home') }}" class="text-xl leading-none" style="color:#B8A090;" title="Keluar">✕</a>
                <div class="flex-1 h-2.5 rounded-full" style="background:#E2D5C8;">
                    <div class="h-2.5 rounded-full transition-all duration-500" style="background:#7A9E7E;" :style="'width:' + (index / steps.length * 100) + '%'"></div>
                </div>
                <span class="text-xs tabular-nums" style="color:#8B6F5E;" x-text="(index + 1) + '/' + steps.length"></span>
            </div>

            {{-- Kenali huruf baharu --}}
            <template x-if="step.type === 'intro'">
                <div class="text-center">
                    <div class="inline-block text-xs font-bold px-3 py-1 rounded-full mb-4" style="background:#EDE0D0;color:#A6845E;">✨ HURUF BARU</div>
                    <button type="button" @click="say(step.letter)" class="block mx-auto font-arabic select-none" style="font-size:9rem;line-height:1.35;" :style="'color:' + step.letter.color" x-text="step.letter.arabic"></button>
                    <div class="text-3xl font-bold mb-1" style="color:#3D2C1E;" x-text="step.letter.name"></div>
                    <div class="text-xs mb-4" :style="'color:' + step.letter.color" x-text="'Keluarga ' + step.letter.family.replace('Keluarga ', '')"></div>
                    <div class="rounded-2xl p-4 mb-6 text-left text-sm" style="background:#F5F0EB;color:#3D2C1E;">💡 <span x-text="step.letter.mnemonic"></span></div>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="say(step.letter)" class="py-4 rounded-2xl font-semibold border-2" style="border-color:#C9A882;color:#A6845E;">🔊 Dengar lagi</button>
                        <button type="button" @click="next()" class="py-4 rounded-2xl font-bold text-white" style="background:#A6845E;">Dah ingat →</button>
                    </div>
                    <p class="text-xs mt-3" style="color:#B8A090;">Sebut namanya dengan kuat 2 kali.</p>
                </div>
            </template>

            {{-- Soalan --}}
            <template x-if="step.type === 'q'">
                <div>
                    <p class="text-center text-sm mb-2" style="color:#8B6F5E;" x-text="prompt"></p>

                    <div class="text-center mb-6" style="min-height:11rem;">
                        <template x-if="step.mode === 'lihat' || step.mode === 'bentuk'">
                            <div class="font-arabic select-none" style="font-size:8rem;line-height:1.35;" :style="'color:' + (answered ? step.letter.color : '#3D2C1E')" x-text="step.mode === 'bentuk' ? step.form : step.letter.arabic"></div>
                        </template>
                        <template x-if="step.mode === 'dengar'">
                            <button type="button" @click="say(step.letter)" class="w-32 h-32 mt-4 rounded-full text-5xl border-4 active:scale-95 transition" style="background:#EDE0D0;border-color:#C9A882;">🔊</button>
                        </template>
                        <template x-if="step.mode === 'nama'">
                            <div class="text-5xl font-bold pt-12" style="color:#3D2C1E;" x-text="step.letter.name"></div>
                        </template>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <template x-for="(opt, i) in step.options" :key="index + '-' + i">
                            <button type="button" @click="answer(opt)" class="rounded-2xl border-2 font-semibold transition active:scale-95"
                                    style="min-height:5.5rem;" :style="optionStyle(opt)">
                                <span x-show="answerIsGlyph" class="font-arabic" style="font-size:2.8rem;line-height:1.5;" :style="{ color: answered && opt.id === step.letter.id ? opt.color : '' }" x-text="opt.arabic"></span>
                                <span x-show="!answerIsGlyph" class="text-lg" x-text="opt.name"></span>
                            </button>
                        </template>
                    </div>

                    <div x-show="answered" x-transition class="mt-5">
                        <div class="rounded-2xl p-4 mb-4" :style="correct ? 'background:rgba(122,158,126,0.15)' : 'background:rgba(196,123,106,0.12)'">
                            <div class="font-bold mb-1" :style="correct ? 'color:#3F6B45' : 'color:#A0503F'" x-text="feedback"></div>
                            <div class="flex items-center gap-3">
                                <span class="font-arabic text-4xl" style="line-height:1.5;" :style="'color:' + step.letter.color" x-text="step.letter.arabic"></span>
                                <span class="text-sm" style="color:#3D2C1E;" x-text="correct ? step.letter.name : step.letter.mnemonic"></span>
                            </div>
                        </div>
                        <button type="button" @click="next()" class="w-full py-4 rounded-2xl font-bold text-white" style="background:#A6845E;">Teruskan →</button>
                    </div>
                </div>
            </template>
        </div>
    </template>

    {{-- ── Selesai: tunjuk apa yang bertambah baik ─────────────────── --}}
    <template x-if="done">
        <div class="text-center">
            <div class="text-6xl mb-2">🎉</div>
            <h1 class="text-2xl font-bold mb-1" style="color:#3D2C1E;" x-text="sessionsToday > 1 ? 'Sesi ke-' + sessionsToday + ' hari ini!' : 'Siap 5 minit!'"></h1>
            <p class="text-sm mb-6" style="color:#8B6F5E;" x-text="summary.correct + ' daripada ' + summary.total + ' betul' + (summary.avgMs ? ' · purata ' + (summary.avgMs / 1000).toFixed(1) + ' saat' : '')"></p>

            <template x-if="summary.newLetters.length">
                <div class="rounded-2xl p-4 mb-3" style="background:#F5F0EB;">
                    <div class="text-xs font-bold mb-2" style="color:#A6845E;">✨ HURUF BARU DALAM KOLEKSI</div>
                    <div class="flex justify-center gap-4" style="direction:rtl;">
                        <template x-for="l in summary.newLetters" :key="l.id">
                            <div><div class="font-arabic text-5xl" style="line-height:1.5;" :style="'color:' + l.color" x-text="l.arabic"></div><div class="text-xs" style="color:#8B6F5E;" x-text="l.name"></div></div>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="summary.levelUps.length">
                <div class="rounded-2xl p-4 mb-3" style="background:rgba(122,158,126,0.12);">
                    <div class="text-xs font-bold mb-2" style="color:#3F6B45;">⬆️ NAIK TAHAP</div>
                    <div class="flex flex-wrap justify-center gap-3">
                        <template x-for="u in summary.levelUps" :key="u.letter.id">
                            <div class="text-sm"><span class="font-arabic text-3xl align-middle" :style="'color:' + u.letter.color" x-text="u.letter.arabic"></span> → <strong x-text="Kemajuan.LEVELS[u.to].label"></strong></div>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="summary.speed">
                <div class="rounded-2xl p-4 mb-3" style="background:#EDE0D0;">
                    <div class="text-sm" style="color:#3D2C1E;">⚡ Awak kenal <span class="font-arabic text-2xl align-middle" :style="'color:' + summary.speed.letter.color" x-text="summary.speed.letter.arabic"></span>
                        dalam <strong x-text="(summary.speed.now / 1000).toFixed(1) + 's'"></strong> — dulu <span x-text="(summary.speed.then / 1000).toFixed(1) + 's'"></span></div>
                </div>
            </template>

            <div class="rounded-2xl p-4 mb-6" style="background:#F5F0EB;">
                <div class="text-xs font-bold mb-2" style="color:#8B6F5E;" x-text="'MINGGU INI · ' + weekInfo.done + '/' + weekInfo.target + ' HARI' + (weekInfo.reached ? ' 🏆' : '')"></div>
                <div class="flex justify-center gap-2">
                    <template x-for="(d, i) in weekInfo.days" :key="i">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold"
                             :style="d.done ? 'background:#7A9E7E;color:#fff' : (d.today ? 'border:2px solid #C9A882;color:#A6845E' : 'background:#E2D5C8;color:#B8A090')" x-text="d.label"></div>
                    </template>
                </div>
            </div>

            <div class="grid gap-3">
                <button type="button" @click="restart()" class="w-full py-4 rounded-2xl font-bold text-white text-lg" style="background:#A6845E;">⚡ Satu lagi 5 minit</button>
                <a href="{{ route('home') }}" class="w-full py-3 rounded-2xl font-semibold border-2" style="border-color:#E2D5C8;color:#8B6F5E;">Cukup untuk sekarang</a>
            </div>
            <x-nasihat tag="sesi" class="mt-6 text-left" />
        </div>
    </template>
</div>

<script>
function kilatSession() {
    const NEW_PER_SESSION = 2;
    const NEW_PER_DAY = 6;
    const REVIEW_MAX = 6;
    const MIN_ITEMS = 8;
    const FAST_MS = 3000;

    const data = window._kilat;
    const byId = Object.fromEntries(data.letters.map(l => [l.id, l]));
    const byChar = Object.fromEntries(data.letters.map(l => [l.arabic, l]));
    const shuffle = (a) => { a = [...a]; for (let i = a.length - 1; i > 0; i--) { const j = Math.floor(Math.random() * (i + 1)); [a[i], a[j]] = [a[j], a[i]]; } return a; };
    const startOfToday = () => { const d = new Date(); d.setHours(0, 0, 0, 0); return d.getTime(); };

    // 3 pilihan salah: utamakan huruf serupa (bentuk / bunyi), lengkapkan dengan rawak.
    function distractors(letter, families) {
        const similar = families.filter(f => f.includes(letter.arabic)).flat().filter(c => c !== letter.arabic && byChar[c]);
        const picked = shuffle([...new Set(similar)]).slice(0, 3).map(c => byChar[c]);
        const rest = shuffle(data.letters.filter(l => l.id !== letter.id && !picked.includes(l)));
        return [...picked, ...rest].slice(0, 3);
    }

    function question(letter, mode) {
        const families = mode === 'dengar' ? [...data.soundalikes, ...data.lookalikes] : data.lookalikes;
        const q = { type: 'q', letter, mode, options: shuffle([letter, ...distractors(letter, families)]) };
        if (mode === 'bentuk') q.form = letter.forms[Math.floor(Math.random() * letter.forms.length)];
        return q;
    }

    // Pelbagaikan jenis soalan supaya tidak bosan; elak jenis sama berturut-turut.
    let lastMode = null;
    function pickMode(letter, isNew) {
        const modes = isNew ? ['lihat', 'dengar'] : ['lihat', 'dengar', 'nama', ...(letter.forms.length ? ['bentuk'] : [])];
        const choices = modes.filter(m => m !== lastMode);
        lastMode = choices[Math.floor(Math.random() * choices.length)];
        return lastMode;
    }

    function buildSteps() {
        const sm2 = HafalanSRS.load();
        const introducedToday = Object.values(sm2).filter(r => (r.firstSeen ?? 0) >= startOfToday()).length;

        const due = data.letters.filter(l => sm2[l.id] && sm2[l.id].nextReview <= Date.now())
            .sort((a, b) => sm2[a.id].nextReview - sm2[b.id].nextReview).slice(0, REVIEW_MAX);
        // Terlalu banyak ulang kaji tertunggak → tiada huruf baru dulu (elak beban).
        const newCount = due.length > 8 ? 0 : Math.max(0, Math.min(NEW_PER_SESSION, NEW_PER_DAY - introducedToday));
        const fresh = data.letters.filter(l => !sm2[l.id]).sort((a, b) => a.plan - b.plan).slice(0, newCount);

        // Isi hingga cukup dengan huruf yang paling lemah (latihan; tidak ubah jadual kecuali salah).
        const seen = data.letters.filter(l => sm2[l.id] && !due.includes(l))
            .sort((a, b) => (sm2[a.id].easiness ?? 2.5) - (sm2[b.id].easiness ?? 2.5) || (sm2[b.id].lapses ?? 0) - (sm2[a.id].lapses ?? 0));
        const practice = seen.slice(0, Math.max(0, MIN_ITEMS - due.length - fresh.length * 2));

        const reviews = shuffle([...due.map(l => ({ l, srs: true })), ...practice.map(l => ({ l, srs: false }))]);
        const steps = [];
        fresh.forEach(l => {
            steps.push({ type: 'intro', letter: l });
            steps.push({ ...question(l, pickMode(l, true)), srs: true, isNew: true });
            // Soalan kedua huruf baru diletak selepas beberapa soalan lain (jarak dalam sesi).
        });
        reviews.forEach(({ l, srs }) => steps.push({ ...question(l, pickMode(l, false)), srs }));
        fresh.forEach(l => {
            const at = Math.min(steps.length, steps.findIndex(s => s.letter === l && s.type === 'q') + 3);
            steps.splice(at, 0, { ...question(l, pickMode(l, true)), srs: false, isNew: true });
        });
        return { steps, fresh, sm2Before: sm2 };
    }

    return {
        steps: [], index: 0, done: false,
        answered: false, selected: null, correct: false, shownAt: 0, feedback: '',
        graded: {}, results: [], fresh: [], before: {},
        summary: { correct: 0, total: 0, avgMs: null, newLetters: [], levelUps: [], speed: null },
        weekInfo: Kemajuan.week(), sessionsToday: 0,

        get step() { return this.steps[this.index]; },
        get answerIsGlyph() { return this.step.mode === 'dengar' || this.step.mode === 'nama'; },
        get prompt() {
            return { lihat: 'Huruf apakah ini?', dengar: 'Dengar — huruf yang mana?', nama: 'Cari huruf ini', bentuk: 'Huruf apakah ini? (bentuk bersambung)' }[this.step.mode];
        },

        start() {
            const { steps, fresh, sm2Before } = buildSteps();
            this.steps = steps;
            this.fresh = fresh;
            this.before = Kemajuan.levels(sm2Before);
            this.enter();
        },
        restart() {
            Object.assign(this, { index: 0, done: false, graded: {}, results: [], weekInfo: Kemajuan.week() });
            this.start();
        },

        say(letter) { speakArabic(letter.arabic); },

        enter() {
            Object.assign(this, { answered: false, selected: null, correct: false, feedback: '' });
            this.shownAt = performance.now();
            const s = this.step;
            if (!s) return;
            if (s.type === 'intro' || s.mode === 'dengar') this.$nextTick(() => this.say(s.letter));
        },

        answer(opt) {
            if (this.answered) return;
            const s = this.step;
            const ms = performance.now() - this.shownAt;
            this.answered = true;
            this.selected = opt;
            this.correct = opt.id === s.letter.id;
            this.results.push({ correct: this.correct, ms: this.correct ? ms : null });
            if (s.mode !== 'dengar') this.say(s.letter); // dengar sebutan selepas jawab — bukan sebelum

            if (this.correct) {
                Kemajuan.recordSpeed(s.letter.id, ms);
                this.feedback = ms < FAST_MS ? `Betul! ⚡ ${(ms / 1000).toFixed(1)}s` : 'Betul! 👍';
            } else {
                this.feedback = `Hampir — ini ${s.letter.name}`;
                // Ulang huruf ini sekali lagi dalam sesi yang sama, sedikit kemudian.
                const at = Math.min(this.steps.length, this.index + 3);
                this.steps.splice(at, 0, { ...question(s.letter, pickMode(s.letter, false)), srs: false });
            }

            // Jadual ulang kaji: dikemas kini sekali bagi setiap huruf setiap sesi (jawapan pertama).
            if (!this.graded[s.letter.id] && (s.srs || !this.correct)) {
                this.graded[s.letter.id] = true;
                HafalanSRS.review(s.letter.id, !this.correct ? 1 : (ms < FAST_MS && !s.isNew ? 5 : 4));
            }
        },

        optionStyle(opt) {
            const base = 'background:#FFFFFF;border-color:#E2D5C8;color:#3D2C1E;';
            if (!this.answered) return base;
            if (opt.id === this.step.letter.id) return 'background:rgba(122,158,126,0.18);border-color:#7A9E7E;color:#3D2C1E;';
            if (opt === this.selected) return 'background:rgba(196,123,106,0.15);border-color:#C47B6A;color:#3D2C1E;';
            return base + 'opacity:0.45;';
        },

        next() {
            if (this.index + 1 < this.steps.length) {
                this.index++;
                this.enter();
            } else {
                this.finish();
            }
        },

        finish() {
            HafalanSRS.markStudyDay();
            this.sessionsToday = Kemajuan.addSession();
            const after = Kemajuan.levels();
            const ranks = Kemajuan.LEVELS;
            const timed = this.results.filter(r => r.ms);
            const speed = Kemajuan.speedStats().best;

            this.summary = {
                correct: this.results.filter(r => r.correct).length,
                total: this.results.length,
                avgMs: timed.length ? timed.reduce((a, r) => a + r.ms, 0) / timed.length : null,
                newLetters: this.fresh,
                levelUps: Object.entries(after)
                    .filter(([id, lvl]) => this.before[id] && ranks[lvl].rank > ranks[this.before[id]].rank)
                    .map(([id, lvl]) => ({ letter: byId[id], to: lvl })),
                speed: speed ? { ...speed, letter: byId[speed.id] } : null,
            };
            this.weekInfo = Kemajuan.week();
            this.done = true;
        },
    };
}
</script>

</x-layouts.app>
