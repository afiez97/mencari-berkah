<x-layouts.app title="{{ $letter->name }} — Kenali Huruf">

<div x-data="{
    ...hafalanData(),
    currentStatus: '',
    init() {
        this.currentStatus = this.getStatus({{ $letter->id }});
    }
}">

    {{-- Back --}}
    <a href="{{ route('learn') }}" class="inline-flex items-center gap-1 text-sm mb-6" style="color: #C9A882;">← Kenali Huruf</a>

    {{-- Main Letter Display --}}
    <div class="text-center mb-8">
        <button @click="speakArabic('{{ $letter->arabic }}')"
                class="font-arabic animate-letter inline-block mb-2 cursor-pointer transition-transform active:scale-95 hover:opacity-80 select-none"
                style="font-size: clamp(6rem, 18vw, 10rem); color: #A6845E; line-height: 1.2; background: none; border: none; padding: 0;"
                title="Tekan untuk dengar sebutan">{{ $letter->arabic }}</button>

        {{-- Sound button with transliteration hint --}}
        <div class="flex items-center justify-center gap-2 mb-3">
            <button @click="speakArabic('{{ $letter->arabic }}')"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium border transition-all hover:opacity-80 active:scale-95"
                    style="background-color: #EDE0D0; border-color: #C9A882; color: #A6845E;">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5L6 9H2v6h4l5 4V5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072M19.07 4.929a10 10 0 010 14.142"/>
                </svg>
                Dengar — <span class="font-mono ml-1">{{ $letter->transliteration }}</span>
            </button>
        </div>

        <div class="flex items-center justify-center gap-4 mb-3">
            <h1 class="text-2xl font-bold" style="color: #3D2C1E;">{{ $letter->name }}</h1>
            <span class="text-lg" style="color: #8B6F5E;">{{ $letter->sound }}</span>
        </div>

        {{-- Status Badge --}}
        <div class="inline-flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-sm font-medium"
                  :style="currentStatus === 'learned' ? 'background-color: #7A9E7E; color: white;' : currentStatus === 'review' ? 'background-color: #D4A853; color: white;' : 'background-color: #E2D5C8; color: #8B6F5E;'"
                  x-text="currentStatus === 'learned' ? '✓ Hafal' : currentStatus === 'review' ? '↺ Ulang Kaji' : '● Baru'">Baru</span>
        </div>
    </div>

    {{-- 4 Forms Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        @foreach(['Bebas' => $letter->form_isolated, 'Awal' => $letter->form_initial, 'Tengah' => $letter->form_medial, 'Akhir' => $letter->form_final] as $label => $form)
            <div class="rounded-xl p-4 border text-center" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                <div class="text-xs mb-2 font-medium" style="color: #8B6F5E;">{{ $label }}</div>
                <div class="font-arabic text-3xl" style="color: #A6845E;">{{ $form }}</div>
            </div>
        @endforeach
    </div>

    {{-- Mnemonic Box --}}
    <div class="rounded-xl p-5 mb-6" style="background-color: #EDE0D0; border-left: 4px solid #C9A882;">
        <div class="text-xs font-semibold mb-2" style="color: #A6845E;">💡 Cara Ingat</div>
        <p class="font-medium mb-2" style="color: #3D2C1E;">{{ $letter->mnemonic }}</p>
        <p class="text-sm italic" style="color: #8B6F5E;">{{ $letter->visual_description }}</p>
    </div>

    {{-- Example Word --}}
    <div class="rounded-xl p-5 border mb-6 flex items-center justify-between" style="background-color: #F5F0EB; border-color: #E2D5C8;">
        <div>
            <div class="text-xs font-medium mb-1" style="color: #8B6F5E;">Contoh Perkataan</div>
            <div class="text-sm" style="color: #3D2C1E;">{{ $letter->example_translation }}</div>
        </div>
        <div class="font-arabic text-2xl" style="color: #A6845E; direction: rtl;">{{ $letter->example_word }}</div>
    </div>

    {{-- Action Buttons --}}
    <div class="grid grid-cols-2 gap-3 mb-6">
        <button @click="markLearned({{ $letter->id }}); currentStatus = 'learned'"
                class="py-4 rounded-xl font-semibold text-white transition-opacity hover:opacity-90"
                style="background-color: #7A9E7E; min-height: 52px;">
            ✓ Saya Hafal!
        </button>
        <button @click="markReview({{ $letter->id }}); currentStatus = 'review'"
                class="py-4 rounded-xl font-semibold border transition-all hover:opacity-80"
                style="background-color: #F5F0EB; border-color: #E2D5C8; color: #3D2C1E; min-height: 52px;">
            ↺ Ulang Lagi
        </button>
    </div>

    {{-- Prev / Next Navigation --}}
    <div class="flex justify-between items-center">
        @if($prevId)
            <a href="{{ route('learn.show', $prevId) }}" class="flex items-center gap-2 font-medium text-sm" style="color: #C9A882;">← Sebelum</a>
        @else
            <span></span>
        @endif
        <span class="text-sm" style="color: #B8A090;">{{ $letter->order_num }}/28</span>
        @if($nextId)
            <a href="{{ route('learn.show', $nextId) }}" class="flex items-center gap-2 font-medium text-sm" style="color: #C9A882;">Seterusnya →</a>
        @else
            <span></span>
        @endif
    </div>

</div>

</x-layouts.app>
