@extends('layouts.app')
@section('title', 'Dashboard')

@php
    // Warna status kavling sama dengan badge (Status.php)
    $warnaStatus = collect(\App\Models\Kavling::STATUS)->mapWithKeys(fn ($s) => [$s => ['tersedia' => '#14b8a6', 'reservasi' => '#38bdf8', 'booking' => '#f59e0b', 'dp' => '#8b5cf6', 'lunas' => '#2e8b62', 'terjual' => '#334155'][$s] ?? '#94a3b8'])->all();
    $persenRab = $stats['anggaran_rab'] > 0 ? $stats['realisasi_rab'] / $stats['anggaran_rab'] * 100 : 0;
    $persenBayar = $stats['nilai_jual'] > 0 ? $stats['pokok'] / $stats['nilai_jual'] * 100 : 0;
    $labelStatus = fn ($s) => \App\Models\Kavling::LABEL_STATUS[$s] ?? $s;
@endphp

@section('content')
<x-page-header :title="'Halo' . (auth()->user() ? ', ' . strtok(auth()->user()->name, ' ') : '')"
               :subtitle="\App\Services\Pengaturan::get('nama_proyek') . ' · ' . tanggal(now(), 'j F Y')">
    <x-slot:actions>
        <x-button variant="secondary" icon="funnel" :href="route('lead.index')">Lead Harian</x-button>
        <x-button icon="plus" :href="route('transaksi-penjualan.create')">Transaksi Baru</x-button>
    </x-slot:actions>
</x-page-header>

<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <x-stat-card label="Harga Aktif/m²" :value="rupiah($stats['harga_aktif'])" tone="utama" icon="tag"
                 :hint="'Tahap ' . $stats['tahap'] . '/' . $stats['jumlah_tahap'] . ($stats['menuju_naik'] !== null ? ' · naik ' . $stats['menuju_naik'] . ' unit lagi' : '')"/>
    <x-stat-card label="Penjualan" :value="rupiah($stats['nilai_jual'])" :singkat="rupiah_singkat($stats['nilai_jual'])" :hint="$stats['transaksi'] . ' transaksi aktif'" icon="cart" :href="route('transaksi-penjualan.index')"/>
    <x-stat-card label="Piutang" :value="rupiah($stats['piutang'])" :singkat="rupiah_singkat($stats['piutang'])" :hint="angka($persenBayar, 1) . '% terbayar'" icon="calendar" :href="route('angsuran.index')">
        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand-500" style="width: {{ min(100, $persenBayar) }}%"></div></div>
    </x-stat-card>
    <x-stat-card label="Saldo Kas" :value="rupiah($stats['saldo'])" :singkat="rupiah_singkat($stats['saldo'])" :hint="'Masuk ' . rupiah_singkat($stats['kas_masuk']) . ' · keluar ' . rupiah_singkat($stats['kas_keluar'])"
                 :tone="$stats['saldo'] < 0 ? 'bahaya' : 'default'" icon="wallet" :href="route('kas-proyek.index')"/>
</div>

@php
    // Terjual = sudah ada pembeli yang membayar (DP/angsuran, lunas, terjual)
    $jumlahTerjual = ($status['dp'] ?? 0) + ($status['lunas'] ?? 0) + ($status['terjual'] ?? 0);
    $persenDari = fn ($n) => $totalKavling > 0 ? angka($n / $totalKavling * 100) . '% dari total' : null;
@endphp
<div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
    <x-stat-card label="Total Kavling" :value="angka($totalKavling)" hint="Seluruh unit" icon="grid" :href="route('kavling.index')" class="col-span-2 sm:col-span-1"/>
    <x-stat-card label="Tersedia" :value="angka($status['tersedia'] ?? 0)" :hint="$persenDari($status['tersedia'] ?? 0)" icon="check-circle" :href="route('kavling.index', ['status' => 'tersedia'])"/>
    <x-stat-card label="Reservasi" :value="angka($status['reservasi'] ?? 0)" :hint="$persenDari($status['reservasi'] ?? 0)" icon="clock" :href="route('kavling.index', ['status' => 'reservasi'])"/>
    <x-stat-card label="Booking" :value="angka($status['booking'] ?? 0)" :hint="$persenDari($status['booking'] ?? 0)" icon="clipboard" :href="route('kavling.index', ['status' => 'booking'])"/>
    <x-stat-card label="Terjual" :value="angka($jumlahTerjual)" icon="check-badge" :href="route('kavling.index')"
                 :hint="'DP ' . ($status['dp'] ?? 0) . ' · Lunas ' . ($status['lunas'] ?? 0) . ' · Terjual ' . ($status['terjual'] ?? 0)"/>
