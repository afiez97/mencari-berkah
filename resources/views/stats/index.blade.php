<x-layouts.app title="Statistik Hafalan">

<div x-data="hafalanData()">

    <h1 class="text-2xl font-bold mb-6" style="color: #3D2C1E;">Statistik Hafalan</h1>

    {{-- Progress Ring + Main Stats --}}
    <div class="flex flex-col lg:flex-row items-center gap-6 mb-8">
        {{-- SVG Progress Ring --}}
        <div class="relative flex items-center justify-center">
            <svg width="160" height="160" viewBox="0 0 160 160">
                <circle cx="80" cy="80" r="64" fill="none" stroke="#E2D5C8" stroke-width="12"/>
                <circle cx="80" cy="80" r="64" fill="none" stroke="#C9A882" stroke-width="12"
                        stroke-linecap="round"
                        stroke-dasharray="402.12"
                        :stroke-dashoffset="402.12 - (402.12 * stats.percentage / 100)"
                        transform="rotate(-90 80 80)"
                        style="transition: stroke-dashoffset 0.8s ease;"/>
            </svg>
            <div class="absolute text-center">
                <div class="text-3xl font-bold" style="color: #3D2C1E;" x-text="stats.learned + '/28'"></div>
                <div class="text-xs" style="color: #8B6F5E;">Dihafal</div>
            </div>
        </div>

        <div class="flex-1 grid grid-cols-3 gap-3 w-full">
            <div class="rounded-2xl p-4 border text-center" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                <div class="text-2xl font-bold" style="color: #7A9E7E;" x-text="stats.learned"></div>
                <div class="text-xs" style="color: #8B6F5E;">Hafal</div>
            </div>
            <div class="rounded-2xl p-4 border text-center" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                <div class="text-2xl font-bold" style="color: #D4A853;" x-text="stats.review"></div>
                <div class="text-xs" style="color: #8B6F5E;">Ulang</div>
            </div>
            <div class="rounded-2xl p-4 border text-center" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                <div class="text-2xl font-bold" style="color: #B8A090;" x-text="stats.newCount"></div>
                <div class="text-xs" style="color: #8B6F5E;">Baru</div>
            </div>
        </div>
    </div>

    {{-- Streak 7 Days --}}
    <div class="rounded-2xl p-5 border mb-6" style="background-color: #F5F0EB; border-color: #E2D5C8;">
        <h2 class="font-semibold mb-3" style="color: #3D2C1E;">Streak <span x-text="streak"></span> Hari</h2>
        <div class="flex gap-2">
            <template x-for="(day, i) in streakDays" :key="i">
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="w-8 h-8 rounded-lg" :style="day.active ? 'background-color: #C9A882;' : 'background-color: #E2D5C8;'"></div>
                    <span class="text-xs" style="color: #8B6F5E;" x-text="day.date"></span>
                </div>
            </template>
        </div>
    </div>

    {{-- All Letters Status --}}
    @php
        $allLetters = \App\Models\ArabicLetter::orderBy('order_num')->get();
    @endphp

    <div class="mb-6">
        <h2 class="font-semibold mb-3" style="color: #3D2C1E;">Semua Huruf</h2>
        <div class="grid grid-cols-4 lg:grid-cols-7 gap-2">
            @foreach($allLetters as $letter)
                <a href="{{ route('learn.show', $letter->id) }}"
                   class="rounded-xl p-3 border text-center transition-all hover:opacity-80"
                   style="background-color: #F5F0EB; border-color: #E2D5C8;"
                   x-data="{}"
                   :style="getStatus({{ $letter->id }}) === 'learned' ? 'border-color: #7A9E7E;' : getStatus({{ $letter->id }}) === 'review' ? 'border-color: #D4A853;' : 'border-color: #E2D5C8;'">
                    <div class="font-arabic text-2xl" style="color: #A6845E;">{{ $letter->arabic }}</div>
                    <div class="text-xs mt-1" style="color: #8B6F5E;">{{ $letter->name }}</div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Quiz History --}}
    <div class="mb-6" x-show="quizHistory.length > 0">
        <h2 class="font-semibold mb-3" style="color: #3D2C1E;">Kuiz Terbaru</h2>
        <div class="space-y-2">
            <template x-for="(q, i) in quizHistory.slice().reverse().slice(0,5)" :key="i">
                <div class="rounded-xl p-4 border flex items-center justify-between" style="background-color: #F5F0EB; border-color: #E2D5C8;">
                    <div>
                        <span class="font-medium" style="color: #3D2C1E;" x-text="q.percentage + '%'"></span>
                        <span class="text-xs ml-2" style="color: #8B6F5E;" x-text="q.correct + '/' + q.total + ' betul'"></span>
                    </div>
                    <span class="text-xs" style="color: #B8A090;" x-text="new Date(q.date).toLocaleDateString('ms-MY')"></span>
                </div>
            </template>
        </div>
    </div>

    {{-- Reset Button --}}
    <button @click="reset()"
            class="w-full py-3 rounded-xl font-medium border transition-all hover:opacity-80"
            style="color: #C47B6A; border-color: #C47B6A; background-color: transparent;">
        Reset Progress
    </button>

</div>

</x-layouts.app>
