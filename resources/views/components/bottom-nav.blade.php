@php
// 4 tab sahaja — kurang pilihan, lebih mudah tahu apa nak tekan.
$tabs = [
    ['route' => 'home',    'label' => 'Utama',    'icon' => '🏠', 'active' => ['home', 'kilat']],
    ['route' => 'learn',   'label' => 'Huruf',    'icon' => '🎴', 'active' => ['learn*', 'credits']],
    ['route' => 'latihan', 'label' => 'Latihan',  'icon' => '🎯', 'active' => ['latihan', 'quiz*', 'result', 'hafalan*', 'baris*', 'plan*']],
    ['route' => 'stats',   'label' => 'Kemajuan', 'icon' => '📈', 'active' => ['stats']],
];
@endphp

<nav class="lg:hidden fixed bottom-0 left-0 right-0 flex border-t z-50 tabbar" style="background-color: #F5F0EB; border-color: #E2D5C8;"
     {{-- Warnakan tab serta-merta bila disentuh, sebelum halaman baharu sampai. --}}
     onpointerdown="const a = event.target.closest('a'); if (a) { this.querySelectorAll('a').forEach(x => x.style.color = '#B8A090'); a.style.color = '#A6845E'; }">
    @foreach($tabs as $tab)
        <a href="{{ route($tab['route']) }}"
           class="flex-1 flex flex-col items-center py-3 text-xs font-medium transition-colors"
           style="color: {{ request()->routeIs(...$tab['active']) ? '#A6845E' : '#B8A090' }};">
            <span class="text-lg mb-0.5">{{ $tab['icon'] }}</span>
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
