@php
$tabs = [
    ['route' => 'home',  'label' => 'Utama',    'icon' => '🏠'],
    ['route' => 'learn', 'label' => 'Belajar',  'icon' => '📖'],
    ['route' => 'quiz',  'label' => 'Kuiz',     'icon' => '✏️'],
    ['route' => 'stats', 'label' => 'Statistik','icon' => '📊'],
];
@endphp

<nav class="lg:hidden fixed bottom-0 left-0 right-0 flex border-t z-50" style="background-color: #F5F0EB; border-color: #E2D5C8;">
    @foreach($tabs as $tab)
        <a href="{{ route($tab['route']) }}"
           class="flex-1 flex flex-col items-center py-3 text-xs font-medium transition-colors"
           style="color: {{ request()->routeIs($tab['route']) || request()->routeIs($tab['route'].'*') ? '#A6845E' : '#B8A090' }};">
            <span class="text-lg mb-0.5">{{ $tab['icon'] }}</span>
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
