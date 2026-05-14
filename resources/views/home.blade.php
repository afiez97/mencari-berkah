<x-layouts.app title="Hafal Huruf Arab">

<div x-data="hafalanData()" class="relative">

    {{-- Hero Section --}}
    <section class="relative text-center py-12 lg:py-16 overflow-hidden">
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none select-none" style="opacity:0.06;">
            <span class="font-arabic font-bold" style="font-size: 20rem; color: #A6845E; line-height:1;">ح</span>
        </div>
        <div class="relative z-10">
            <p class="font-arabic text-xl mb-2" style="color: #C9A882;">تعلم الحروف</p>
            <h1 class="text-4xl lg:text-5xl font-bold mb-4" style="color: #3D2C1E;">Hafal Huruf Arab</h1>
            <p class="text-base max-w-md mx-auto" style="color: #8B6F5E;">Pelajari 28 huruf Arab dengan mnemonik kreatif, kuiz interaktif, dan sistem ulang kaji pintar. Mulakan perjalanan hafalan anda hari ini!</p>
        </div>
    </section>

    {{-- Stats Row --}}
    <section class="grid grid-cols-3 gap-3 mb-8">
        <div class="rounded-2xl p-4 text-center border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
            <div class="text-2xl font-bold" style="color: #3D2C1E;"><span x-text="stats.learned"></span>/28</div>
            <div class="text-xs mt-1" style="color: #8B6F5E;">Dihafal</div>
        </div>
        <div class="rounded-2xl p-4 text-center border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
            <div class="text-2xl font-bold" style="color: #3D2C1E;"><span x-text="streak"></span></div>
            <div class="text-xs mt-1" style="color: #8B6F5E;">Streak Hari</div>
        </div>
        <div class="rounded-2xl p-4 text-center border" style="background-color: #F5F0EB; border-color: #E2D5C8;">
            <div class="text-2xl font-bold" style="color: #3D2C1E;"><span x-text="stats.percentage"></span>%</div>
            <div class="text-xs mt-1" style="color: #8B6F5E;">Skor Terbaik</div>
        </div>
    </section>

    {{-- Module Cards --}}
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('learn') }}"
           class="rounded-2xl p-5 border transition-all hover:scale-[1.02] group"
           style="background-color: #F5F0EB; border-color: #E2D5C8;"
           onmouseover="this.style.backgroundColor='#EDE0D0';this.style.borderColor='#C9A882'"
           onmouseout="this.style.backgroundColor='#F5F0EB';this.style.borderColor='#E2D5C8'">
            <div class="font-arabic text-3xl mb-3" style="color: #C9A882;">ب</div>
            <h3 class="font-semibold mb-1" style="color: #3D2C1E;">Belajar Huruf</h3>
            <p class="text-xs" style="color: #8B6F5E;">Pelajari setiap huruf dengan mnemonik</p>
        </a>
        <a href="{{ route('quiz') }}"
           class="rounded-2xl p-5 border transition-all hover:scale-[1.02]"
           style="background-color: #F5F0EB; border-color: #E2D5C8;"
           onmouseover="this.style.backgroundColor='#EDE0D0';this.style.borderColor='#C9A882'"
           onmouseout="this.style.backgroundColor='#F5F0EB';this.style.borderColor='#E2D5C8'">
            <div class="font-arabic text-3xl mb-3" style="color: #C9A882;">؟</div>
            <h3 class="font-semibold mb-1" style="color: #3D2C1E;">Mula Kuiz</h3>
            <p class="text-xs" style="color: #8B6F5E;">Uji pengetahuan anda sekarang</p>
        </a>
        <a href="{{ route('quiz') }}?mode=review"
           class="rounded-2xl p-5 border transition-all hover:scale-[1.02]"
           style="background-color: #F5F0EB; border-color: #E2D5C8;"
           onmouseover="this.style.backgroundColor='#EDE0D0';this.style.borderColor='#C9A882'"
           onmouseout="this.style.backgroundColor='#F5F0EB';this.style.borderColor='#E2D5C8'">
            <div class="font-arabic text-3xl mb-3" style="color: #C9A882;">↺</div>
            <h3 class="font-semibold mb-1" style="color: #3D2C1E;">Ulang Kaji</h3>
            <p class="text-xs" style="color: #8B6F5E;">Kaji semula huruf yang diulang</p>
        </a>
        <a href="{{ route('stats') }}"
           class="rounded-2xl p-5 border transition-all hover:scale-[1.02]"
           style="background-color: #F5F0EB; border-color: #E2D5C8;"
           onmouseover="this.style.backgroundColor='#EDE0D0';this.style.borderColor='#C9A882'"
           onmouseout="this.style.backgroundColor='#F5F0EB';this.style.borderColor='#E2D5C8'">
            <div class="font-arabic text-3xl mb-3" style="color: #C9A882;">★</div>
            <h3 class="font-semibold mb-1" style="color: #3D2C1E;">Statistik</h3>
            <p class="text-xs" style="color: #8B6F5E;">Pantau kemajuan hafalan anda</p>
        </a>
    </section>

</div>

</x-layouts.app>
