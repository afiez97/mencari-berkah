@props(['letter', 'status' => 'new'])

@php
$dotColor = match($status) {
    'learned' => '#7A9E7E',
    'review'  => '#D4A853',
    default   => '#B8A090',
};
@endphp

<a href="{{ route('learn.show', $letter->id) }}"
   class="relative rounded-xl border p-4 flex flex-col items-center text-center transition-all hover:scale-[1.04] hover:opacity-90 block"
   style="background-color: #F5F0EB; border-color: #E2D5C8;">

    {{-- Status dot --}}
    <span class="absolute top-2 right-2 w-2.5 h-2.5 rounded-full" style="background-color: {{ $dotColor }};"></span>

    {{-- Arabic letter --}}
    <span class="font-arabic text-4xl mb-1" style="color: #A6845E;">{{ $letter->arabic }}</span>

    {{-- Name --}}
    <span class="text-xs font-medium" style="color: #8B6F5E;">{{ $letter->name }}</span>

    {{-- Sound button --}}
    <button onclick="event.preventDefault(); speakArabic('{{ $letter->arabic }}')"
            class="mt-2 text-xs px-2 py-0.5 rounded-full border transition-all hover:opacity-70"
            style="background-color: #EDE0D0; border-color: #C9A882; color: #A6845E;">
        🔊
    </button>
</a>
