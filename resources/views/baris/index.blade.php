<x-layouts.app title="Baris & Suku Kata">

<div x-data="{
    best: (() => { try { return JSON.parse(localStorage.getItem('arabic_baris') || '{}'); } catch { return {}; } })(),
    open: 'a',
}">

    <div class="mb-6">
        <div class="text-xs font-semibold mb-1" style="color: #A6845E;">PERINGKAT 2 · SELEPAS KENAL HURUF</div>
        <h1 class="text-2xl font-bold" style="color: #3D2C1E;">Baris & Suku Kata</h1>
        <p class="text-sm mt-1" style="color: #8B6F5E;">Baris memberi huruf bunyi vokal. Huruf + baris = suku kata. Inilah langkah pertama untuk <strong>membaca</strong>.</p>
    </div>

    <div class="rounded-xl p-4 mb-6 text-sm" style="background-color: #EDE0D0; color: #3D2C1E; border-left: 3px solid #C9A882;">
        💡 Mulakan bila anda sudah lepas <strong>Hari 14</strong> pelan (ujian 90%). Jika belum, huruf masih bercampur dan baris akan terasa berat.
    </div>

    {{-- Pelajaran --}}
    <div class="space-y-3 mb-8">
        @foreach($rows as $key => $row)
            <div class="rounded-2xl border overflow-hidden" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                <button type="button" @click="open = open === '{{ $key }}' ? null : '{{ $key }}'"
                        class="w-full flex items-center gap-4 p-4 text-left">
                    <span class="font-arabic text-4xl w-14 text-center" style="color: #A6845E; line-height: 1.6;">{{ $row['syllables'][1]['text'] }}</span>
                    <div class="flex-1">
                        <div class="font-semibold" style="color: #3D2C1E;">{{ $loop->iteration }}. {{ $row['name'] }} — bunyi "{{ $key }}"</div>
                        <div class="text-xs" style="color: #8B6F5E;">{{ ucfirst($row['where']) }}</div>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full" x-show="best['{{ $key }}'] !== undefined"
                          :style="best['{{ $key }}'] >= 90 ? 'background:rgba(122,158,126,0.18);color:#4F7353' : 'background:#EDE0D0;color:#8B6F5E'"
                          x-text="best['{{ $key }}'] + '%'"></span>
                </button>

                <div x-show="open === '{{ $key }}'" class="px-4 pb-4">
                    <p class="text-xs mb-3" style="color: #8B6F5E;">Tekan untuk dengar. Baca dari <strong>kanan ke kiri</strong>, sebut setiap satu dengan kuat.</p>
                    <div class="grid grid-cols-4 sm:grid-cols-7 gap-2 mb-4" style="direction: rtl;">
                        @foreach($row['syllables'] as $syl)
                            <button type="button" onclick="speakArabic(@js($syl['text']))"
                                    class="rounded-xl border py-2 text-center hover:opacity-80 active:scale-95 transition"
                                    style="background-color: #FAF7F4; border-color: #E2D5C8;">
                                <div class="font-arabic text-3xl" style="color: #A6845E; line-height: 1.7;">{{ $syl['text'] }}</div>
                                <div class="text-xs" style="color: #8B6F5E; direction: ltr;">{{ $syl['rumi'] }}</div>
                            </button>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('baris.latih', ['pelajaran' => $key, 'mode' => 'see']) }}"
                           class="py-3 rounded-xl text-sm font-semibold text-center text-white" style="background-color: #A6845E;">👁 Latih membaca</a>
                        <a href="{{ route('baris.latih', ['pelajaran' => $key, 'mode' => 'hear']) }}"
                           class="py-3 rounded-xl text-sm font-semibold text-center border" style="border-color: #C9A882; color: #A6845E;">🔊 Latih mendengar</a>
                    </div>
                </div>
            </div>
        @endforeach

        <a href="{{ route('baris.latih', ['pelajaran' => 'campur', 'mode' => 'mixed', 'count' => 20]) }}"
           class="flex items-center gap-4 p-4 rounded-2xl border-2" style="background-color: #EDE0D0; border-color: #C9A882;">
            <span class="font-arabic text-3xl" style="color: #A6845E; line-height: 1.7;">بَ بِ بُ</span>
            <div class="flex-1">
                <div class="font-semibold" style="color: #3D2C1E;">4. Campur a · i · u</div>
                <div class="text-xs" style="color: #8B6F5E;">Ujian sebenar: baris dan huruf serupa bercampur. Sasaran 90%.</div>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full" x-show="best.campur !== undefined" style="background:#FAF7F4;color:#8B6F5E" x-text="best.campur + '%'"></span>
        </a>
    </div>

    <x-kelas-audio track="338777898" title="Kelas Audio: Mengenal Harokat" class="mb-6" />

    <x-nasihat tag="sesi" class="mb-6" />

</div>

</x-layouts.app>
