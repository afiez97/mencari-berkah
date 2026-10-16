@props(['letter'])

{{-- Dengar contoh → rakam sebutan sendiri → bandingkan. Mod Guru boleh simpan rakaman sebagai sebutan rasmi. --}}
<div x-data="voiceRecorder({{ $letter->id }}, @js($letter->arabic))"
     {{ $attributes->merge(['class' => 'rounded-xl p-5 border']) }}
     style="background-color: #F5F0EB; border-color: #E2D5C8;">

    <div class="flex items-center justify-between mb-1">
        <div class="text-xs font-semibold" style="color: #A6845E;">🎙️ UJI SEBUTAN SAYA</div>
        <span class="text-[11px] px-2 py-0.5 rounded-full"
              :style="audio ? 'background:rgba(122,158,126,0.18);color:#4F7353' : 'background:#EDE0D0;color:#8B6F5E'"
              x-text="hasOfficial ? '✓ Rakaman guru' : ({ ai: '✓ Suara AI', open: '✓ Rakaman penutur Arab' }[audio?.source] || 'Suara komputer')"></span>
    </div>
    <p class="text-sm mb-3" style="color: #8B6F5E;">Dengar contoh, sebut dengan kuat sambil merakam, kemudian bandingkan.</p>
    <p x-show="!audio" class="text-xs mb-3" style="color: #B8A090;">Suara komputer mungkin kurang tepat untuk makhraj — bandingkan dengan rujukan di atas dan semak dengan guru.</p>
    <p x-show="audio && audio.source === 'ai'" class="text-[11px] mb-3" style="color: #B8A090;">
        Suara ini dijana AI (<span x-text="audio?.credit"></span>) — bukan suara manusia. Tetap semak makhraj dengan guru ·
        <a href="{{ route('credits') }}" class="underline">kredit</a>
    </p>
    <p x-show="audio && audio.source === 'open'" class="text-[11px] mb-3" style="color: #B8A090;">
        Rakaman: <a :href="audio?.page" target="_blank" rel="noopener" class="underline" x-text="audio?.credit"></a> · Lingua Libre ·
        <a href="{{ route('credits') }}" class="underline">kredit</a>
    </p>

    <template x-if="supported">
        <div class="grid grid-cols-3 gap-2">
            <button type="button" @click="playExample()"
                    class="py-3 rounded-xl text-sm font-semibold border" style="background:#FAF7F4;border-color:#C9A882;color:#A6845E;">
                ▶ Contoh
            </button>
            <button type="button" @click="recording ? stop() : start()"
                    class="py-3 rounded-xl text-sm font-semibold text-white"
                    :style="recording ? 'background:#C47B6A' : 'background:#A6845E'"
                    x-text="recording ? '■ Berhenti' : '● Rakam'"></button>
            <button type="button" @click="playMine()" :disabled="!myUrl"
                    class="py-3 rounded-xl text-sm font-semibold border disabled:opacity-40" style="background:#FAF7F4;border-color:#C9A882;color:#A6845E;">
                ▶ Saya
            </button>
        </div>
    </template>
    <p x-show="!supported" class="text-sm" style="color: #8B6F5E;">Pelayar ini tidak menyokong rakaman suara.</p>
    <p x-show="recording" class="text-xs mt-2" style="color: #C47B6A;">Merakam… (berhenti sendiri selepas 4 saat)</p>
    <p x-show="error" class="text-xs mt-2" style="color: #C47B6A;" x-text="error"></p>

    {{-- Mod Guru --}}
    <div class="mt-4 pt-3 border-t text-xs" style="border-color: #E2D5C8; color: #8B6F5E;">
        <template x-if="!teacherKey">
            <button type="button" @click="enableTeacher()" class="underline hover:opacity-70">Mod Guru — rakam sebutan rasmi</button>
        </template>
        <template x-if="teacherKey">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-semibold" style="color: #A6845E;">Mod Guru:</span>
                <button type="button" @click="saveOfficial()" :disabled="!blob || saving"
                        class="px-3 py-1.5 rounded-lg text-white disabled:opacity-40" style="background:#7A9E7E;"
                        x-text="saving ? 'Menyimpan…' : 'Simpan rakaman saya sebagai rasmi'"></button>
                <button type="button" x-show="hasOfficial" @click="removeOfficial()" class="underline" style="color:#C47B6A;">Padam rasmi</button>
            </div>
        </template>
        <p x-show="message" class="mt-2" x-text="message"></p>
    </div>
</div>
