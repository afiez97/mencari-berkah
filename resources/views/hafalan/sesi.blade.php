<x-layouts.app title="Sesi Hafalan">

<script>
window._hafalanCards = @json($letters->map(fn($l) => [
    'id'           => $l->id,
    'arabic'       => $l->arabic,
    'name'         => $l->name,
    'transliteration' => $l->transliteration,
    'sound'        => $l->sound,
    'mnemonic'     => $l->mnemonic,
    'visual'       => $l->visual_description,
    'example_word' => $l->example_word,
    'example_tr'   => $l->example_translation,
    'form_isolated'=> $l->form_isolated,
]));
</script>

<style>
.card-wrap { perspective: 1000px; }
.card-inner {
    position: relative;
    width: 100%;
    transform-style: preserve-3d;
    transition: transform 0.5s cubic-bezier(.4,2,.6,1);
}
.card-inner.flipped { transform: rotateY(180deg); }
.card-front, .card-back {
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    width: 100%;
}
.card-back { transform: rotateY(180deg); position: absolute; top: 0; left: 0; }
</style>

<div x-data="hafalanSesi()" x-init="init()">

    {{-- Done Screen --}}
    <template x-if="done">
        <div class="text-center py-8">
            <div class="text-6xl mb-4">🎉</div>
            <h2 class="text-2xl font-bold mb-2" style="color: #3D2C1E;">Sesi Selesai!</h2>
            <p class="text-sm mb-6" style="color: #8B6F5E;">Sistem telah jadualkan ulang kaji berdasarkan prestasi anda.</p>

            <div class="grid grid-cols-3 gap-3 mb-8 max-w-xs mx-auto">
                <div class="rounded-xl p-4 border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                    <div class="text-2xl font-bold" style="color: #7A9E7E;" x-text="summary.hafal"></div>
                    <div class="text-xs" style="color: #8B6F5E;">Hafal</div>
                </div>
                <div class="rounded-xl p-4 border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                    <div class="text-2xl font-bold" style="color: #D4A853;" x-text="summary.ragu"></div>
                    <div class="text-xs" style="color: #8B6F5E;">Ragu</div>
                </div>
                <div class="rounded-xl p-4 border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                    <div class="text-2xl font-bold" style="color: #C47B6A;" x-text="summary.takIngat"></div>
                    <div class="text-xs" style="color: #8B6F5E;">Tak Ingat</div>
                </div>
            </div>

            <div class="text-sm p-4 rounded-xl mb-6" style="background-color: #EDE0D0; color: #8B6F5E;">
                <span x-text="'Ulang kaji seterusnya: huruf \'' + (nextDue ? nextDue : 'tiada') + '\' dijadualkan'"></span>
            </div>

            <div class="flex gap-3 justify-center">
                <a href="{{ route('hafalan') }}"
                   class="px-6 py-3 rounded-xl font-semibold border transition-all"
                   style="background-color: #F5F0EB; border-color: #E2D5C8; color: #3D2C1E;">
                    ← Balik
                </a>
                <button @click="restart()"
                        class="px-6 py-3 rounded-xl font-semibold text-white transition-opacity hover:opacity-90"
                        style="background-color: #A6845E;">
                    Ulang Semula
                </button>
            </div>
        </div>
    </template>

    {{-- Session Screen --}}
    <template x-if="!done && queue.length > 0">
        <div>
            {{-- Header --}}
            <div class="flex items-center justify-between mb-4">
                <a href="{{ route('hafalan') }}" class="text-sm" style="color: #C9A882;">← Keluar</a>
                <span class="text-sm font-medium" style="color: #8B6F5E;">
                    <span x-text="cardIndex + 1"></span> / <span x-text="queue.length"></span>
                </span>
                <button @click="soundOn = !soundOn"
                        class="text-xs px-2 py-1 rounded-lg border"
                        :style="soundOn ? 'background:#EDE0D0;border-color:#C9A882;color:#A6845E' : 'background:#F5F0EB;border-color:#E2D5C8;color:#B8A090'"
                        x-text="soundOn ? '🔊' : '🔇'"></button>
            </div>

            {{-- Progress --}}
            <div class="w-full rounded-full h-1.5 mb-6" style="background-color: #E2D5C8;">
                <div class="h-1.5 rounded-full transition-all duration-500" style="background-color: #C9A882;"
                     :style="'width:' + ((cardIndex / queue.length) * 100) + '%'"></div>
            </div>

            {{-- Flashcard --}}
            <div class="card-wrap mb-6" style="min-height: 320px;">
                <div class="card-inner" :class="{ flipped: flipped }">

                    {{-- FRONT --}}
                    <div class="card-front rounded-2xl border p-6 flex flex-col items-center justify-center cursor-pointer"
                         style="background-color: #F5F0EB; border-color: #E2D5C8; min-height: 320px;"
                         @click="flip()">
                        <div class="font-arabic mb-4 select-none"
                             style="font-size: clamp(6rem, 20vw, 9rem); color: #A6845E; line-height: 1.2;"
                             x-text="card.arabic"></div>
                        <div class="text-sm" style="color: #B8A090;">Tekan untuk balik kad →</div>
                    </div>

                    {{-- BACK --}}
                    <div class="card-back rounded-2xl border p-6"
                         style="background-color: #F5F0EB; border-color: #C9A882; min-height: 320px;">

                        {{-- Name + sound --}}
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="text-2xl font-bold" style="color: #3D2C1E;" x-text="card.name"></div>
                                <div class="text-sm font-mono" style="color: #A6845E;" x-text="card.transliteration + ' · ' + card.sound"></div>
                            </div>
                            <button @click="speakArabic(card.arabic)"
                                    class="w-12 h-12 rounded-full border flex items-center justify-center text-xl transition-all hover:opacity-80"
                                    style="background-color: #EDE0D0; border-color: #C9A882;">
                                🔊
                            </button>
                        </div>

                        {{-- Isolated form big --}}
                        <div class="text-center mb-4">
                            <span class="font-arabic text-5xl" style="color: #C9A882;" x-text="card.form_isolated"></span>
                        </div>

                        {{-- Mnemonic --}}
                        <div class="rounded-xl p-3 mb-3" style="background-color: #EDE0D0; border-left: 3px solid #C9A882;">
                            <div class="text-xs font-semibold mb-1" style="color: #A6845E;">💡 Cara Ingat</div>
                            <p class="text-sm" style="color: #3D2C1E;" x-text="card.mnemonic"></p>
                        </div>

                        {{-- Example --}}
                        <div class="flex items-center justify-between rounded-xl px-3 py-2"
                             style="background-color: #F5F0EB; border: 1px solid #E2D5C8;">
                            <span class="text-xs" style="color: #8B6F5E;" x-text="card.example_tr"></span>
                            <span class="font-arabic text-xl" style="color: #A6845E;" x-text="card.example_word"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rating buttons — only show after flip --}}
            <div x-show="flipped" x-transition class="grid grid-cols-3 gap-3">
                <button @click="rate(1)"
                        class="py-4 rounded-xl font-semibold border-2 transition-all active:scale-95"
                        style="background-color: rgba(196,123,106,0.12); border-color: #C47B6A; color: #C47B6A;">
                    <div class="text-lg mb-0.5">😟</div>
                    <div class="text-xs">Tak Ingat</div>
                    <div class="text-xs opacity-60 mt-0.5">Ulang 1 hari</div>
                </button>
                <button @click="rate(3)"
                        class="py-4 rounded-xl font-semibold border-2 transition-all active:scale-95"
                        style="background-color: rgba(212,168,83,0.12); border-color: #D4A853; color: #D4A853;">
                    <div class="text-lg mb-0.5">🤔</div>
                    <div class="text-xs">Ragu-ragu</div>
                    <div class="text-xs opacity-60 mt-0.5">Ulang 3 hari</div>
                </button>
                <button @click="rate(5)"
                        class="py-4 rounded-xl font-semibold border-2 transition-all active:scale-95"
                        style="background-color: rgba(122,158,126,0.12); border-color: #7A9E7E; color: #7A9E7E;">
                    <div class="text-lg mb-0.5">✅</div>
                    <div class="text-xs">Hafal!</div>
                    <div class="text-xs opacity-60 mt-0.5" x-text="'Ulang ' + nextInterval(card.id, 5) + ' hari'">Ulang ~7 hari</div>
                </button>
            </div>

            {{-- Hint before flip --}}
            <div x-show="!flipped" class="text-center mt-4">
                <p class="text-xs" style="color: #B8A090;">Cuba ingat nama dan sebutan huruf ini sebelum balik kad</p>
            </div>
        </div>
    </template>

    {{-- Empty state --}}
    <template x-if="!done && queue.length === 0">
        <div class="text-center py-16">
            <div class="text-5xl mb-4">🌙</div>
            <h2 class="text-xl font-bold mb-2" style="color: #3D2C1E;">Tiada yang perlu diulang</h2>
            <p class="text-sm mb-6" style="color: #8B6F5E;">Semua huruf dalam kumpulan ini sudah dijadualkan untuk masa hadapan. Datang semula esok!</p>
            <a href="{{ route('hafalan') }}"
               class="inline-block px-6 py-3 rounded-xl font-semibold text-white"
               style="background-color: #A6845E;">← Balik</a>
        </div>
    </template>

