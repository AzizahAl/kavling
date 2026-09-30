<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('kode') · @yield('judul')</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 px-4">
    <div class="w-full max-w-md text-center">
        <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-forest-800 text-lg font-bold text-gold-400">TR</div>
        <p class="text-sm font-semibold tracking-wider text-gold-700">@yield('kode')</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">@yield('judul')</h1>
        <p class="mt-2 text-slate-500">@yield('pesan')</p>
        <div class="mt-8 flex flex-col justify-center gap-2 sm:flex-row">
            <a href="javascript:history.back()" class="btn btn-secondary">Kembali</a>
            <a href="{{ url('/') }}" class="btn btn-primary">Ke Beranda</a>
        </div>
    </div>
</body>
</html>
