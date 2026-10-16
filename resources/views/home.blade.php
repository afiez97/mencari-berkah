<x-layouts.app title="Hafal Huruf Arab">

<script>window._plan = @json($plan);</script>

<div x-data="{
    ...hafalanData(),
    planStarted: !!HafalanPlan.settings(),
    planDay: HafalanPlan.currentDay(window._plan),
}" class="relative">

    {{-- Hero --}}
    <section class="relative text-center py-10 lg:py-14 overflow-hidden">
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none select-none" style="opacity:0.06;">
            <span class="font-arabic font-bold" style="font-size: 20rem; color: #A6845E; line-height:1;">ح</span>
        </div>
        <div class="relative z-10">
            <p class="font-arabic text-xl mb-2" style="color: #C9A882;">حفظ الحروف</p>
            <h1 class="text-4xl lg:text-5xl font-bold mb-3" style="color: #3D2C1E;">Hafal Huruf Arab</h1>
            <p class="text-sm max-w-sm mx-auto" style="color: #8B6F5E;">Hafalan bermaksud ingat tanpa tengok — latih diri recall setiap huruf dari ingatan, bukan baca dari skrin.</p>
        </div>
    </section>

    {{-- Progress Stats --}}
    <section class="grid grid-cols-3 gap-3 mb-6">
        <div class="rounded-2xl p-4 text-center border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
            <div class="text-2xl font-bold" style="color: #7A9E7E;"><span x-text="stats.learned"></span><span style="color:#B8A090; font-size:1rem;" x-text="'/' + stats.total"></span></div>
            <div class="text-xs mt-1" style="color: #8B6F5E;">Dah Hafal</div>
        </div>
        <div class="rounded-2xl p-4 text-center border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
            <div class="text-2xl font-bold" style="color: #D4A853;"><span x-text="stats.due"></span></div>
            <div class="text-xs mt-1" style="color: #8B6F5E;">Ulang Hari Ini</div>
        </div>
        <div class="rounded-2xl p-4 text-center border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
            <div class="text-2xl font-bold" style="color: #3D2C1E;"><span x-text="streak"></span></div>
            <div class="text-xs mt-1" style="color: #8B6F5E;">Hari Berturut</div>
        </div>
    </section>

    {{-- Pelan 21 hari — panduan harian untuk pemula --}}
    <section class="mb-4">
        <a href="{{ route('plan') }}"
           class="flex items-center justify-between gap-4 rounded-2xl p-5 border-2 transition-all hover:scale-[1.01]"
           style="background-color: #EDE0D0; border-color: #C9A882;">
            <div>
                <div class="text-xs font-semibold mb-1" style="color: #A6845E;"
                     x-text="!planStarted ? 'BARU BERMULA?' : (planDay ? 'PELAN · HARI ' + planDay.day + ' DARIPADA 21' : 'PELAN SELESAI')"></div>
                <h2 class="text-lg font-bold" style="color: #3D2C1E;"
                    x-text="!planStarted ? 'Ikut Pelan 21 Hari' : (planDay ? planDay.title : 'Tahniah! 🎉')"></h2>
                <p class="text-xs mt-0.5" style="color: #8B6F5E;"
                   x-text="!planStarted ? 'Kenal semua huruf dalam 8 hari, kukuh dalam 3 minggu · 15–20 min sehari' : (planDay ? 'Tekan untuk lihat tugasan hari ini' : 'Teruskan ulang kaji harian')"></p>
            </div>
            <div class="font-arabic text-4xl shrink-0" style="color: #A6845E; direction: rtl;"
                 x-text="planStarted && planDay && planDay.letters.length ? planDay.letters.join(' ') : '📅'"></div>
        </a>
    </section>

    <x-nasihat tag="home" class="mb-4" />

    <x-pasang-app class="mb-4" />

    {{-- MAIN CTA — Hafalan is the primary action --}}
    <section class="mb-4">
        {{-- Bila pelan aktif, sesi ikut huruf hari pelan supaya tidak terlajak ke huruf hari lain. --}}
        <a href="{{ route('hafalan.sesi') }}?group=all"
           :href="planStarted && planDay ? planDay.sesi_url : '{{ route('hafalan.sesi') }}?group=all'"
           class="block w-full rounded-2xl p-6 text-center border-2 transition-all hover:scale-[1.01] hover:opacity-95"
           style="background-color: #A6845E; border-color: #A6845E; color: white;">
            <div class="font-arabic text-4xl mb-1">✦</div>
            <h2 class="text-xl font-bold mb-1">Mula Sesi Hafalan</h2>
            <p class="text-sm opacity-80">Recall dari ingatan — tanpa tengok huruf dahulu</p>
        </a>
    </section>

    {{-- Secondary: Kenali (reference) + Ulang Kaji + Statistik --}}
    <section class="grid grid-cols-3 gap-3">
        <a href="{{ route('learn') }}"
           class="rounded-2xl p-4 border text-center transition-all hover:scale-[1.02]"
           style="background-color: #F5F0EB; border-color: #E2D5C8;"
           onmouseover="this.style.backgroundColor='#EDE0D0'"
           onmouseout="this.style.backgroundColor='#F5F0EB'">
            <div class="font-arabic text-3xl mb-2" style="color: #C9A882;">ب</div>
            <h3 class="font-semibold text-sm mb-0.5" style="color: #3D2C1E;">Kenali Huruf</h3>
            <p class="text-xs" style="color: #8B6F5E;">Rujuk bentuk & cara ingat</p>
        </a>
        <a href="{{ route('quiz') }}"
           class="rounded-2xl p-4 border text-center transition-all hover:scale-[1.02]"
           style="background-color: #F5F0EB; border-color: #E2D5C8;"
           onmouseover="this.style.backgroundColor='#EDE0D0'"
           onmouseout="this.style.backgroundColor='#F5F0EB'">
            <div class="text-3xl mb-2" style="color: #D4A853;">✏️</div>
            <h3 class="font-semibold text-sm mb-0.5" style="color: #3D2C1E;">Kuiz</h3>
            <p class="text-xs" style="color: #8B6F5E;">Dengar & bentuk sambung</p>
        </a>
        <a href="{{ route('stats') }}"
           class="rounded-2xl p-4 border text-center transition-all hover:scale-[1.02]"
           style="background-color: #F5F0EB; border-color: #E2D5C8;"
           onmouseover="this.style.backgroundColor='#EDE0D0'"
           onmouseout="this.style.backgroundColor='#F5F0EB'">
            <div class="text-3xl mb-2" style="color: #C9A882;">★</div>
            <h3 class="font-semibold text-sm mb-0.5" style="color: #3D2C1E;">Kemajuan</h3>
            <p class="text-xs" style="color: #8B6F5E;">Rekod hafalan anda</p>
        </a>
    </section>

</div>

</x-layouts.app>
