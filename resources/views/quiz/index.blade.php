<x-layouts.app title="Tetapkan Kuiz">

<div x-data="{
    mode: '{{ request('mode', 'mixed') }}',
    count: 10,
    groups: ['dot_family','round_family','straight_family','teeth_family','tail_family','misc'],
    toggleGroup(g) {
        if (this.groups.includes(g)) {
            this.groups = this.groups.filter(x => x !== g);
        } else {
            this.groups.push(g);
        }
    },
    startQuiz() {
        const params = new URLSearchParams({
            mode: this.mode,
            count: this.count,
        });
        this.groups.forEach(g => params.append('groups[]', g));
        window.location.href = '/kuiz/main?' + params.toString();
    }
}">

    <h1 class="text-2xl font-bold mb-8" style="color: #3D2C1E;">Tetapkan Kuiz</h1>

    {{-- Mode Selection --}}
    <div class="mb-6">
        <h2 class="font-semibold mb-3" style="color: #3D2C1E;">Pilih Mod</h2>
        <div class="flex flex-wrap gap-2">
            @foreach(['name' => 'Teka Nama', 'letter' => 'Teka Huruf', 'mixed' => 'Campuran'] as $val => $label)
                <button @click="mode = '{{ $val }}'"
                        class="px-4 py-2.5 rounded-xl font-medium border transition-all"
                        :style="mode === '{{ $val }}' ? 'background-color: #C9A882; color: white; border-color: #C9A882;' : 'background-color: #F5F0EB; color: #3D2C1E; border-color: #E2D5C8;'"
                        style="min-height: 44px;">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Question Count --}}
    <div class="mb-6">
        <h2 class="font-semibold mb-3" style="color: #3D2C1E;">Bilangan Soalan</h2>
        <div class="flex flex-wrap gap-2">
            @foreach([5, 10, 20, 'all'] as $num)
                <button @click="count = '{{ $num }}'"
                        class="px-4 py-2.5 rounded-xl font-medium border transition-all"
                        :style="count == '{{ $num }}' ? 'background-color: #C9A882; color: white; border-color: #C9A882;' : 'background-color: #F5F0EB; color: #3D2C1E; border-color: #E2D5C8;'"
                        style="min-height: 44px;">
                    {{ $num === 'all' ? 'Semua' : $num }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Group Selection --}}
    <div class="mb-8">
        <h2 class="font-semibold mb-3" style="color: #3D2C1E;">Pilih Kumpulan</h2>
        @php
            $groupLabels = [
                'dot_family' => 'Keluarga Titik',
                'round_family' => 'Keluarga Bulat',
                'straight_family' => 'Keluarga Lurus',
                'teeth_family' => 'Keluarga Gigi',
                'tail_family' => 'Keluarga Ekor',
                'misc' => 'Lain-lain',
            ];
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-2">
            @foreach($groupLabels as $val => $label)
                <button @click="toggleGroup('{{ $val }}')"
                        class="px-4 py-3 rounded-xl text-left border transition-all text-sm font-medium"
                        :style="groups.includes('{{ $val }}') ? 'background-color: #EDE0D0; border-color: #C9A882; color: #A6845E;' : 'background-color: #F5F0EB; border-color: #E2D5C8; color: #8B6F5E;'"
                        style="min-height: 52px;">
                    <span x-text="groups.includes('{{ $val }}') ? '✓ ' : ''"  class="font-bold"></span>{{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Start Button --}}
    <button @click="startQuiz()"
            class="w-full py-4 rounded-xl font-bold text-white text-lg transition-opacity hover:opacity-90"
            style="background-color: #A6845E; min-height: 56px;">
        Mula Kuiz →
    </button>

</div>

</x-layouts.app>
