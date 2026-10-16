<x-layouts.app title="Latihan">

<h1 class="text-2xl font-bold mb-1" style="color:#3D2C1E;">Latihan</h1>
<p class="text-sm mb-5" style="color:#8B6F5E;">Tak pasti nak pilih apa? Tekan <strong>5 minit</strong> — ia campurkan semuanya untuk awak.</p>

<a href="{{ route('kilat') }}" class="flex items-center gap-4 rounded-3xl p-5 mb-5 text-white" style="background:linear-gradient(135deg,#A6845E,#8C6B47);">
    <div class="text-3xl">⚡</div>
    <div class="flex-1"><div class="font-bold text-lg">5 minit</div><div class="text-xs opacity-90">Huruf baru + ulang kaji + pelbagai soalan</div></div>
    <div class="text-xl">→</div>
</a>

@php
$items = [
    ['🔊', 'Latih telinga', 'Dengar sebutan, pilih huruf', route('quiz.play').'?mode=audio&count=15'],
    ['🔗', 'Bentuk bersambung', 'Kenal huruf di awal / tengah / akhir', route('quiz.play').'?mode=form&count=15'],
    ['🃏', 'Kad imbas', 'Ingat sendiri, kemudian balik kad', route('hafalan.sesi').'?group=all'],
    ['✍️', 'Latih menulis', 'Pilih huruf, surih atau tulis dari ingatan', route('learn')],
    ['بَ', 'Baris & suku kata', 'Langkah 2 ke arah membaca', route('baris')],
    ['📅', 'Pelan 21 hari', 'Jadual huruf mengikut hari', route('plan')],
    ['🎯', 'Kuiz pilih sendiri', 'Tetapkan mod & bilangan soalan', route('quiz')],
];
@endphp
<div class="grid gap-3">
    @foreach($items as [$icon, $title, $desc, $url])
        <a href="{{ $url }}" class="flex items-center gap-4 rounded-2xl p-4 border transition hover:opacity-90" style="background:#F5F0EB;border-color:#E2D5C8;">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0 {{ $icon === 'بَ' ? 'font-arabic' : '' }}" style="background:#FAF7F4;color:#A6845E;">{{ $icon }}</div>
            <div class="flex-1"><div class="font-semibold text-sm" style="color:#3D2C1E;">{{ $title }}</div><div class="text-xs" style="color:#8B6F5E;">{{ $desc }}</div></div>
            <div style="color:#C9A882;">→</div>
        </a>
    @endforeach
</div>

</x-layouts.app>