</div>

<script>
function hafalanSesi() {
    const SM2_KEY = 'arabic_sm2';

    function loadSM2() {
        try { return JSON.parse(localStorage.getItem(SM2_KEY) || '{}'); } catch { return {}; }
    }
    function saveSM2(data) {
        localStorage.setItem(SM2_KEY, JSON.stringify(data));
    }

    // SM-2 algorithm
    function sm2Update(record, quality) {
        let { repetitions = 0, easiness = 2.5, interval = 1 } = record || {};
        if (quality < 3) {
            repetitions = 0;
            interval = 1;
        } else {
            if (repetitions === 0) interval = 1;
            else if (repetitions === 1) interval = 6;
            else interval = Math.round(interval * easiness);
            repetitions++;
        }
        easiness = Math.max(1.3, easiness + 0.1 - (5 - quality) * (0.08 + (5 - quality) * 0.02));
        const nextReview = Date.now() + interval * 86400000;
        return { repetitions, easiness, interval, nextReview };
    }

    function isDue(id) {
        const sm2 = loadSM2();
        const r = sm2[id];
        return !r || r.nextReview <= Date.now();
    }

    function buildQueue(cards) {
        const due = cards.filter(c => isDue(c.id));
        // Shuffle due cards
        for (let i = due.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [due[i], due[j]] = [due[j], due[i]];
        }
        return due;
    }

    return {
        queue: [],
        cardIndex: 0,
        flipped: false,
        done: false,
        soundOn: true,
        summary: { hafal: 0, ragu: 0, takIngat: 0 },
        nextDue: null,

        get card() { return this.queue[this.cardIndex] || {}; },

        init() {
            const allCards = window._hafalanCards || [];
            this.queue = buildQueue(allCards);
            if (this.queue.length > 0 && this.soundOn) {
                this.$nextTick(() => speakArabic(this.queue[0].arabic));
            }
        },

        flip() {
            if (this.flipped) return;
            this.flipped = true;
            if (this.soundOn) speakArabic(this.card.arabic);
        },

        nextInterval(id, quality) {
            const sm2 = loadSM2();
            const updated = sm2Update(sm2[id], quality);
            return updated.interval;
        },

        rate(quality) {
            const sm2 = loadSM2();
            sm2[this.card.id] = sm2Update(sm2[this.card.id], quality);
            saveSM2(sm2);

            if (quality >= 5) this.summary.hafal++;
            else if (quality >= 3) this.summary.ragu++;
            else this.summary.takIngat++;

            // Cards rated < 3 go back into queue (requeue at end)
            if (quality < 3) {
                this.queue.push({ ...this.card });
            }

            if (this.cardIndex + 1 >= this.queue.length) {
                this.finishSession(sm2);
            } else {
                this.cardIndex++;
                this.flipped = false;
                this.$nextTick(() => {
                    if (this.soundOn) speakArabic(this.card.arabic);
                });
            }
        },

        finishSession(sm2) {
            this.done = true;
            // Find next due date
            const upcoming = Object.values(sm2)
                .map(r => r.nextReview)
                .filter(t => t > Date.now())
                .sort((a, b) => a - b);
            if (upcoming.length > 0) {
                const d = new Date(upcoming[0]);
                this.nextDue = d.toLocaleDateString('ms-MY', { weekday: 'long', day: 'numeric', month: 'long' });
            }
            // Update streak
            const today = new Date().toDateString();
            let streak = {};
            try { streak = JSON.parse(localStorage.getItem('arabic_hafalan_streak') || '{}'); } catch {}
            if (streak.lastDate !== today) {
                const yesterday = new Date(Date.now() - 86400000).toDateString();
                streak.count = streak.lastDate === yesterday ? (streak.count || 0) + 1 : 1;
                streak.lastDate = today;
                streak.history = streak.history || [];
                if (!streak.history.includes(today)) streak.history.push(today);
                localStorage.setItem('arabic_hafalan_streak', JSON.stringify(streak));
            }
        },

        restart() {
            const allCards = window._hafalanCards || [];
            this.queue = buildQueue(allCards);
            this.cardIndex = 0;
            this.flipped = false;
            this.done = false;
            this.summary = { hafal: 0, ragu: 0, takIngat: 0 };
        }
    };
}
</script>

</x-layouts.app>
