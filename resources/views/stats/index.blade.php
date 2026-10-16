<x-layouts.app title="Kemajuan">

@php $letters = \App\Support\LetterPayload::all(); @endphp
<script>window._letters = @json($letters);</script>

<div x-data="{
    ...hafalanData(),
    letters: window._letters,
    week: Kemajuan.week(),
    sp: Kemajuan.speedStats(),
    level(id) { return Kemajuan.levelOf(this.sm2[id]); },
    count(lvl) { return this.letters.filter(l => this.level(l.id) === lvl).length; },
    get slowest() {
        return Object.entries(this.sp.recent).sort((a, b) => b[1] - a[1]).slice(0, 4)
            .map(([id, ms]) => ({ letter: this.letters.find(l => l.id == id), ms })).filter(x => x.letter);
    },
    get weakest() {
        return this.letters.filter(l => this.sm2[l.id]?.lapses).sort((a, b) => this.sm2[b.id].lapses - this.sm2[a.id].lapses).slice(0, 4);
    },
}" class="max-w-xl mx-auto">

    <h1 class="text-2xl font-bold mb-5" style="color:#3D2C1E;">Kemajuan saya</h1>

    {{-- Tahap huruf --}}
    <div class="grid grid-cols-4 gap-2 mb-4">
        <template x-for="key in ['baru', 'kenal', 'ingat', 'hafal']" :key="key">
            <div class="rounded-2xl p-3 text-center" :style="'background:' + Kemajuan.LEVELS[key].color">
                <div class="text-2xl font-bold" :style="'color:' + Kemajuan.LEVELS[key].text" x-text="count(key)"></div>
                <div class="text-[11px] font-semibold" :style="'color:' + Kemajuan.LEVELS[key].text" x-text="Kemajuan.LEVELS[key].label"></div>
            </div>
        </template>
    </div>
    <p class="text-xs mb-5" style="color:#8B6F5E;"><strong>Kenal</strong> = dah belajar · <strong>Ingat</strong> = betul dalam 2 ulang kaji berjarak · <strong>Hafal</strong> = ulang kaji seterusnya ≥ 14 hari lagi.</p>

    {{-- Minggu ini --}}
    <div class="rounded-2xl p-4 mb-4" style="background:#F5F0EB;">
        <div class="flex justify-between items-center mb-2">
            <div class="text-xs font-bold" style="color:#8B6F5E;">MINGGU INI</div>
            <div class="text-xs" style="color:#8B6F5E;" x-text="week.done + '/' + week.target + ' hari' + (week.reached ? ' 🏆' : '')"></div>
        </div>
        <div class="flex justify-between">
            <template x-for="(d, i) in week.days" :key="i">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold"
                     :style="d.done ? 'background:#7A9E7E;color:#fff' : (d.today ? 'border:2px solid #C9A882;color:#A6845E' : 'background:#E2D5C8;color:#B8A090')" x-text="d.label"></div>
            </template>
        </div>
        <p class="text-xs mt-2" style="color:#B8A090;">Sasaran 5 hari seminggu — 2 hari rehat dibenarkan tanpa rasa bersalah.</p>
    </div>

    {{-- Kelajuan --}}
    <div class="rounded-2xl p-4 mb-4" style="background:#F5F0EB;">
        <div class="text-xs font-bold mb-2" style="color:#8B6F5E;">⚡ KELAJUAN MENGINGAT</div>
        <template x-if="sp.recentAvg">
            <div>
                <div class="text-sm mb-3" style="color:#3D2C1E;">Purata 7 hari: <strong x-text="(sp.recentAvg / 1000).toFixed(1) + 's'"></strong>
                    <template x-if="sp.beforeAvg"><span :style="sp.recentAvg < sp.beforeAvg ? 'color:#3F6B45' : 'color:#8B6F5E'"
                        x-text="' (sebelum ' + (sp.beforeAvg / 1000).toFixed(1) + 's' + (sp.recentAvg < sp.beforeAvg ? ' — makin pantas! 🎉)' : ')')"></span></template>
                </div>
                <div class="text-xs mb-1" style="color:#8B6F5E;">Paling lambat minggu ini — fokus latihan:</div>
                <div class="flex gap-3" style="direction:rtl;">
                    <template x-for="s in slowest" :key="s.letter.id">
                        <a :href="'/belajar/' + s.letter.id" class="text-center"><div class="font-arabic text-3xl" style="line-height:1.5;" :style="'color:' + s.letter.color" x-text="s.letter.arabic"></div><div class="text-[11px]" style="color:#8B6F5E;direction:ltr;" x-text="(s.ms / 1000).toFixed(1) + 's'"></div></a>
                    </template>
                </div>
            </div>
        </template>
        <p x-show="!sp.recentAvg" class="text-sm" style="color:#8B6F5E;">Buat beberapa sesi 5 minit — kelajuan awak akan dipaparkan di sini.</p>
    </div>

    {{-- Selalu tersilap --}}
    <template x-if="weakest.length">
        <div class="rounded-2xl p-4 mb-4" style="background:#F5F0EB;">
            <div class="text-xs font-bold mb-2" style="color:#8B6F5E;">🎯 SELALU TERSILAP</div>
            <div class="flex gap-4" style="direction:rtl;">
                <template x-for="l in weakest" :key="l.id">
                    <a :href="'/belajar/' + l.id" class="text-center"><div class="font-arabic text-3xl" style="line-height:1.5;" :style="'color:' + l.color" x-text="l.arabic"></div><div class="text-[11px]" style="color:#8B6F5E;" x-text="sm2[l.id].lapses + '×'"></div></a>
                </template>
            </div>
            <p class="text-xs mt-2" style="color:#B8A090;">Huruf ini akan muncul lebih kerap dalam sesi 5 minit.</p>
        </div>
    </template>

    {{-- Semua huruf --}}
    <div class="rounded-2xl p-4 mb-4" style="background:#F5F0EB;">
        <div class="text-xs font-bold mb-3" style="color:#8B6F5E;">🎴 SEMUA HURUF</div>
        <div class="grid grid-cols-6 sm:grid-cols-8 gap-2" style="direction:rtl;">
            <template x-for="l in letters" :key="l.id">
                <a :href="'/belajar/' + l.id" class="rounded-xl py-1 text-center" :style="'background:' + Kemajuan.LEVELS[level(l.id)].color">
                    <div class="font-arabic text-2xl" style="line-height:1.6;" :style="'color:' + (level(l.id) === 'hafal' ? '#FFFFFF' : l.color)" x-text="l.arabic"></div>
                </a>
            </template>
        </div>
    </div>

    <x-selaras-peranti class="mb-4" />

    <button @click="reset()" class="w-full py-3 rounded-xl text-sm border mb-2" style="color:#C47B6A;border-color:#C47B6A;">Reset kemajuan</button>
    <a href="{{ route('credits') }}" class="block text-center text-xs underline mb-4" style="color:#B8A090;">Kredit & lesen audio</a>

</div>

</x-layouts.app>
