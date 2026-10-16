<x-layouts.app title="Hafalan Huruf Arab">

@php
$groupLabels = [
    'dot_family'      => 'Keluarga Titik',
    'round_family'    => 'Keluarga Bulat',
    'straight_family' => 'Keluarga Lurus',
    'teeth_family'    => 'Keluarga Gigi',
    'tail_family'     => 'Keluarga Ekor',
    'misc'            => 'Lain-lain',
];
@endphp

<div x-data="{
    sm2: HafalanSRS.load(),
    newPerSession: 5,
    dueCount(ids) {
        return ids.filter(id => HafalanSRS.isDue(id, this.sm2)).length;
    },
    newCount(ids) {
        return ids.filter(id => !this.sm2[id]).length;
    },
    get dueTotal() {
        return Object.values(this.sm2).filter(r => r.nextReview <= Date.now()).length;
    },
    get newToday() {
        return Math.min(this.newPerSession, {{ $totalLetters }} - Object.keys(this.sm2).length);
    }
}">

    <div class="mb-8">
        <h1 class="text-2xl font-bold mb-1" style="color: #3D2C1E;">Hafalan</h1>
        <p class="text-sm" style="color: #8B6F5E;">Kaedah Active Recall + Spaced Repetition — standard dunia untuk hafal dengan cepat dan kekal.</p>
    </div>

    {{-- Due Today Card --}}
    <a href="{{ route('hafalan.sesi') }}?group=all"
       class="flex items-center justify-between rounded-2xl p-5 mb-6 border-2 transition-all hover:scale-[1.01]"
       style="background-color: #EDE0D0; border-color: #C9A882;">
        <div>
            <div class="text-xs font-semibold mb-1" style="color: #A6845E;">PERLU DIULANG HARI INI</div>
            <div class="text-3xl font-bold" style="color: #3D2C1E;" x-text="(dueTotal + newToday) + ' kad'">...</div>
            <div class="text-xs mt-1" style="color: #8B6F5E;"><span x-text="dueTotal"></span> ulang kaji + <span x-text="newToday"></span> huruf baru</div>
        </div>
        <div class="font-arabic text-5xl" style="color: #C9A882; opacity: 0.6;">ح</div>
    </a>

    {{-- How it works --}}
    <div class="rounded-xl p-4 mb-6 border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
        <div class="text-xs font-semibold mb-3" style="color: #A6845E;">CARA HAFALAN BERFUNGSI</div>
        <div class="space-y-2">
            <div class="flex items-start gap-3">
                <span class="text-lg">👁</span>
                <div><span class="font-medium text-sm" style="color: #3D2C1E;">Lihat</span> <span class="text-sm" style="color: #8B6F5E;">— Huruf ditunjukkan (kadang-kadang dalam bentuk bersambung)</span></div>
            </div>
            <div class="flex items-start gap-3">
                <span class="text-lg">🧠</span>
                <div><span class="font-medium text-sm" style="color: #3D2C1E;">Ingat</span> <span class="text-sm" style="color: #8B6F5E;">— Sebut nama huruf dengan kuat <em>sebelum</em> balik kad</span></div>
            </div>
            <div class="flex items-start gap-3">
                <span class="text-lg">🔄</span>
                <div><span class="font-medium text-sm" style="color: #3D2C1E;">Balik kad</span> <span class="text-sm" style="color: #8B6F5E;">— Semak jawapan</span></div>
            </div>
            <div class="flex items-start gap-3">
                <span class="text-lg">⭐</span>
                <div><span class="font-medium text-sm" style="color: #3D2C1E;">Nilai</span> <span class="text-sm" style="color: #8B6F5E;">— Tak Ingat / Ragu / Hafal → sistem jadualkan ulang kaji</span></div>
            </div>
        </div>
    </div>

    {{-- By Group --}}
    <h2 class="font-semibold mb-3" style="color: #3D2C1E;">Hafal Mengikut Kumpulan</h2>
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($groups as $groupName => $groupLetters)
        @php $ids = $groupLetters->pluck('id')->toArray(); @endphp
        <a href="{{ route('hafalan.sesi') }}?group={{ $groupName }}"
           class="rounded-2xl p-4 border transition-all hover:scale-[1.02]"
           style="background-color: #F5F0EB; border-color: #E2D5C8;">
            <div class="font-arabic text-2xl mb-2 flex flex-wrap gap-1" style="color: #A6845E; direction: rtl;">
                @foreach($groupLetters->take(4) as $l)
                    <span>{{ $l->arabic }}</span>
                @endforeach
            </div>
            <div class="font-semibold text-sm mb-1" style="color: #3D2C1E;">{{ $groupLabels[$groupName] ?? $groupName }}</div>
            <div class="text-xs" style="color: #8B6F5E;">
                <span x-text="dueCount({{ json_encode($ids) }}) + ' ulang · ' + newCount({{ json_encode($ids) }}) + ' baru'">{{ count($ids) }} huruf</span>
            </div>
        </a>
        @endforeach
    </div>

</div>

</x-layouts.app>
