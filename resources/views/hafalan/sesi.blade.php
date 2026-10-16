<x-layouts.app title="Sesi Hafalan">

<script>
window._hafalanCards = @json($cards);
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
                <span x-text="nextDue ? 'Ulang kaji seterusnya: ' + nextDue : 'Tiada ulang kaji dijadualkan'"></span>
            </div>

            <x-nasihat tag="sesi" class="mb-6 text-left max-w-md mx-auto" />

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
                    <span x-show="card.isNew" class="ml-1 text-xs px-2 py-0.5 rounded-full" style="background:#EDE0D0;color:#A6845E;">baru</span>
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
                             x-text="card.front"></div>
                        <div x-show="card.front !== card.arabic" class="text-xs mb-2" style="color: #A6845E;">bentuk bersambung</div>
                        <div class="text-sm" style="color: #B8A090;">Sebut nama huruf dengan kuat, kemudian tekan →</div>
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

                        {{-- All 4 forms: bebas / awal / tengah / akhir --}}
                        <div class="grid grid-cols-4 gap-2 mb-4 text-center">
                            <template x-for="(f, i) in card.all_forms" :key="i">
                                <div class="rounded-lg py-1" :style="f === card.front ? 'background:#EDE0D0' : ''">
                                    <div class="font-arabic text-3xl" style="color: #A6845E;" x-text="f"></div>
                                    <div class="text-[10px]" style="color: #8B6F5E;" x-text="['bebas','awal','tengah','akhir'][i]"></div>
                                </div>
                            </template>
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
                    <div class="text-xs opacity-60 mt-0.5">Ulang sekarang</div>
                </button>
                <button @click="rate(3)"
                        class="py-4 rounded-xl font-semibold border-2 transition-all active:scale-95"
                        style="background-color: rgba(212,168,83,0.12); border-color: #D4A853; color: #D4A853;">
                    <div class="text-lg mb-0.5">🤔</div>
                    <div class="text-xs">Ragu-ragu</div>
                    <div class="text-xs opacity-60 mt-0.5" x-text="'Ulang ' + nextInterval(card.id, 3) + ' hari'"></div>
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
                <p class="text-xs" style="color: #B8A090;">Jangan balik kad sebelum cuba ingat — usaha mengingat itulah yang menguatkan hafalan.</p>
            </div>
        </div>
    </template>

    {{-- Empty state --}}
    <template x-if="!done && queue.length === 0">
        <div class="text-center py-16">
            <div class="text-5xl mb-4">🌙</div>
            <h2 class="text-xl font-bold mb-2" style="color: #3D2C1E;">Tiada yang perlu diulang</h2>
            <p class="text-sm mb-6" style="color: #8B6F5E;">Semua huruf dalam kumpulan ini sudah dijadualkan. Sementara itu, cuba kuiz Dengar Bunyi atau Bentuk Sambung.</p>
            <a href="{{ route('hafalan') }}"
               class="inline-block px-6 py-3 rounded-xl font-semibold text-white"
               style="background-color: #A6845E;">← Balik</a>
        </div>
    </template>

</div>

<script>
function hafalanSesi() {
    // Huruf baru setiap sesi dihadkan supaya tidak terbeban; ulang kaji sentiasa didahulukan.
    const NEW_PER_SESSION = @json($newLimit);
    // Dari pelan: hanya perkenalkan huruf hari ini (kosong = ikut susunan abjad).
    const NEW_IDS = @json($newIds);

    function shuffle(arr) {
        for (let i = arr.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [arr[i], arr[j]] = [arr[j], arr[i]];
        }
        return arr;
    }

    // Kad yang pernah dilihat diuji dalam bentuk bersambung secara rawak (awal/tengah/akhir)
    // supaya hafalan tidak terikat pada bentuk bebas sahaja.
    function withFront(card, isNew) {
        const useForm = !isNew && card.forms.length > 0 && Math.random() < 0.5;
        const front = useForm ? card.forms[Math.floor(Math.random() * card.forms.length)] : card.arabic;
        return { ...card, front, isNew };
    }

    function buildQueue(cards) {
        const sm2 = HafalanSRS.load();
        const due = shuffle(cards.filter(c => HafalanSRS.isDue(c.id, sm2))).map(c => withFront(c, false));
        const fresh = cards
            .filter(c => !sm2[c.id] && (NEW_IDS.length === 0 || NEW_IDS.includes(c.id)))
            .slice(0, NEW_PER_SESSION)
            .map(c => withFront(c, true));
        return [...due, ...fresh];
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
            this.queue = buildQueue(window._hafalanCards || []);
        },

        flip() {
            if (this.flipped) return;
            this.flipped = true;
            if (this.soundOn) speakArabic(this.card.arabic);
        },

        nextInterval(id, quality) {
            return HafalanSRS.previewInterval(id, quality);
        },

        rate(quality) {
            HafalanSRS.review(this.card.id, quality);

            if (quality >= 5) this.summary.hafal++;
            else if (quality >= 3) this.summary.ragu++;
            else this.summary.takIngat++;

            // Tak ingat → ulang dalam sesi yang sama, 3 kad kemudian (bukan terus di hujung).
            if (quality < 3) {
                const pos = Math.min(this.queue.length, this.cardIndex + 4);
                this.queue.splice(pos, 0, withFront(this.card, false));
            }

            if (this.cardIndex + 1 >= this.queue.length) {
                this.finishSession();
            } else {
                this.cardIndex++;
                this.flipped = false;
            }
        },

        finishSession() {
            this.done = true;
            HafalanSRS.markStudyDay();
            const upcoming = Object.values(HafalanSRS.load())
                .map(r => r.nextReview)
                .filter(t => t > Date.now())
                .sort((a, b) => a - b);
            if (upcoming.length > 0) {
                this.nextDue = new Date(upcoming[0]).toLocaleDateString('ms-MY', { weekday: 'long', day: 'numeric', month: 'long' });
            }
        },

        restart() {
            this.queue = buildQueue(window._hafalanCards || []);
            this.cardIndex = 0;
            this.flipped = false;
            this.done = false;
            this.summary = { hafal: 0, ragu: 0, takIngat: 0 };
        }
    };
}
</script>

</x-layouts.app>
