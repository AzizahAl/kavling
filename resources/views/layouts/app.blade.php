<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1a5a3f">
    <title>@yield('title', 'Dashboard') · {{ $namaProyek ?? 'Tectona Residen' }}</title>
    {{-- Semua aset (Tailwind, Alpine, Chart.js, font Inter, ikon Lucide) lokal: tidak butuh internet. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-dvh" x-data="{ sidebar: false }" x-on:keydown.escape.window="sidebar = false"
      x-effect="document.documentElement.classList.toggle('overflow-hidden', sidebar && window.innerWidth < 1024)">
    <a href="#konten" class="sr-only z-[90] rounded-lg bg-white px-3 py-2 focus:not-sr-only focus:fixed focus:top-2 focus:left-2">Lewati ke konten</a>
    <div class="flex min-h-dvh">
        @include('partials.sidebar')

        <div class="flex min-w-0 flex-1 flex-col">
            @include('partials.topbar')

            <main id="konten" class="mx-auto w-full max-w-[1400px] flex-1 px-4 pt-5 pb-10 sm:px-6 lg:px-8 lg:pt-7">
                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.flash')
    @include('partials.confirm-dialog')
    @stack('scripts')
</body>
</html>
