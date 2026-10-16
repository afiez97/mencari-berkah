<x-layouts.app title="Kredit & Lesen">

<h1 class="text-2xl font-bold mb-1" style="color: #3D2C1E;">Kredit & Lesen</h1>
<p class="text-sm mb-6" style="color: #8B6F5E;">Terima kasih kepada penyumbang kandungan terbuka yang menjadikan app ini mungkin.</p>

@if(!empty($ai['items']))
<section class="rounded-2xl p-5 border mb-6" style="background-color: #F5F0EB; border-color: #E2D5C8;">
    <h2 class="font-semibold mb-1" style="color: #3D2C1E;">🤖 Suara AI (digunakan sekarang)</h2>
    <p class="text-xs mb-3" style="color: #8B6F5E;">
        Sebutan huruf dan suku kata dijana oleh <strong>{{ $ai['generator']['provider'] ?? 'OpenAI' }}</strong>
        ({{ \App\Support\OpenAudio::modelLabel(collect($ai['items'])->pluck('model')->filter()->unique()->values()->all() ?: ($ai['generator']['model'] ?? null)) }})
        (suara "{{ $ai['generator']['voice'] ?? '' }}") dengan arahan menyebut seperti guru al-Quran. Ini <strong>suara AI, bukan suara manusia</strong> —
        semak sebutan dengan guru. {{ count($ai['items']) }} fail.
    </p>
    <div class="flex flex-wrap gap-1.5" style="direction: rtl;">
        @foreach(collect($ai['items'])->where('set', 'huruf') as $text => $item)
            <button type="button" onclick="speakArabic(@js($text))" class="relative font-arabic text-2xl w-11 h-11 rounded-lg border"
                    style="background:#FAF7F4;border-color:{{ !empty($item['verified']) ? '#7A9E7E' : '#E2D5C8' }};color:#A6845E;line-height:1.6;"
                    title="{{ !empty($item['verified']) ? 'Disahkan: AI lain mengenal pasti huruf ini dengan betul' : 'Belum disahkan' }}">{{ $text }}</button>
        @endforeach
    </div>
    <p class="text-[11px] mt-2" style="color: #B8A090;">Bingkai hijau = disahkan: model AI kedua mendengar audio dan memilih huruf yang betul daripada 29 pilihan.</p>
</section>
@endif

<section class="rounded-2xl p-5 border mb-6" style="background-color: #F5F0EB; border-color: #E2D5C8;">
    <h2 class="font-semibold mb-1" style="color: #3D2C1E;">🔊 Rakaman sebutan huruf{{ !empty($ai['items']) ? ' (sandaran)' : '' }}</h2>
    <p class="text-xs mb-4" style="color: #8B6F5E;">
        Dari <a href="https://lingualibre.org" target="_blank" rel="noopener" class="underline">Lingua Libre</a> melalui
        <a href="https://commons.wikimedia.org" target="_blank" rel="noopener" class="underline">Wikimedia Commons</a>.
        Fail asal (WAV) ditukar kepada MP3 dan kelantangannya diseragamkan; fail CC BY-SA diedarkan semula di bawah lesen yang sama.
    </p>
    <div class="space-y-1.5">
        @foreach($entries as $e)
            <div class="flex items-center gap-3 text-sm rounded-lg px-3 py-2" style="background:#FAF7F4;">
                <button type="button" onclick="new Audio(@js($e['file'])).play()" class="font-arabic text-2xl w-8 text-center" style="color:#A6845E; line-height:1.6;">{{ $e['letter'] }}</button>
                <span class="flex-1 min-w-0 truncate" style="color:#3D2C1E;">{{ $names[$e['letter']] ?? '' }} — <a href="{{ $e['source_page'] }}" target="_blank" rel="noopener" class="underline">{{ $e['speaker'] }}</a></span>
                <a href="{{ $e['license_url'] ?: '#' }}" target="_blank" rel="noopener" class="text-[11px] px-2 py-0.5 rounded-full shrink-0" style="background:#EDE0D0;color:#8B6F5E;">{{ $e['license'] }}</a>
            </div>
        @endforeach
    </div>
</section>

<section class="rounded-2xl p-5 border mb-6 text-sm space-y-3" style="background-color: #F5F0EB; border-color: #E2D5C8; color: #3D2C1E;">
    <h2 class="font-semibold" style="color: #3D2C1E;">📚 Rujukan lain</h2>
    <p><strong>Nama huruf, makhraj & sifat:</strong> <a href="https://akuislam.com/quran/huruf-hijaiyyah/" target="_blank" rel="noopener" class="underline">AkuIslam — Huruf Hijaiyyah</a> (pautan rujukan).</p>
    <p><strong>Kelas Audio:</strong> "Tajwid Via Audio" oleh Abu Sulaiman Febrio, dimainkan melalui pemain rasmi <a href="https://soundcloud.com/user-969515050/sets/belajar-makhorijul-huruf" target="_blank" rel="noopener" class="underline">SoundCloud</a> (tidak disalin).</p>
    <p><strong>Rujukan tambahan:</strong> <a href="https://www.arabicreadingcourse.com/learn-the-arabic-alphabet.php" target="_blank" rel="noopener" class="underline">ArabicReadingCourse</a> (pautan sahaja; © Big Languages).</p>
    <p><strong>Fon:</strong> Amiri oleh Khaled Hosny dan Inter oleh Rasmus Andersson — SIL Open Font License, melalui Google Fonts.</p>
    <p><strong>Suara komputer</strong> (suku kata baris & contoh perkataan): Google Translate TTS.</p>
</section>

</x-layouts.app>
