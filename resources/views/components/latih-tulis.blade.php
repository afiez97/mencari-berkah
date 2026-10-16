@props(['letter'])

@php
    $forms = collect(['Bebas' => $letter->form_isolated, 'Awal' => $letter->form_initial, 'Tengah' => $letter->form_medial, 'Akhir' => $letter->form_final])
        ->unique()
        ->map(fn ($text, $label) => ['label' => $label, 'text' => $text])
        ->values();
@endphp

{{-- Kanvas latihan menulis: surih panduan atau tulis dari ingatan, kemudian semak. --}}
<div x-data="writingPad(@js($forms))"
     {{ $attributes->merge(['class' => 'rounded-xl p-5 border']) }}
     style="background-color: #F5F0EB; border-color: #E2D5C8;">

    <div class="flex items-center justify-between mb-1">
        <div class="text-xs font-semibold" style="color: #A6845E;">✍️ LATIH MENULIS</div>
        <button type="button" @click="toggleMemory()" class="text-xs px-2 py-1 rounded-lg border"
                :style="fromMemory ? 'background:#A6845E;color:#fff;border-color:#A6845E' : 'border-color:#C9A882;color:#A6845E'"
                x-text="fromMemory ? '🧠 Dari ingatan' : '👁 Surih panduan'"></button>
    </div>
    <p class="text-xs mb-3" style="color: #8B6F5E;"
       x-text="fromMemory ? 'Tulis huruf tanpa panduan, kemudian tekan Semak.' : 'Surih huruf dengan jari. Tulis dari kanan ke kiri; titik ditulis selepas badan huruf.'"></p>

    <div class="flex flex-wrap gap-2 mb-3" x-show="forms.length > 1">
        <template x-for="(f, i) in forms" :key="i">
            <button type="button" @click="setForm(i)" class="px-3 py-1 rounded-full text-xs border"
                    :style="formIndex === i ? 'background:#EDE0D0;border-color:#C9A882;color:#A6845E' : 'border-color:#E2D5C8;color:#8B6F5E'">
                <span x-text="f.label"></span> <span class="font-arabic" x-text="f.text"></span>
            </button>
        </template>
    </div>

    <canvas x-ref="pad"
            @pointerdown.prevent="down($event)" @pointermove.prevent="move($event)"
            @pointerup="up()" @pointerleave="up()" @pointercancel="up()"
            class="w-full max-w-xs mx-auto block rounded-xl border aspect-square"
            style="background-color: #FFFFFF; border-color: #E2D5C8; touch-action: none; cursor: crosshair;"></canvas>

    <div class="grid grid-cols-3 gap-2 mt-3 max-w-xs mx-auto">
        <button type="button" @click="undo()" class="py-2 rounded-lg text-sm border" style="border-color:#E2D5C8;color:#8B6F5E;">↶ Undur</button>
        <button type="button" @click="clear()" class="py-2 rounded-lg text-sm border" style="border-color:#E2D5C8;color:#8B6F5E;">Padam</button>
        <button type="button" @click="check()" class="py-2 rounded-lg text-sm font-semibold text-white" style="background:#A6845E;">Semak</button>
    </div>

    <div x-show="score !== null" class="mt-3 text-center">
        <div class="text-2xl font-bold" :style="'color:' + (score >= 75 ? '#7A9E7E' : score >= 50 ? '#D4A853' : '#C47B6A')" x-text="score + '%'"></div>
        <p class="text-sm" style="color: #3D2C1E;" x-text="verdict"></p>
    </div>
</div>
