<!DOCTYPE html>
<html lang="ms" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FAF7F4">
    <meta name="description" content="Sistem hafalan huruf Arab interaktif">
    <title>{{ $title ?? 'Hafal Huruf Arab' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen pb-20 lg:pb-0" style="background-color: #FAF7F4; font-family: 'Inter', sans-serif; color: #3D2C1E;">

    {{-- Desktop Top Navbar --}}
    <nav class="hidden lg:flex items-center justify-between px-8 py-4 border-b" style="background-color: #F5F0EB; border-color: #E2D5C8;">
        <a href="{{ route('home') }}" class="font-arabic text-3xl font-bold" style="color: #A6845E;">حروف</a>
        <div class="flex gap-8">
            <a href="{{ route('home') }}" class="font-medium hover:text-[#A6845E] transition-colors {{ request()->routeIs('home') ? 'text-[#A6845E]' : 'text-[#8B6F5E]' }}">Utama</a>
            <a href="{{ route('learn') }}" class="font-medium hover:text-[#A6845E] transition-colors {{ request()->routeIs('learn*') ? 'text-[#A6845E]' : 'text-[#8B6F5E]' }}">Belajar</a>
            <a href="{{ route('quiz') }}" class="font-medium hover:text-[#A6845E] transition-colors {{ request()->routeIs('quiz*') ? 'text-[#A6845E]' : 'text-[#8B6F5E]' }}">Kuiz</a>
            <a href="{{ route('stats') }}" class="font-medium hover:text-[#A6845E] transition-colors {{ request()->routeIs('stats') ? 'text-[#A6845E]' : 'text-[#8B6F5E]' }}">Statistik</a>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="max-w-5xl mx-auto px-4 py-6 lg:px-8 lg:py-10">
        {{ $slot }}
    </main>

    <x-bottom-nav />

</body>
</html>
