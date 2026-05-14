<x-layouts.app title="Keputusan Kuiz">

<div x-data="{
    result: null,
    init() {
        const raw = sessionStorage.getItem('quiz_result');
        if (raw) {
            this.result = JSON.parse(raw);
        }
    },
    get grade() {
        if (!this.result) return { label: '-', color: '#8B6F5E', text: '' };
        const p = this.result.percentage;
        if (p >= 90) return { label: 'A', color: '#7A9E7E', text: 'Cemerlang!' };
        if (p >= 75) return { label: 'B', color: '#C9A882', text: 'Bagus!' };
        if (p >= 60) return { label: 'C', color: '#D4A853', text: 'Boleh lagi!' };
        return { label: 'D', color: '#C47B6A', text: 'Teruskan!' };
    }
}">

    <template x-if="result">
        <div class="text-center">

            {{-- Score Circle --}}
            <div class="inline-flex flex-col items-center justify-center w-40 h-40 rounded-full border-4 mb-4"
                 :style="'border-color: ' + grade.color">
                <div class="text-5xl font-bold" :style="'color: ' + grade.color" x-text="grade.label"></div>
                <div class="text-lg font-medium" style="color: #3D2C1E;" x-text="result.percentage + '%'"></div>
            </div>

            <h2 class="text-2xl font-bold mb-2" style="color: #3D2C1E;" x-text="grade.text"></h2>

            {{-- Stats Row --}}
            <div class="grid grid-cols-3 gap-3 mb-8 mt-6">
                <div class="rounded-2xl p-4 border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                    <div class="text-2xl font-bold" style="color: #7A9E7E;" x-text="result.correct"></div>
                    <div class="text-xs" style="color: #8B6F5E;">Betul</div>
                </div>
                <div class="rounded-2xl p-4 border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                    <div class="text-2xl font-bold" style="color: #C47B6A;" x-text="result.wrong"></div>
                    <div class="text-xs" style="color: #8B6F5E;">Salah</div>
                </div>
                <div class="rounded-2xl p-4 border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                    <div class="text-2xl font-bold" style="color: #3D2C1E;" x-text="result.duration + 's'"></div>
                    <div class="text-xs" style="color: #8B6F5E;">Masa</div>
                </div>
            </div>

            {{-- Wrong Answers --}}
            <template x-if="result.wrongAnswers && result.wrongAnswers.length > 0">
                <div class="text-left mb-8">
                    <h3 class="font-semibold mb-3" style="color: #3D2C1E;">Huruf yang perlu diulang:</h3>
                    <div class="space-y-3">
                        <template x-for="(w, i) in result.wrongAnswers" :key="i">
                            <div class="rounded-xl p-4 border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="font-arabic text-2xl" style="color: #A6845E;" x-text="w.arabic"></span>
                                    <span class="font-medium" style="color: #3D2C1E;" x-text="w.name"></span>
                                </div>
                                <p class="text-xs" style="color: #8B6F5E;" x-text="w.mnemonic"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Action Buttons --}}
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('quiz') }}"
                   class="py-4 rounded-xl font-semibold text-center transition-opacity hover:opacity-90"
                   style="background-color: #A6845E; color: white; min-height: 52px; display:flex; align-items:center; justify-content:center;">
                    Cuba Lagi
                </a>
                <a href="{{ route('home') }}"
                   class="py-4 rounded-xl font-semibold text-center border transition-all hover:opacity-80"
                   style="background-color: #F5F0EB; border-color: #E2D5C8; color: #3D2C1E; min-height: 52px; display:flex; align-items:center; justify-content:center;">
                    Balik Utama
                </a>
            </div>

        </div>
    </template>

    <template x-if="!result">
        <div class="text-center py-16">
            <p style="color: #8B6F5E;">Tiada data kuiz. Mulakan kuiz dahulu.</p>
            <a href="{{ route('quiz') }}" class="inline-block mt-4 px-6 py-3 rounded-xl font-medium text-white" style="background-color: #A6845E;">Mula Kuiz</a>
        </div>
    </template>

</div>

</x-layouts.app>
