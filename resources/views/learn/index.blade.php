<x-layouts.app title="Kenali Huruf Arab">

<div x-data="hafalanData()">

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

    <div class="mb-6">
        <h1 class="text-2xl font-bold" style="color: #3D2C1E;">Kenali Huruf</h1>
        <p class="text-sm mt-1" style="color: #8B6F5E;">Rujukan bentuk, sebutan & cara ingat. Selepas kenal, pergi sesi hafalan untuk uji ingatan.</p>
    </div>

    @if(!request()->has('group'))
        {{-- Group Selection Grid --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($groups as $groupName => $groupLetters)
                <a href="{{ route('learn') }}?group={{ $groupName }}"
                   class="rounded-2xl p-5 border transition-all hover:scale-[1.02]"
                   style="background-color: #F5F0EB; border-color: #E2D5C8;"
                   onmouseover="this.style.backgroundColor='#EDE0D0';this.style.borderColor='#C9A882'"
                   onmouseout="this.style.backgroundColor='#F5F0EB';this.style.borderColor='#E2D5C8'">
                    <h3 class="font-semibold text-lg mb-2" style="color: #3D2C1E;">{{ $groupLabels[$groupName] ?? $groupName }}</h3>
                    <div class="font-arabic text-2xl mb-3 flex flex-wrap gap-1" style="color: #A6845E; direction: rtl;">
                        @foreach($groupLetters->take(5) as $l)
                            <span>{{ $l->arabic }}</span>
                        @endforeach
                    </div>
                    <x-progress-bar :value="0" :max="$groupLetters->count()" />
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-xs px-2 py-0.5 rounded-full" style="background-color: #EDE0D0; color: #A6845E;">{{ $groupLetters->count() }} huruf</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-5 rounded-xl p-4" style="background-color: #EDE0D0; border-left: 3px solid #C9A882;">
            <p class="text-sm" style="color: #A6845E;">Dah kenal semua huruf? <a href="{{ route('quiz') }}" style="font-weight: 700; text-decoration: underline;">Mula Sesi Hafalan →</a></p>
        </div>
    @else
        {{-- Letters in selected group --}}
        <div class="mb-4 flex items-center gap-3">
            <a href="{{ route('learn') }}" class="text-sm font-medium" style="color: #C9A882;">← Semua Kumpulan</a>
            <span style="color: #E2D5C8;">|</span>
            <h2 class="font-semibold" style="color: #3D2C1E;">{{ $groupLabels[request('group')] ?? request('group') }}</h2>
        </div>

        <div class="grid grid-cols-3 lg:grid-cols-6 gap-3 mb-5">
            @foreach($letters as $letter)
                @php $status = 'new'; @endphp
                <x-letter-card :letter="$letter" :status="$status" />
            @endforeach
        </div>

        <a href="{{ route('quiz') }}?groups[]={{ request('group') }}"
           class="block w-full py-4 rounded-xl font-bold text-white text-center transition-opacity hover:opacity-90"
           style="background-color: #A6845E;">
            Hafal Kumpulan Ini →
        </a>
    @endif

</div>

</x-layouts.app>
