@props(['track' => null, 'title' => 'Kelas Audio: Tajwid Via Audio'])

@php
    // Siri "Tajwid Via Audio" oleh Abu Sulaiman Febrio — dimainkan melalui pemain rasmi SoundCloud (tidak dimuat turun).
    $resource = $track ? 'tracks/'.$track : 'playlists/723295560';
    $src = 'https://w.soundcloud.com/player/?'.http_build_query([
        'url' => 'https://api.soundcloud.com/'.$resource,
        'color' => '#a6845e',
        'auto_play' => 'false',
        'hide_related' => 'true',
        'show_comments' => 'false',
        'show_reposts' => 'false',
        'visual' => 'false',
    ]);
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl p-4 border']) }} style="background-color: #F5F0EB; border-color: #E2D5C8;">
    <div class="text-xs font-semibold mb-1" style="color: #A6845E;">🎧 {{ strtoupper($title) }}</div>
    <p class="text-xs mb-3" style="color: #8B6F5E;">Dengar ustaz menerangkan makhraj dan sebutan. Perlukan internet.</p>
    <iframe title="{{ $title }}" width="100%" height="{{ $track ? 120 : 300 }}" scrolling="no" frameborder="no" allow="autoplay" loading="lazy"
            class="rounded-xl" src="{{ $src }}"></iframe>
    <p class="text-[11px] mt-2" style="color: #B8A090;">Sumber: <a href="https://soundcloud.com/user-969515050/sets/belajar-makhorijul-huruf" target="_blank" rel="noopener" class="underline">"Belajar makhorijul huruf" di SoundCloud</a> · rakaman oleh Abu Sulaiman Febrio.</p>
</div>
