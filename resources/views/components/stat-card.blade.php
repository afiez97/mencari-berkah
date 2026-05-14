@props(['number', 'label', 'color' => '#3D2C1E'])

<div class="rounded-xl border p-4 text-center" style="background-color: #F5F0EB; border-color: #E2D5C8;">
    <div class="text-2xl font-bold" style="color: {{ $color }};">{{ $number }}</div>
    <div class="text-xs mt-0.5" style="color: #8B6F5E;">{{ $label }}</div>
</div>
