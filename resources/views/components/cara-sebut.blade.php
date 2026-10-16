@props(['letter'])

@php
    $t = \App\Support\Tajwid::for($letter->arabic);
    $query = 'makhraj huruf '.preg_replace('/\s*\(.*\)/', '', $letter->name).' '.$letter->arabic;
@endphp

@if($t)
{{-- Makhraj & sifat — rujukan: akuislam.com/quran/huruf-hijaiyyah --}}
<div x-data="{ open: null }" {{ $attributes->merge(['class' => 'rounded-xl p-5 border']) }} style="background-color: #F5F0EB; border-color: #E2D5C8;">
    <div class="flex items-center justify-between mb-3">
        <div class="text-xs font-semibold" style="color: #A6845E;">🗣️ CARA SEBUT</div>
        <span class="text-[11px] px-2 py-0.5 rounded-full font-semibold"
              style="{{ $t['weight'] === 'tebal' ? 'background:#A6845E;color:#fff' : ($t['weight'] === 'nipis' ? 'background:#EDE0D0;color:#8B6F5E' : 'background:#D4A853;color:#fff') }}">
            {{ ucfirst($t['weight']) }}
        </span>
    </div>

    <div class="flex flex-wrap gap-2 mb-2">
        @foreach($t['regions'] as $r)
            <span class="text-xs px-2.5 py-1 rounded-lg" style="background:#FAF7F4;border:1px solid #C9A882;color:#3D2C1E;">
                Makhraj: <strong>{{ $r['name'] }}</strong> ({{ $r['desc'] }})
            </span>
        @endforeach
    </div>
    <p class="text-sm mb-4" style="color: #3D2C1E;">{{ $t['how'] }}</p>

    <div class="text-xs font-semibold mb-2" style="color: #8B6F5E;">Sifat huruf <span class="font-normal">(tekan untuk penerangan)</span></div>
    <div class="flex flex-wrap gap-1.5">
        @foreach($t['sifat'] as $i => $s)
            <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}"
                    class="text-xs px-2.5 py-1 rounded-full border transition"
                    :style="open === {{ $i }} ? 'background:#A6845E;color:#fff;border-color:#A6845E' : '{{ !empty($s['special']) ? 'background:#EDE0D0;border-color:#C9A882;color:#A6845E' : 'background:#FAF7F4;border-color:#E2D5C8;color:#3D2C1E' }}'">
                {{ $s['name'] }}
            </button>
        @endforeach
    </div>
    @foreach($t['sifat'] as $i => $s)
        <p x-show="open === {{ $i }}" class="text-sm mt-2 p-2 rounded-lg" style="background:#FAF7F4;color:#3D2C1E;"><strong>{{ $s['name'] }}:</strong> {{ $s['desc'] }}</p>
    @endforeach

    <div class="mt-4 pt-3 border-t" style="border-color: #E2D5C8;">
        <div class="text-xs font-semibold mb-2" style="color: #8B6F5E;">Rujukan sebutan</div>
        <div class="grid grid-cols-2 gap-2 text-xs">
            <a href="https://www.arabicreadingcourse.com/learn-the-arabic-alphabet.php" target="_blank" rel="noopener"
               class="px-3 py-2 rounded-lg border text-center" style="border-color:#C9A882;color:#A6845E;">🔊 ArabicReadingCourse ↗</a>
            <a href="{{ route('learn') }}#kelas-audio" class="px-3 py-2 rounded-lg border text-center" style="border-color:#C9A882;color:#A6845E;">🎧 Kelas Audio (SoundCloud)</a>
            <a href="https://www.youtube.com/results?search_query={{ rawurlencode($query) }}" target="_blank" rel="noopener"
               class="px-3 py-2 rounded-lg border text-center" style="border-color:#C9A882;color:#A6845E;">▶ Video YouTube ↗</a>
            <a href="https://www.tiktok.com/search?q={{ rawurlencode($query) }}" target="_blank" rel="noopener"
               class="px-3 py-2 rounded-lg border text-center" style="border-color:#C9A882;color:#A6845E;">♪ Video TikTok ↗</a>
        </div>
        <p class="text-[11px] mt-2" style="color: #B8A090;">Makhraj & sifat: <a href="{{ \App\Support\Tajwid::SOURCE }}" target="_blank" rel="noopener" class="underline">AkuIslam</a>. Sumber audio: <a href="{{ route('credits') }}" class="underline">kredit</a>. Pastikan sebutan disemak oleh guru bertauliah.</p>
    </div>
</div>
@endif
