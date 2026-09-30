<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ $namaProyek ?? 'Tectona Residen' }}</title>
    {{-- Semua aset (Tailwind, Alpine, Chart.js, font Inter) dibundel lokal lewat Vite: tidak butuh internet. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen" x-data="{ sidebar: false }" x-on:keydown.escape.window="sidebar = false">
    <div class="flex min-h-screen">
        @include('partials.sidebar')

        <div class="flex min-w-0 flex-1 flex-col">
            @include('partials.topbar')

            <main class="mx-auto w-full max-w-[1440px] flex-1 px-4 py-5 sm:px-6 lg:px-8 lg:py-7">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.confirm-dialog')
    @stack('scripts')
</body>
</html>
