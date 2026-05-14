<x-layouts.app title="Tetapkan Kuiz">

<div x-data="{
    mode: '{{ request('mode', 'mixed') }}',
    count: 20,
    customCount: '',
    showCustom: false,
    groups: ['dot_family','round_family','straight_family','teeth_family','tail_family','misc'],
    toggleGroup(g) {
        if (this.groups.includes(g)) {
            this.groups = this.groups.filter(x => x !== g);
        } else {
            this.groups.push(g);
        }
    },
    setCount(val) {
        if (val === 'custom') {
            this.showCustom = true;
            this.count = 'custom';
        } else {
            this.showCustom = false;
            this.customCount = '';
            this.count = val;
        }
    },
    get finalCount() {
        if (this.count === 'custom') return parseInt(this.customCount) || 20;
        return this.count;
    },
    startQuiz() {
        if (this.count === 'custom' && (!this.customCount || parseInt(this.customCount) < 1)) {
            alert('Sila masukkan bilangan soalan yang sah.');
            return;
        }
        const params = new URLSearchParams({ mode: this.mode, count: this.finalCount });
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
        <div class="flex flex-wrap gap-2 mb-3">
            @foreach([10, 20, 30, 'all', 'custom'] as $num)
                <button @click="setCount('{{ $num }}')"
                        class="px-4 py-2.5 rounded-xl font-medium border transition-all"
                        :style="count == '{{ $num }}' ? 'background-color: #C9A882; color: white; border-color: #C9A882;' : 'background-color: #F5F0EB; color: #3D2C1E; border-color: #E2D5C8;'"
                        style="min-height: 44px;">
                    @if($num === 'all') Semua
                    @elseif($num === 'custom') Pilih sendiri
                    @else {{ $num }}
                    @endif
                </button>
            @endforeach
        </div>
        <div x-show="showCustom" x-transition class="flex items-center gap-2">
            <input type="number" x-model="customCount" min="1" max="100" placeholder="Contoh: 15"
                   class="w-36 px-4 py-2.5 rounded-xl border text-sm font-medium outline-none"
                   style="background-color: #F5F0EB; border-color: #C9A882; color: #3D2C1E;" />
            <span class="text-xs" style="color: #8B6F5E;">soalan (max 100)</span>
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
