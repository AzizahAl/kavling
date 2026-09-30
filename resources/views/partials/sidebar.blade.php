@php
    // Menu per peran. Item: [label, route, ikon, pola route aktif, parameter route].
    // Menu yang route-nya tidak ada otomatis disembunyikan.
    $pengguna = auth()->user();
    $menu = $pengguna?->isAgen() ? [
        [null, [
            ['Kinerja Saya', 'agen.show', 'home', ['agen.show', 'transaksi-penjualan.show'], $pengguna->agen_id],
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
            ['Kavling', 'kavling.index', 'grid', 'kavling.*'],
            ['Skema Harga', 'skema-harga.index', 'tag', 'skema-harga.*'],
            ['Agen', 'agen.index', 'users', 'agen.*'],
            ['Pengguna', 'pengguna.index', 'key', 'pengguna.*'],
        ]],
        ['Marketing', [
            ['Lead Harian', 'lead.index', 'funnel', 'lead.*'],
        ]],
        ['Penjualan', [
            ['Transaksi', 'transaksi-penjualan.index', 'cart', ['transaksi-penjualan.*', 'pembayaran.*', 'dokumen.*']],
            ['Konsumen', 'konsumen.index', 'user', 'konsumen.*'],
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

{{-- Latar redup saat sidebar terbuka di layar kecil --}}
<div x-show="sidebar" x-cloak x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:leave="transition-opacity duration-200" x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-40 bg-slate-900/45 backdrop-blur-[2px] lg:hidden" x-on:click="sidebar = false"></div>

<aside class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] -translate-x-full flex-col bg-brand-800 text-white transition-transform duration-250 ease-[var(--ease-halus)] lg:sticky lg:top-0 lg:h-dvh lg:w-64 lg:translate-x-0"
       :class="sidebar ? 'translate-x-0 shadow-popup' : '-translate-x-full lg:translate-x-0'" aria-label="Menu utama">
    <div class="flex h-16 shrink-0 items-center justify-between gap-3 border-b border-white/10 px-5">
        <a href="{{ route('beranda') }}" class="flex min-w-0 items-center gap-3">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-white text-sm font-bold text-brand-700">TR</span>
            <span class="min-w-0 leading-tight">
                <span class="block truncate text-[15px] font-semibold">{{ $namaProyek ?? 'Tectona Residen' }}</span>
                <span class="block text-xs text-brand-200">Manajemen Proyek</span>
            </span>
        </a>
        <button type="button" class="rounded-lg p-2 text-brand-100 hover:bg-white/10 lg:hidden" x-on:click="sidebar = false" aria-label="Tutup menu">
            <x-icon name="x" class="size-5"/>
        </button>
    </div>

    <nav class="scrollbar-tipis flex-1 overflow-y-auto px-3 py-3 text-sm">
        @foreach ($menu as [$grup, $items])
            @php $items = array_filter($items, fn ($i) => Route::has($i[1])); @endphp
            @continue(empty($items))
            @if ($grup)
                <p class="px-3 pt-5 pb-1.5 text-[11px] font-semibold tracking-wider text-brand-200/80 uppercase">{{ $grup }}</p>
            @endif
            <ul class="space-y-0.5">
                @foreach ($items as $item)
                    @php
                        [$label, $route, $icon, $pola] = $item;
                        $aktif = request()->routeIs(...(array) $pola);
                    @endphp
                    <li>
                        <a href="{{ route($route, $item[4] ?? null) }}" @if ($aktif) aria-current="page" @endif
                           @class([
                               'relative flex h-10 items-center gap-3 rounded-lg px-3 transition-colors duration-150',
                               'bg-white/15 font-medium text-white before:absolute before:inset-y-2 before:-left-3 before:w-1 before:rounded-r-full before:bg-brand-200' => $aktif,
                               'text-brand-50/85 hover:bg-white/10 hover:text-white' => ! $aktif,
                           ])>
                            <x-icon :name="$icon" @class(['size-[18px]', 'opacity-80' => ! $aktif])/>
                            {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </nav>

    @if ($pengguna)
        <form method="POST" action="{{ route('logout') }}" class="shrink-0 border-t border-white/10 p-3">
            @csrf
            <button type="submit" class="flex h-10 w-full items-center gap-3 rounded-lg px-3 text-sm text-brand-50/85 transition-colors hover:bg-white/10 hover:text-white">
                <x-icon name="logout" class="size-[18px] opacity-80"/> Keluar
            </button>
        </form>
    @endif
</aside>
