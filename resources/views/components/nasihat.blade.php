@props(['tag' => null])

{{-- Kad nasihat belajar — bertukar setiap hari, boleh lihat nasihat lain. --}}
<div x-data="{ offset: 0, get tip() { return HafalanTips.pick(@js($tag), this.offset); } }"
     {{ $attributes->merge(['class' => 'rounded-2xl p-4 border flex gap-3 items-start']) }}
     style="background-color: #FAF7F4; border-color: #E2D5C8; border-style: dashed;">
    <div class="text-2xl leading-none mt-0.5" x-text="tip.icon"></div>
    <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between gap-2 mb-0.5">
            <span class="text-xs font-semibold" style="color: #A6845E;">NASIHAT · <span x-text="tip.title.toUpperCase()"></span></span>
            <button type="button" @click="offset++" class="text-xs shrink-0 hover:opacity-70" style="color: #B8A090;" title="Nasihat lain">↻ lain</button>
        </div>
        <p x-show="tip.arabic" class="font-arabic text-2xl my-1" dir="rtl" style="color: #A6845E;" x-text="tip.arabic"></p>
        <p class="text-sm" style="color: #3D2C1E;" x-text="tip.text"></p>
    </div>
</div>
