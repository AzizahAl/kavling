<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1a5a3f">
    <title>Masuk · {{ \App\Services\Pengaturan::get('nama_proyek', 'Tectona Residen') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-slate-50">
@php $proyek = \App\Services\Pengaturan::get('nama_proyek', 'Tectona Residen'); @endphp
<div class="grid min-h-dvh lg:grid-cols-2">
    <div class="relative hidden overflow-hidden bg-brand-800 p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div class="absolute -top-24 -right-24 size-96 rounded-full bg-brand-700/60"></div>
        <div class="absolute -bottom-32 -left-16 size-80 rounded-full bg-brand-600/30"></div>
        <div class="relative flex items-center gap-3">
            <span class="flex size-11 items-center justify-center rounded-xl bg-white font-bold text-brand-700">TR</span>
            <div>
                <p class="text-lg font-semibold">{{ $proyek }}</p>
                <p class="text-sm text-brand-200">Manajemen Proyek Kavling</p>
            </div>
        </div>
        <div class="relative max-w-md">
            <p class="text-3xl leading-tight font-semibold">Kavling, penjualan, angsuran, dan kas proyek dalam satu tempat.</p>
        </div>
        <p class="relative text-sm text-brand-200">© {{ now()->year }} {{ $proyek }}</p>
    </div>

    <div class="flex items-center justify-center px-4 py-12 sm:px-8">
        <div class="w-full max-w-sm">
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <span class="flex size-10 items-center justify-center rounded-xl bg-brand-700 font-bold text-white">TR</span>
                <p class="text-lg font-semibold text-slate-900">{{ $proyek }}</p>
            </div>

            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Masuk</h1>
            <p class="mt-1 text-sm text-slate-500">Gunakan akun dari administrator.</p>

            <form method="POST" action="{{ route('login.proses') }}" class="mt-6 space-y-4" x-data="{ lihat: false }">
                @csrf
                <x-field label="Email" name="email">
                    <x-input name="email" type="email" autocomplete="username" autofocus required/>
                </x-field>
                <x-field label="Kata Sandi" name="password">
                    <div class="relative">
                        <input :type="lihat ? 'text' : 'password'" type="password" name="password" id="password" autocomplete="current-password" required class="form-input pr-11">
                        <button type="button" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-slate-400 hover:text-slate-600" x-on:click="lihat = !lihat" :aria-label="lihat ? 'Sembunyikan sandi' : 'Tampilkan sandi'">
                            <x-icon name="eye" class="size-5" x-show="!lihat"/><x-icon name="eye-off" class="size-5" x-show="lihat" x-cloak/>
                        </button>
                    </div>
                </x-field>
                <label class="flex items-center gap-2.5 text-sm text-slate-600">
                    <input type="checkbox" name="ingat" value="1" class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500"> Ingat saya
                </label>
                <x-button type="submit" class="h-11 w-full">Masuk</x-button>
            </form>
        </div>
    </div>
</div>
@include('partials.flash', ['tanpaValidasi' => true])
</body>
</html>
