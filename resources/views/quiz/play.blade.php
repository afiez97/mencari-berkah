<x-layouts.app title="Kuiz — Hafal Huruf Arab">

<script>
    window._quizQuestions = @json($questions);
</script>

<div x-data="{
    questions: window._quizQuestions,
    current: 0,
    selectedAnswer: null,
    answered: false,
    score: { correct: 0, wrong: 0 },
    wrongAnswers: [],
    startTime: Date.now(),
    soundEnabled: true,

    get question() { return this.questions[this.current]; },
    get progress() { return ((this.current + 1) / this.questions.length) * 100; },

    init() {
        this.$nextTick(() => this.speakQuestion());
    },

    speakQuestion() {
        if (!this.soundEnabled) return;
        if (this.question.mode === 'name') {
            speakArabic(this.question.question);
        }
    },

    selectAnswer(opt) {
        if (this.answered) return;
        this.selectedAnswer = opt;
        this.answered = true;
        const arabic = this.question.mode === 'name' ? this.question.question : this.question.answer;
        if (opt === this.question.answer) {
            this.score.correct++;
            if (this.soundEnabled) speakArabic(arabic);
        } else {
            this.score.wrong++;
            if (this.soundEnabled) speakArabic(arabic);
            this.wrongAnswers.push({
                arabic: this.question.mode === 'name' ? this.question.question : this.question.answer,
                name: this.question.mode === 'name' ? this.question.answer : this.question.question,
                mnemonic: this.question.mnemonic
            });
        }
    },

    optionStyle(opt) {
        if (!this.answered) return 'background-color: #F5F0EB; border-color: #E2D5C8; color: #3D2C1E;';
        if (opt === this.question.answer) return 'background-color: rgba(122,158,126,0.2); border-color: #7A9E7E; color: #3D2C1E;';
        if (opt === this.selectedAnswer) return 'background-color: rgba(196,123,106,0.2); border-color: #C47B6A; color: #3D2C1E;';
        return 'background-color: #F5F0EB; border-color: #E2D5C8; color: #8B6F5E; opacity: 0.6;';
    },

    next() {
        if (this.current < this.questions.length - 1) {
            this.current++;
            this.selectedAnswer = null;
            this.answered = false;
            this.$nextTick(() => this.speakQuestion());
        } else {
            const duration = Math.round((Date.now() - this.startTime) / 1000);
            const result = {
                correct: this.score.correct,
                wrong: this.score.wrong,
                total: this.questions.length,
                duration,
                wrongAnswers: this.wrongAnswers,
                percentage: Math.round((this.score.correct / this.questions.length) * 100),
            };
            sessionStorage.setItem('quiz_result', JSON.stringify(result));
            if (window.hafalanData) hafalanData().saveQuiz(result);
            window.location.href = '/keputusan';
        }
    }
}">

    {{-- Progress Bar --}}
    <div class="mb-6">
        <div class="flex justify-between items-center text-sm mb-2" style="color: #8B6F5E;">
            <span>Soalan <span x-text="current + 1"></span> / <span x-text="questions.length"></span></span>
            <div class="flex items-center gap-3">
                <span x-text="score.correct + ' betul'"></span>
                {{-- Sound toggle --}}
                <button @click="soundEnabled = !soundEnabled; if(!soundEnabled) window.speechSynthesis?.cancel()"
                        class="flex items-center gap-1 px-2 py-1 rounded-lg text-xs border transition-all"
                        :style="soundEnabled ? 'background-color: #EDE0D0; border-color: #C9A882; color: #A6845E;' : 'background-color: #F5F0EB; border-color: #E2D5C8; color: #B8A090;'"
                        title="Toggle bunyi">
                    <span x-text="soundEnabled ? '🔊' : '🔇'"></span>
                </button>
            </div>
        </div>
        <div class="w-full rounded-full h-2" style="background-color: #E2D5C8;">
            <div class="h-2 rounded-full transition-all duration-500" style="background-color: #C9A882;" :style="'width: ' + progress + '%'"></div>
        </div>
    </div>

    {{-- Question --}}
    <div class="text-center mb-8">
        <template x-if="question.mode === 'name'">
            <div>
                <button @click="speakArabic(question.question)"
                        class="font-arabic animate-letter cursor-pointer hover:opacity-75 active:scale-95 transition-transform"
                        style="font-size: clamp(5rem, 18vw, 7rem); color: #A6845E; line-height: 1.3; background:none; border:none; padding:0;"
                        x-text="question.question"></button>
                <div class="mt-1">
                    <button @click="speakArabic(question.question)"
                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs border transition-all hover:opacity-80"
                            style="background-color: #EDE0D0; border-color: #C9A882; color: #A6845E;">
                        🔊 Ulang sebutan — <span class="font-mono" x-text="question.transliteration"></span>
                    </button>
                </div>
            </div>
        </template>
        <template x-if="question.mode === 'letter'">
            <div class="text-3xl font-bold py-6" style="color: #3D2C1E;" x-text="question.question"></div>
        </template>
        <p class="text-sm mt-2" style="color: #8B6F5E;" x-text="question.mode === 'name' ? 'Apakah nama huruf ini?' : 'Pilih huruf yang betul'"></p>
    </div>

    {{-- Answer Options --}}
    <div class="grid grid-cols-2 gap-3 mb-6">
        <template x-for="(opt, i) in question.options" :key="i">
            <button @click="selectAnswer(opt)"
                    class="py-4 px-3 rounded-xl border font-medium transition-all text-center"
                    :style="optionStyle(opt)"
                    style="min-height: 64px;">
                <template x-if="question.mode === 'letter'">
                    <span class="font-arabic text-3xl" x-text="opt"></span>
                </template>
                <template x-if="question.mode === 'name'">
                    <span x-text="opt"></span>
                </template>
                <template x-if="answered && opt === question.answer">
                    <span class="ml-1 font-bold" style="color: #7A9E7E;"> ✓</span>
                </template>
                <template x-if="answered && opt === selectedAnswer && opt !== question.answer">
                    <span class="ml-1 font-bold" style="color: #C47B6A;"> ✗</span>
                </template>
            </button>
        </template>
    </div>

    {{-- Mnemonic Hint + sound after answer --}}
    <div x-show="answered" x-transition class="rounded-xl p-4 mb-6 animate-slide-down" style="background-color: #EDE0D0;">
        <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-semibold" style="color: #A6845E;">💡 Cara Ingat</span>
            <button @click="speakArabic(question.mode === 'name' ? question.question : question.answer)"
                    class="text-xs px-2 py-0.5 rounded-full border"
                    style="background-color: #F5F0EB; border-color: #C9A882; color: #A6845E;">
                🔊 Dengar huruf
            </button>
        </div>
        <p class="text-sm" style="color: #3D2C1E;" x-text="question.mnemonic"></p>
    </div>

    {{-- Next Button --}}
    <button x-show="answered" x-transition @click="next()"
            class="w-full py-4 rounded-xl font-bold text-white transition-opacity hover:opacity-90"
            style="background-color: #A6845E; min-height: 52px;">
        <span x-text="current < questions.length - 1 ? 'Seterusnya →' : 'Lihat Keputusan →'"></span>
    </button>

</div>

</x-layouts.app>