</div>

<div class="mb-5 grid grid-cols-1 gap-5 xl:grid-cols-3">
    <x-card title="Status Kavling" :subtitle="$totalKavling . ' kavling'">
        <x-slot:actions><a href="{{ route('kavling.index') }}" class="tautan text-sm">Lihat</a></x-slot:actions>
        <div class="flex flex-col items-center gap-6 sm:flex-row xl:flex-col 2xl:flex-row">
            <div class="relative size-40 shrink-0">
                <canvas id="grafik-status" role="img" aria-label="Grafik status kavling"></canvas>
                <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-semibold text-slate-900">{{ $status['tersedia'] ?? 0 }}</span>
                    <span class="text-xs text-slate-500">tersedia</span>
                </div>
            </div>
            <ul class="w-full space-y-1 text-sm">
                @foreach (\App\Models\Kavling::STATUS as $s)
                    <li>
                        <a href="{{ route('kavling.index', ['status' => $s]) }}" class="flex items-center justify-between gap-3 rounded-lg px-2 py-1.5 text-slate-700 transition-colors hover:bg-slate-50">
                            <span class="flex items-center gap-2"><span class="size-2.5 rounded-full" style="background: {{ $warnaStatus[$s] }}"></span>{{ $labelStatus($s) }}</span>
                            <span class="font-semibold tabular-nums">{{ $status[$s] ?? 0 }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </x-card>

    <x-card title="Penjualan 12 Bulan" class="xl:col-span-2">
        <div class="h-64"><canvas id="grafik-jual" role="img" aria-label="Grafik penjualan per bulan"></canvas></div>
    </x-card>
</div>

<div class="mb-5 grid grid-cols-1 gap-5 xl:grid-cols-3">
    <x-card title="Transaksi Terbaru" :padding="false" class="xl:col-span-2">
        <x-slot:actions><a href="{{ route('transaksi-penjualan.index') }}" class="tautan text-sm">Semua</a></x-slot:actions>
        @if ($transaksiTerbaru->isEmpty())
            <x-empty-state title="Belum ada transaksi">
                <x-button icon="plus" :href="route('transaksi-penjualan.create')">Transaksi Baru</x-button>
            </x-empty-state>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($transaksiTerbaru as $t)
                    <li>
                        <a href="{{ route('transaksi-penjualan.show', $t) }}" class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-slate-50 sm:px-5">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-semibold text-slate-700">{{ $t->kavling->no }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900">{{ $t->konsumen->nama_lengkap }}</p>
                                <p class="text-xs text-slate-500">{{ $t->kode_transaksi }} · {{ tanggal($t->tanggal) }}</p>
                            </div>
                            <div class="hidden text-right sm:block">
                                <p class="text-sm font-medium text-slate-900 tabular-nums">{{ rupiah($t->nilai_jual) }}</p>
                                <p class="text-xs text-slate-500 tabular-nums">{{ angka($t->persenLunas()) }}% terbayar</p>
                            </div>
                            <x-status-bayar :t="$t"/>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-card>

    <x-card title="Marketing Bulan Ini">
        <x-slot:actions><a href="{{ route('lead.rekap', ['mode' => 'bulanan']) }}" class="tautan text-sm">Rekap</a></x-slot:actions>
        <div class="grid grid-cols-3 gap-2 text-center">
            @foreach (['lead' => 'Lead', 'prospek' => 'Prospek', 'closing' => 'Closing'] as $k => $l)
                <div @class(['rounded-kontrol py-3', 'bg-brand-700 text-white' => $k === 'closing', 'bg-slate-50' => $k !== 'closing'])>
                    <p class="text-2xl font-semibold tabular-nums">{{ $funnel[$k] }}</p>
                    <p @class(['text-xs', 'text-brand-100' => $k === 'closing', 'text-slate-500' => $k !== 'closing'])>{{ $l }}</p>
                </div>
            @endforeach
        </div>
        <p class="mt-5 mb-1.5 text-xs font-medium text-slate-500">Agen teratas</p>
        <ul class="space-y-0.5">
            @forelse ($agenTop as $i => $a)
                <li>
                    <a href="{{ route('agen.show', $a->agen) }}" class="-mx-2 flex items-center justify-between gap-3 rounded-lg px-2 py-2 transition-colors hover:bg-slate-50">
                        <span class="flex min-w-0 items-center gap-2.5">
                            <span @class(['flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold', 'bg-brand-600 text-white' => $i === 0, 'bg-slate-100 text-slate-600' => $i > 0])>{{ $i + 1 }}</span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-medium text-slate-900">{{ $a->agen->nama_agen }}</span>
                                <span class="text-xs text-slate-500">{{ $a->lead }} / {{ $a->prospek }} / {{ $a->closing }}</span>
                            </span>
                        </span>
                        <span class="text-sm font-medium tabular-nums">{{ rupiah_singkat($a->penjualan) }}</span>
                    </a>
                </li>
            @empty
                <li class="text-sm text-slate-500">Belum ada agen aktif.</li>
            @endforelse
        </ul>
    </x-card>
</div>

<div class="mb-5 grid grid-cols-1 gap-5 xl:grid-cols-3">
    <x-card title="Arus Kas" class="xl:col-span-2">
        <div class="h-64"><canvas id="grafik-kas" role="img" aria-label="Grafik arus kas per bulan"></canvas></div>
    </x-card>

    <x-card title="Keuangan">
        <x-slot:actions><a href="{{ route('cashflow.index') }}" class="tautan text-sm">Detail</a></x-slot:actions>
        <div class="space-y-4 text-sm">
            <div>
                <div class="flex justify-between"><span class="text-slate-500">Realisasi RAB</span><span class="font-semibold tabular-nums">{{ angka($persenRab, 1) }}%</span></div>
                <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full {{ $persenRab > 100 ? 'bg-red-500' : 'bg-brand-500' }}" style="width: {{ min(100, $persenRab) }}%"></div></div>
                <p class="mt-1 text-xs text-slate-500 tabular-nums">{{ rupiah($stats['realisasi_rab']) }} / {{ rupiah($stats['anggaran_rab']) }}</p>
            </div>
            <dl class="space-y-1.5 border-t border-slate-100 pt-4">
                @foreach ($pos as $p)
                    <div class="flex justify-between gap-2"><dt class="text-slate-600">{{ $p->label }} <span class="text-xs text-slate-400">{{ persen($p->persen, false) }}</span></dt><dd @class(['tabular-nums', 'text-red-600' => $p->saldo < 0])>{{ rupiah($p->saldo) }}</dd></div>
                @endforeach
            </dl>
            <a href="{{ route('rab.index', ['periode' => 'semua', 'kategori' => 'Tanah']) }}" class="-mx-1 block rounded-kontrol border-t border-slate-100 px-1 pt-4 hover:bg-slate-50">
                <div class="flex justify-between gap-2"><span class="text-slate-600">Kewajiban tanah</span>
                    <span class="font-semibold tabular-nums">{{ $tanah['total'] === null ? 'Total belum ditetapkan' : ($tanah['lunas'] ? 'Lunas' : 'Sisa ' . rupiah_singkat($tanah['sisa'])) }}</span></div>
                @if ($tanah['total'])
                    <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand-500" style="width: {{ min(100, $tanah['persen']) }}%"></div></div>
                @endif
                <p class="mt-1 text-xs text-slate-500 tabular-nums">Dibayar {{ rupiah($tanah['terbayar']) }}{{ $tanah['total'] ? ' / ' . rupiah($tanah['total']) : '' }}</p>
            </a>
            <div class="flex justify-between gap-2"><span class="text-slate-600">Laba bersih tersedia</span><span class="font-semibold text-brand-700 tabular-nums">{{ rupiah($laba['laba_tersedia']) }}</span></div>
            <div class="flex items-center justify-between gap-2 rounded-kontrol bg-slate-50 px-3 py-2.5">
                <span class="text-slate-600">Bagi laba {{ persen($laba['persen_pengelola'], false) }}:{{ persen($laba['persen_pemilik'], false) }}</span>
                <x-badge :status="$laba['layak'] ? 'selesai' : 'belum'" :label="$laba['layak'] ? 'Layak' : 'Belum layak'"/>
            </div>
        </div>
    </x-card>
</div>

@if ($menunggu->isNotEmpty())
    <x-card class="mb-5" title="Menunggu Pembayaran Reservasi" :padding="false">
        <x-slot:actions><a href="{{ route('transaksi-penjualan.index', ['status' => 'menunggu']) }}" class="tautan text-sm">Semua</a></x-slot:actions>
        <ul class="divide-y divide-slate-100">
            @foreach ($menunggu as $m)
                <li x-data="hitungMundur(@js($m->batas_tahan?->toIso8601String()))">
                    <a href="{{ route('transaksi-penjualan.show', $m) }}" class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-slate-50 sm:px-5">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $m->konsumen->nama_lengkap }}</p>
                            <p class="text-xs text-slate-500">{{ $m->kavling->kode_kavling }} · {{ $m->kode_transaksi }}</p>
                        </div>
                        <span class="text-sm font-semibold tabular-nums" :class="sisa < 3600 ? 'text-red-600' : 'text-amber-700'" x-text="teks">…</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </x-card>
@endif

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <x-card title="Angsuran Terlambat" :padding="false" class="xl:col-span-2">
        <x-slot:actions><a href="{{ route('angsuran.index', ['status' => 'terlambat']) }}" class="tautan text-sm">Semua</a></x-slot:actions>
        @if ($terlambat->isEmpty())
            <x-empty-state icon="check-circle" title="Tidak ada yang terlambat"/>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($terlambat as $x)
                    <li>
                        <a href="{{ route('transaksi-penjualan.show', $x->t) }}" class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-slate-50 sm:px-5">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-900">{{ $x->t->konsumen->nama_lengkap }}</p>
                                <p class="text-xs text-slate-500">{{ $x->t->kavling->kode_kavling }} · {{ $x->t->konsumen->no_hp }}</p>
                            </div>
                            <span class="text-sm font-semibold text-red-600 tabular-nums">{{ rupiah($x->tunggakan) }}</span>
                            <x-badge status="terlambat" :label="$x->hari . ' hari'"/>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-card>

    <section class="rounded-kartu bg-brand-800 p-5 text-white shadow-kartu">
        <h2 class="text-[15px] font-semibold">Baseline Proyek</h2>
        <dl class="mt-4 space-y-3 text-sm">
            @foreach ($baseline as $l => $v)
                <div class="flex justify-between gap-3 border-b border-white/10 pb-3 last:border-0 last:pb-0">
                    <dt class="text-brand-100">{{ $l }}</dt><dd class="text-right font-medium">{{ $v }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const g = @js($grafik), w = window.warnaGrafik;
        const rp = (v) => window.rupiah(v);
        const singkat = (v) => v >= 1e9 ? (v / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' M' : v >= 1e6 ? (v / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' jt' : v.toLocaleString('id-ID');
        const sumbu = { x: { grid: { display: false } }, y: { beginAtZero: true, border: { display: false }, ticks: { callback: singkat, maxTicksLimit: 6 } } };

        new Chart(document.getElementById('grafik-status'), {
            type: 'doughnut',
            data: {
                labels: @js(collect(\App\Models\Kavling::STATUS)->map($labelStatus)->values()),
                datasets: [{ data: @js(collect(\App\Models\Kavling::STATUS)->map(fn ($s) => $status[$s] ?? 0)->values()), backgroundColor: @js(array_values($warnaStatus)), borderWidth: 3, borderColor: '#fff', hoverOffset: 4 }],
            },
            options: { cutout: '74%', plugins: { legend: { display: false } }, maintainAspectRatio: false },
        });

        new Chart(document.getElementById('grafik-jual'), {
            data: {
                labels: g.label,
                datasets: [
                    { type: 'bar', label: 'Nilai penjualan', data: g.jual, backgroundColor: w.utama, hoverBackgroundColor: w.gelap, borderRadius: 6, maxBarThickness: 36, yAxisID: 'y' },
                    { type: 'line', label: 'Unit', data: g.unit, borderColor: w.netral, backgroundColor: w.netral, pointRadius: 3, borderWidth: 2, cubicInterpolationMode: 'monotone', yAxisID: 'y1' },
                ],
            },
            options: {
                maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: (c) => c.dataset.yAxisID === 'y' ? ' ' + rp(c.raw) : ' ' + c.raw + ' unit' } } },
                scales: { ...sumbu, y1: { beginAtZero: true, position: 'right', grid: { display: false }, border: { display: false }, ticks: { precision: 0 } } },
            },
        });

        new Chart(document.getElementById('grafik-kas'), {
            type: 'bar',
            data: {
                labels: g.label,
                datasets: [
                    { label: 'Masuk', data: g.masuk, backgroundColor: w.utama, borderRadius: 5, maxBarThickness: 22 },
                    { label: 'Keluar', data: g.keluar, backgroundColor: w.netral, borderRadius: 5, maxBarThickness: 22 },
                ],
            },
            options: {
                maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: (c) => ' ' + c.dataset.label + ': ' + rp(c.raw) } } },
                scales: sumbu,
            },
        });
    });
</script>
@endpush
