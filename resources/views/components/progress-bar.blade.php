@props(['value' => 0, 'max' => 28, 'label' => ''])

@php $pct = $max > 0 ? round(($value / $max) * 100) : 0; @endphp

<div>
    <div class="w-full rounded-full" style="background-color: #E2D5C8; height: 8px;">
        <div class="rounded-full transition-all duration-500" style="background-color: #C9A882; height: 8px; width: {{ $pct }}%;"></div>
    </div>
    @if($label)
        <div class="text-xs mt-1" style="color: #8B6F5E;">{{ $label }}</div>
    @endif
</div>
