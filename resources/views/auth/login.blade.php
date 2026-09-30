<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk · {{ \App\Services\Pengaturan::get('nama_proyek', 'Tectona Residen') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50">
<div class="grid min-h-screen lg:grid-cols-2">
    {{-- Panel merek (desktop) --}}
    <div class="relative hidden overflow-hidden bg-forest-900 p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-forest-700/40"></div>
        <div class="absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-gold-500/10"></div>
        <div class="relative flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gold-500 font-bold text-forest-900">TR</div>
            <div>
                <p class="text-lg font-semibold">{{ \App\Services\Pengaturan::get('nama_proyek', 'Tectona Residen') }}</p>
                <p class="text-sm text-forest-300">Sistem Manajemen Proyek Kavling</p>
            </div>
        </div>
        <div class="relative max-w-md">
            <p class="text-3xl leading-tight font-semibold">Kavling, penjualan, angsuran, dan kas proyek dalam satu tempat.</p>
            <p class="mt-4 text-forest-200">Setiap angka dihitung dari satu sumber data, sehingga laporan selalu sama dengan kenyataan di lapangan.</p>
        </div>
        <p class="relative text-sm text-forest-400">© {{ now()->year }} {{ \App\Services\Pengaturan::get('nama_proyek', 'Tectona Residen') }}</p>
    </div>

    {{-- Form --}}
    <div class="flex items-center justify-center px-4 py-12 sm:px-8">
        <div class="w-full max-w-sm">
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-forest-800 font-bold text-gold-400">TR</div>
                <p class="text-lg font-semibold text-slate-900">{{ \App\Services\Pengaturan::get('nama_proyek', 'Tectona Residen') }}</p>
            </div>

            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Masuk</h1>
            <p class="mt-1 text-sm text-slate-500">Gunakan akun yang diberikan administrator.</p>

            @include('partials.flash', ['tanpaValidasi' => true])

            <form method="POST" action="{{ route('login.proses') }}" class="mt-6 space-y-4" x-data="{ lihat: false, kirim: false }" x-on:submit="kirim = true">
                @csrf
                <x-field label="Email" name="email">
                    <x-input name="email" type="email" autocomplete="username" autofocus required/>
                </x-field>
                <x-field label="Kata Sandi" name="password">
                    <div class="relative">
                        <input :type="lihat ? 'text' : 'password'" name="password" id="password" autocomplete="current-password" required class="form-input pr-11">
                        <button type="button" class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600" x-on:click="lihat = !lihat" :aria-label="lihat ? 'Sembunyikan' : 'Tampilkan'"><x-icon name="eye" class="h-5 w-5"/></button>
                    </div>
                </x-field>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="ingat" value="1" class="h-4 w-4 rounded border-slate-300 text-forest-600 focus:ring-forest-500"> Ingat saya di perangkat ini
                </label>
                <button type="submit" class="btn btn-primary w-full py-3" :disabled="kirim">
                    <span x-show="!kirim">Masuk</span><span x-show="kirim" x-cloak>Memproses…</span>
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
