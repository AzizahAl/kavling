@php
    // Helper kecil buat nge-highlight menu aktif berdasarkan nama route
    $isActive = fn($routeName) => request()->routeIs($routeName)
        ? 'bg-white/10 text-white border-l-4 border-gold-500'
        : 'text-slate-300 hover:bg-white/5 hover:text-white border-l-4 border-transparent';
@endphp

<aside class="w-64 shrink-0 bg-forest-900 text-white flex flex-col">
    {{-- Logo --}}
    <div class="flex items-center gap-3 px-5 py-6 border-b border-white/10">
        <div class="w-9 h-9 rounded-lg bg-gold-500 flex items-center justify-center font-bold text-forest-900">S</div>
        <div>
            <p class="font-semibold leading-tight">Tectona Residence</p>
            <p class="text-xs text-slate-400 leading-tight">Land Management</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto scrollbar-thin py-4 text-sm">
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-5 py-2.5 font-medium {{ $isActive('dashboard') }}">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.5 1.5 0 012.122 0L22.28 12M4.5 9.75V21a.75.75 0 00.75.75H9a.75.75 0 00.75-.75v-4.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21a.75.75 0 00.75.75h3.75a.75.75 0 00.75-.75V9.75" />
            </svg>
            Dashboard
        </a>

        <p class="px-5 pt-5 pb-2 text-[11px] tracking-wider text-slate-500 font-semibold">DATA MASTER</p>

        <a href="{{ route('proyek.index') }}" class="flex items-center gap-3 px-5 py-2.5 {{ $isActive('proyek.index') }}">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3.75h15M5.25 3.75v17.25m13.5-17.25v17.25M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
            </svg>
            Proyek
        </a>

        <a href="{{ route('kavling.index') }}" class="flex items-center gap-3 px-5 py-2.5 {{ $isActive('kavling.index') }}">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h12A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5M9 20.25V9" />
            </svg>
            Master Kavling
        </a>

        <a href="{{ route('skema-harga.index') }}" class="flex items-center gap-3 px-5 py-2.5 {{ $isActive('skema-harga.index') }}">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3.75-9.75a2.25 2.25 0 012.25-2.25h3a2.25 2.25 0 010 4.5h-3a2.25 2.25 0 000 4.5h3a2.25 2.25 0 002.25-2.25" />
            </svg>
            Skema Harga
        </a>

        <a href="{{ route('agen.index') }}" class="flex items-center gap-3 px-5 py-2.5 {{ $isActive('agen.index') }}">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
            </svg>
            Data Agen &amp; Marketing
        </a>

        <p class="px-5 pt-5 pb-2 text-[11px] tracking-wider text-slate-500 font-semibold">PENJUALAN</p>

        <a href="{{ route('transaksi-penjualan.index') }}" class="flex items-center gap-3 px-5 py-2.5 {{ $isActive('transaksi-penjualan.index') }}">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.876-4.708 2.25-7.183a1.125 1.125 0 00-1.114-1.317H5.25M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
            </svg>
            Transaksi Penjualan
        </a>

        <a href="{{ route('konsumen.index') }}" class="flex items-center gap-3 px-5 py-2.5 {{ $isActive('konsumen.index') }}">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            Data Konsumen
        </a>

        <p class="px-5 pt-5 pb-2 text-[11px] tracking-wider text-slate-500 font-semibold">KEUANGAN</p>

        <a href="{{ route('rab.index') }}" class="flex items-center gap-3 px-5 py-2.5 {{ $isActive('rab.index') }}">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4.5h6m.75-9H18a2.25 2.25 0 012.25 2.25v10.5A2.25 2.25 0 0118 21.75H6a2.25 2.25 0 01-2.25-2.25V9.75A2.25 2.25 0 016 7.5h2.25m6.5 0V6a2.25 2.25 0 00-2.25-2.25h-1.5A2.25 2.25 0 008.75 6v1.5m6.5 0h-6.5" />
            </svg>
            RAB &amp; Realisasi
        </a>

        <a href="{{ route('kas-proyek.index') }}" class="flex items-center gap-3 px-5 py-2.5 {{ $isActive('kas-proyek.index') }}">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-4.5-9h15a1.5 1.5 0 011.5 1.5v9a1.5 1.5 0 01-1.5 1.5h-15a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 011.5-1.5z" />
            </svg>
            Kas Proyek
        </a>

        <a href="{{ route('cashflow.index') }}" class="flex items-center gap-3 px-5 py-2.5 {{ $isActive('cashflow.index') }}">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Alokasi Cashflow
        </a>
    </nav>

    <div class="border-t border-white/10 px-5 py-4">
        {{--
            Sementara tombol biasa dulu (belum ada route 'logout' karena sistem
            auth/Breeze belum di-install). Nanti kalau auth sudah ada, ganti lagi
            jadi <form action="{{ route('logout') }}" method="POST"> @csrf ... </form>
        --}}
        <button type="button" class="flex items-center gap-3 text-sm text-slate-300 hover:text-white">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
            </svg>
            Logout
        </button>
    </div>
</aside>