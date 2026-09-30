@php
    // Daftar menu per peran. Item: [label, route, ikon, pola route aktif, parameter route].
    // Menu yang route-nya belum ada otomatis disembunyikan.
    $pengguna = auth()->user();
    $menu = $pengguna?->isAgen() ? [
        [null, [
            ['Kinerja Saya', 'agen.show', 'home', 'agen.show', $pengguna->agen_id],
        ]],
        ['Marketing', [
            ['Lead Harian', 'lead.index', 'funnel', 'lead.index'],
            ['Rekap Lead', 'lead.rekap', 'chart', 'lead.rekap'],
        ]],
    ] : [
        [null, [
            ['Dashboard', 'dashboard', 'home', 'dashboard'],
        ]],
        ['Data Master', [
            ['Pengaturan Proyek', 'proyek.index', 'cog', 'proyek.*'],
            ['Master Kavling', 'kavling.index', 'grid', 'kavling.*'],
            ['Skema Harga', 'skema-harga.index', 'tag', 'skema-harga.*'],
            ['Agen & Marketing', 'agen.index', 'users', 'agen.*'],
            ['Pengguna', 'pengguna.index', 'lock', 'pengguna.*'],
        ]],
        ['Marketing', [
            ['Lead Harian', 'lead.index', 'funnel', 'lead.*'],
        ]],
        ['Penjualan', [
            ['Transaksi Penjualan', 'transaksi-penjualan.index', 'cart', 'transaksi-penjualan.*'],
            ['Data Konsumen', 'konsumen.index', 'user', 'konsumen.*'],
            ['Angsuran & Piutang', 'angsuran.index', 'calendar', 'angsuran.*'],
        ]],
        ['Keuangan', [
            ['Kas Proyek', 'kas-proyek.index', 'wallet', 'kas-proyek.*'],
            ['RAB & Realisasi', 'rab.index', 'clipboard', 'rab.*'],
            ['Alokasi Cashflow', 'cashflow.index', 'cashflow', 'cashflow.*'],
        ]],
        ['Dokumen', [
            ['Checklist Legal', 'legal.index', 'check-badge', 'legal.*'],
        ]],
    ];
@endphp

{{-- Latar gelap saat sidebar terbuka di layar kecil --}}
<div x-show="sidebar" x-transition.opacity x-cloak class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden" x-on:click="sidebar = false"></div>

<aside class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-forest-900 text-white transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:w-64 lg:translate-x-0"
       :class="sidebar ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0'">
    <div class="flex items-center justify-between gap-3 border-b border-white/10 px-5 py-5">
        <a href="{{ route('beranda') }}" class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gold-500 text-sm font-bold text-forest-900">TR</div>
            <div class="leading-tight">
                <p class="font-semibold">{{ $namaProyek ?? 'Tectona Residen' }}</p>
                <p class="text-xs text-forest-300">Sistem Manajemen Proyek</p>
            </div>
        </a>
        <button type="button" class="rounded-lg p-1.5 text-forest-200 hover:bg-white/10 lg:hidden" x-on:click="sidebar = false" aria-label="Tutup menu">
            <x-icon name="x"/>
        </button>
    </div>

    <nav class="scrollbar-thin flex-1 overflow-y-auto py-3 text-sm">
        @foreach ($menu as [$grup, $items])
            @php $items = array_filter($items, fn ($i) => Route::has($i[1])); @endphp
            @continue(empty($items))
            @if ($grup)
                <p class="px-5 pt-5 pb-1.5 text-[11px] font-semibold tracking-wider text-forest-400 uppercase">{{ $grup }}</p>
            @endif
            @foreach ($items as $item)
                @php [$label, $route, $icon, $pola] = $item; $param = $item[4] ?? null; @endphp
                @php $aktif = request()->routeIs($pola); @endphp
                <a href="{{ route($route, $param) }}" @class([
                    'mx-3 flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors',
                    'bg-white/10 font-medium text-white shadow-[inset_3px_0_0_var(--color-gold-500)]' => $aktif,
                    'text-forest-100/80 hover:bg-white/5 hover:text-white' => ! $aktif,
                ]) @if ($aktif) aria-current="page" @endif>
                    <x-icon :name="$icon" @class(['h-5 w-5', 'text-gold-400' => $aktif])/>
                    {{ $label }}
                </a>
            @endforeach
        @endforeach
    </nav>

    @if (Route::has('logout'))
        <form method="POST" action="{{ route('logout') }}" class="border-t border-white/10 p-3">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-forest-100/80 hover:bg-white/5 hover:text-white">
                <x-icon name="logout"/> Keluar
            </button>
        </form>
    @endif
</aside>
