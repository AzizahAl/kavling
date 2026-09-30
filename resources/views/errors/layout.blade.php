<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('kode') · @yield('judul')</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-dvh items-center justify-center bg-slate-50 px-4">
    <div class="w-full max-w-md text-center">
        <span class="mx-auto mb-6 flex size-14 items-center justify-center rounded-2xl bg-brand-700 text-lg font-bold text-white">TR</span>
        <p class="text-sm font-semibold tracking-wider text-brand-600">@yield('kode')</p>
        <h1 class="mt-2 text-2xl font-semibold text-slate-900">@yield('judul')</h1>
        <p class="mt-2 text-slate-500">@yield('pesan')</p>
        <div class="mt-8 flex flex-col justify-center gap-2 sm:flex-row">
            <a href="javascript:history.back()" class="btn btn-secondary">Kembali</a>
            <a href="{{ url('/') }}" class="btn btn-primary">Ke Beranda</a>
        </div>
    </div>
</body>
</html>
