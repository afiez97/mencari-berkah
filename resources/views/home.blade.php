<x-layouts.app title="Hafal Huruf Arab">

<div x-data="hafalanData()" class="relative">

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
            <div class="text-2xl font-bold" style="color: #7A9E7E;"><span x-text="stats.learned"></span><span style="color:#B8A090; font-size:1rem;">/28</span></div>
            <div class="text-xs mt-1" style="color: #8B6F5E;">Dah Hafal</div>
        </div>
        <div class="rounded-2xl p-4 text-center border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
            <div class="text-2xl font-bold" style="color: #D4A853;"><span x-text="stats.review"></span></div>
            <div class="text-xs mt-1" style="color: #8B6F5E;">Perlu Ulang</div>
        </div>
        <div class="rounded-2xl p-4 text-center border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
            <div class="text-2xl font-bold" style="color: #3D2C1E;"><span x-text="streak"></span></div>
            <div class="text-xs mt-1" style="color: #8B6F5E;">Hari Berturut</div>
        </div>
    </section>

    {{-- MAIN CTA — Hafalan is the primary action --}}
    <section class="mb-4">
        <a href="{{ route('quiz') }}"
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
        <a href="{{ route('quiz') }}?mode=review"
           class="rounded-2xl p-4 border text-center transition-all hover:scale-[1.02]"
           style="background-color: #F5F0EB; border-color: #E2D5C8;"
           onmouseover="this.style.backgroundColor='#EDE0D0'"
           onmouseout="this.style.backgroundColor='#F5F0EB'">
            <div class="text-3xl mb-2" style="color: #D4A853;">↺</div>
            <h3 class="font-semibold text-sm mb-0.5" style="color: #3D2C1E;">Ulang Kaji</h3>
            <p class="text-xs" style="color: #8B6F5E;">Hafalan yang lemah</p>
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
