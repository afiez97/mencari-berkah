<x-layouts.app title="Latih Baris — {{ $lessonTitle }}">

<script>
    window._barisQuestions = @json($questions);
</script>

<div x-data="{
    lesson: @js($lesson),
    questions: window._barisQuestions,
    current: 0,
    selected: null,
    correct: 0,
    finished: false,
    mistakes: [],

    get q() { return this.questions[this.current]; },
    get answered() { return this.selected !== null; },
    get percentage() { return Math.round(this.correct / this.questions.length * 100); },

    init() { this.$nextTick(() => this.speakIfHear()); },
    speakIfHear() { if (this.q.mode === 'hear') speakArabic(this.q.text); },

    choose(opt) {
        if (this.answered) return;
        this.selected = opt;
        speakArabic(this.q.text);
        HafalanSRS.markStudyDay();
        if (opt === this.q.answer) this.correct++;
        else if (!this.mistakes.some(m => m.text === this.q.text)) this.mistakes.push({ text: this.q.text, rumi: this.q.rumi });
    },
    optionStyle(opt) {
        if (!this.answered) return 'background-color: #F5F0EB; border-color: #E2D5C8; color: #3D2C1E;';
        if (opt === this.q.answer) return 'background-color: rgba(122,158,126,0.2); border-color: #7A9E7E; color: #3D2C1E;';
        if (opt === this.selected) return 'background-color: rgba(196,123,106,0.2); border-color: #C47B6A; color: #3D2C1E;';
        return 'background-color: #F5F0EB; border-color: #E2D5C8; color: #8B6F5E; opacity: 0.6;';
    },
    next() {
        if (this.current < this.questions.length - 1) {
            this.current++;
            this.selected = null;
            this.$nextTick(() => this.speakIfHear());
            return;
        }
        this.finished = true;
        try {
            const best = JSON.parse(localStorage.getItem('arabic_baris') || '{}');
            best[this.lesson] = Math.max(this.percentage, best[this.lesson] ?? 0);
            localStorage.setItem('arabic_baris', JSON.stringify(best));
        } catch {}
        window.HafalanSync?.schedule();
    },
}">

    <template x-if="!finished">
        <div>
            <div class="flex justify-between items-center text-sm mb-2" style="color: #8B6F5E;">
                <a href="{{ route('baris') }}" style="color: #C9A882;">← Baris</a>
                <span><span x-text="current + 1"></span> / <span x-text="questions.length"></span> · <span x-text="correct"></span> betul</span>
            </div>
            <div class="w-full rounded-full h-2 mb-8" style="background-color: #E2D5C8;">
                <div class="h-2 rounded-full transition-all" style="background-color: #C9A882;" :style="'width:' + ((current + 1) / questions.length * 100) + '%'"></div>
            </div>

            <div class="text-center mb-8">
                <template x-if="q.mode === 'see'">
                    <div class="font-arabic select-none" style="font-size: clamp(5rem, 20vw, 8rem); color: #A6845E; line-height: 1.6;" x-text="q.text"></div>
                </template>
                <template x-if="q.mode === 'hear'">
                    <button type="button" @click="speakArabic(q.text)" class="w-28 h-28 rounded-full border-2 text-5xl my-4 active:scale-95"
                            style="background-color: #EDE0D0; border-color: #C9A882;">🔊</button>
                </template>
                <p class="text-sm" style="color: #8B6F5E;" x-text="q.mode === 'see' ? 'Bagaimana suku kata ini dibaca?' : 'Pilih suku kata yang anda dengar'"></p>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-6">
                <template x-for="(opt, i) in q.options" :key="current + '-' + i">
                    <button type="button" @click="choose(opt)" class="py-4 rounded-xl border font-semibold" :style="optionStyle(opt)" style="min-height: 72px;">
                        <span :class="q.mode === 'hear' ? 'font-arabic text-4xl' : 'text-xl'" :style="q.mode === 'hear' ? 'line-height:1.7' : ''" x-text="opt"></span>
                    </button>
                </template>
            </div>

            <div x-show="answered" class="rounded-xl p-4 mb-4 flex items-center justify-between" style="background-color: #EDE0D0;">
                <div class="flex items-center gap-3">
                    <span class="font-arabic text-4xl" style="color: #A6845E; line-height: 1.6;" x-text="q.text"></span>
                    <div>
                        <div class="font-semibold" style="color: #3D2C1E;" x-text="q.rumi"></div>
                        <div class="text-xs" style="color: #8B6F5E;" x-text="q.vowel_name"></div>
                    </div>
                </div>
                <button type="button" @click="speakArabic(q.text)" class="text-xs px-3 py-1 rounded-full border" style="border-color: #C9A882; color: #A6845E;">🔊 Dengar</button>
            </div>

            <button x-show="answered" @click="next()" class="w-full py-4 rounded-xl font-bold text-white" style="background-color: #A6845E;"
                    x-text="current < questions.length - 1 ? 'Seterusnya →' : 'Lihat Keputusan →'"></button>
        </div>
    </template>

    <template x-if="finished">
        <div class="text-center py-6">
            <div class="text-6xl mb-3" x-text="percentage >= 90 ? '🎉' : '💪'"></div>
            <h2 class="text-3xl font-bold mb-1" style="color: #3D2C1E;" x-text="percentage + '%'"></h2>
            <p class="text-sm mb-6" style="color: #8B6F5E;" x-text="percentage >= 90 ? 'Bagus! Anda boleh teruskan ke pelajaran seterusnya.' : 'Ulang sekali lagi — sasaran 90%.'"></p>

            <template x-if="mistakes.length">
                <div class="text-left mb-6">
                    <h3 class="font-semibold mb-2" style="color: #3D2C1E;">Suku kata untuk diulang:</h3>
                    <div class="flex flex-wrap gap-2" style="direction: rtl;">
                        <template x-for="m in mistakes" :key="m.text">
                            <button type="button" @click="speakArabic(m.text)" class="rounded-xl border px-3 py-1 text-center" style="background:#F5F0EB;border-color:#E2D5C8;">
                                <div class="font-arabic text-3xl" style="color: #A6845E; line-height: 1.7;" x-text="m.text"></div>
                                <div class="text-xs" style="color: #8B6F5E;" x-text="m.rumi"></div>
                            </button>
                        </template>
                    </div>
                </div>
            </template>

            <x-nasihat tag="kuiz" class="mb-4 text-left" />

            <div class="grid grid-cols-2 gap-3">
                <a href="{{ request()->fullUrl() }}" class="py-4 rounded-xl font-semibold text-white" style="background-color: #A6845E;">Ulang</a>
                <a href="{{ route('baris') }}" class="py-4 rounded-xl font-semibold border" style="border-color: #E2D5C8; color: #3D2C1E;">← Baris</a>
            </div>
        </div>
    </template>

</div>

</x-layouts.app>
