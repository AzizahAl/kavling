@extends('layouts.app')
@section('title', 'Dashboard')

@php
    $warnaStatus = ['tersedia' => '#4f9772', 'reservasi' => '#38bdf8', 'booking' => '#f59e0b', 'dp' => '#8b5cf6', 'terjual' => '#123420'];
    $persenRab = $stats['anggaran_rab'] > 0 ? $stats['realisasi_rab'] / $stats['anggaran_rab'] * 100 : 0;
    $persenBayar = $stats['nilai_jual'] > 0 ? $stats['pokok'] / $stats['nilai_jual'] * 100 : 0;
@endphp

@section('content')
<x-page-header :title="'Selamat datang' . (auth()->user() ? ', ' . strtok(auth()->user()->name, ' ') : '')"
               :subtitle="'Ringkasan ' . \App\Services\Pengaturan::get('nama_proyek') . ' per ' . tanggal(now(), 'j F Y')">
    <x-slot:actions>
        <a href="{{ route('lead.index') }}" class="btn btn-secondary"><x-icon name="funnel" class="h-4 w-4"/> Lead Harian</a>
        <a href="{{ route('transaksi-penjualan.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4"/> Transaksi Baru</a>
    </x-slot:actions>
</x-page-header>

{{-- KPI utama --}}
<div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 sm:gap-4">
    <x-stat-card label="Harga Aktif / m²" :value="rupiah($stats['harga_aktif'])" tone="dark" icon="tag"
                 :hint="'Tahap ' . $stats['tahap'] . ' dari ' . $stats['jumlah_tahap'] . ($stats['menuju_naik'] !== null ? ' · naik setelah ' . $stats['menuju_naik'] . ' unit lagi' : '')"/>
    <x-stat-card label="Total Penjualan" :value="rupiah_singkat($stats['nilai_jual'])" :hint="$stats['transaksi'] . ' transaksi aktif'" icon="cart"/>
    <x-stat-card label="Piutang Konsumen" :value="rupiah_singkat($stats['piutang'])" :hint="angka($persenBayar, 1) . '% harga sudah terbayar'" icon="calendar">
        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-forest-500" style="width: {{ min(100, $persenBayar) }}%"></div></div>
    </x-stat-card>
    <x-stat-card label="Saldo Kas Proyek" :value="rupiah_singkat($stats['saldo'])" :hint="'Masuk ' . rupiah_singkat($stats['kas_masuk']) . ' · keluar ' . rupiah_singkat($stats['kas_keluar'])"
                 :tone="$stats['saldo'] < 0 ? 'danger' : 'gold'" icon="wallet"/>
</div>

<div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
    {{-- Status kavling --}}
    <x-card title="Status Kavling" :subtitle="$totalKavling . ' kavling'">
        <x-slot:actions><a href="{{ route('kavling.index') }}" class="text-sm font-medium text-forest-700 hover:underline">Lihat</a></x-slot:actions>
        <div class="flex flex-col items-center gap-6 sm:flex-row xl:flex-col 2xl:flex-row">
            <div class="relative h-40 w-40 shrink-0">
                <canvas id="grafik-status"></canvas>
                <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-bold text-slate-900">{{ $status['tersedia'] ?? 0 }}</span>
                    <span class="text-xs text-slate-500">tersedia</span>
                </div>
            </div>
            <ul class="w-full space-y-2 text-sm">
                @foreach (\App\Models\Kavling::STATUS as $s)
                    <li class="flex items-center justify-between gap-3">
                        <a href="{{ route('kavling.index', ['status' => $s]) }}" class="flex items-center gap-2 text-slate-700 hover:text-forest-700">
                            <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $warnaStatus[$s] }}"></span>{{ $s === 'dp' ? 'DP / Angsuran' : \App\Support\Status::label($s) }}
                        </a>
                        <span class="font-semibold tabular-nums">{{ $status[$s] ?? 0 }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </x-card>

    {{-- Penjualan per bulan --}}
    <x-card title="Penjualan 12 Bulan Terakhir" subtitle="Nilai jual transaksi aktif per bulan" class="xl:col-span-2">
        <div class="h-64"><canvas id="grafik-jual"></canvas></div>
    </x-card>
</div>

<div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
    <x-card title="Transaksi Terbaru" :padding="false" class="xl:col-span-2">
        <x-slot:actions><a href="{{ route('transaksi-penjualan.index') }}" class="text-sm font-medium text-forest-700 hover:underline">Semua</a></x-slot:actions>
        @if ($transaksiTerbaru->isEmpty())
            <x-empty-state title="Belum ada transaksi">
                <a href="{{ route('transaksi-penjualan.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4"/> Transaksi Baru</a>
            </x-empty-state>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Transaksi</th><th>Kavling</th><th class="text-right">Harga</th><th class="text-right">Terbayar</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($transaksiTerbaru as $t)
                            <tr>
                                <td><a href="{{ route('transaksi-penjualan.show', $t) }}" class="font-medium text-slate-900 hover:text-forest-700">{{ $t->konsumen->nama_lengkap }}</a>
                                    <div class="text-xs text-slate-500">{{ $t->kode_transaksi }} · {{ tanggal($t->tanggal) }}</div></td>
                                <td class="font-medium">{{ $t->kavling->kode_kavling }}</td>
                                <td class="text-right tabular-nums">{{ rupiah($t->nilai_jual) }}</td>
                                <td class="text-right tabular-nums">{{ angka($t->persenLunas(), 0) }}%</td>
                                <td><x-badge :status="$t->status"/></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    <x-card title="Marketing Bulan Ini">
        <x-slot:actions><a href="{{ route('lead.rekap', ['mode' => 'bulanan']) }}" class="text-sm font-medium text-forest-700 hover:underline">Rekap</a></x-slot:actions>
        <div class="grid grid-cols-3 gap-2 text-center">
            @foreach (['lead' => 'Lead', 'prospek' => 'Prospek', 'closing' => 'Closing'] as $k => $l)
                <div @class(['rounded-lg py-3', 'bg-forest-800 text-white' => $k === 'closing', 'bg-slate-50' => $k !== 'closing'])>
                    <p class="text-2xl font-bold tabular-nums">{{ $funnel[$k] }}</p>
                    <p @class(['text-xs', 'text-forest-200' => $k === 'closing', 'text-slate-500' => $k !== 'closing'])>{{ $l }}</p>
                </div>
            @endforeach
        </div>
        <p class="mt-5 mb-2 text-xs font-medium tracking-wide text-slate-500 uppercase">Agen Teratas</p>
        @forelse ($agenTop as $i => $a)
            <a href="{{ route('agen.show', $a->agen) }}" class="-mx-2 flex items-center justify-between gap-3 rounded-lg px-2 py-2 hover:bg-slate-50">
                <span class="flex min-w-0 items-center gap-2.5">
                    <span @class(['flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold', 'bg-gold-500 text-forest-900' => $i === 0, 'bg-slate-100 text-slate-600' => $i > 0])>{{ $i + 1 }}</span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium text-slate-900">{{ $a->agen->nama_agen }}</span>
                        <span class="text-xs text-slate-500">{{ $a->lead }} / {{ $a->prospek }} / {{ $a->closing }}</span>
                    </span>
                </span>
                <span class="text-sm font-semibold tabular-nums">{{ rupiah_singkat($a->penjualan) }}</span>
            </a>
        @empty
            <p class="text-sm text-slate-500">Belum ada agen aktif.</p>
        @endforelse
    </x-card>
</div>

<div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
    <x-card title="Arus Kas per Bulan" subtitle="Kas masuk vs keluar" class="xl:col-span-2">
        <div class="h-64"><canvas id="grafik-kas"></canvas></div>
    </x-card>

    <x-card title="Keuangan Proyek">
        <x-slot:actions><a href="{{ route('cashflow.index') }}" class="text-sm font-medium text-forest-700 hover:underline">Detail</a></x-slot:actions>
        <div class="space-y-4 text-sm">
            <div>
                <div class="flex justify-between"><span class="text-slate-500">Realisasi RAB</span><span class="font-semibold">{{ angka($persenRab, 1) }}%</span></div>
                <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full {{ $persenRab > 100 ? 'bg-red-500' : 'bg-forest-500' }}" style="width: {{ min(100, $persenRab) }}%"></div></div>
                <p class="mt-1 text-xs text-slate-500 tabular-nums">{{ rupiah($stats['realisasi_rab']) }} dari {{ rupiah($stats['anggaran_rab']) }}</p>
            </div>
            <div class="space-y-1.5 border-t border-slate-100 pt-4">
                @foreach ($pos as $p)
                    <div class="flex justify-between gap-2"><span class="text-slate-600">{{ $p->label }} <span class="text-xs text-slate-400">{{ persen($p->persen, false) }}</span></span><span @class(['tabular-nums', 'text-red-600' => $p->saldo < 0])>{{ rupiah($p->saldo) }}</span></div>
                @endforeach
            </div>
            <div class="flex items-center justify-between gap-2 rounded-lg px-3 py-2.5 {{ $laba['layak'] ? 'bg-forest-50 text-forest-700' : 'bg-slate-50 text-slate-600' }}">
                <span>Bagi laba {{ persen($laba['persen_pengelola'], false) }}:{{ persen($laba['persen_pemilik'], false) }}</span>
                <x-badge :status="$laba['layak'] ? 'selesai' : 'belum'" :label="$laba['layak'] ? 'Layak' : 'Belum layak'"/>
            </div>
        </div>
    </x-card>
</div>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <x-card title="Angsuran Terlambat" :padding="false" class="xl:col-span-2">
        <x-slot:actions><a href="{{ route('angsuran.index', ['status' => 'terlambat']) }}" class="text-sm font-medium text-forest-700 hover:underline">Semua</a></x-slot:actions>
        @if ($terlambat->isEmpty())
            <x-empty-state icon="check-circle" title="Tidak ada angsuran terlambat"/>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Konsumen</th><th>Kavling</th><th class="text-right">Tunggakan</th><th>Telat</th></tr></thead>
                    <tbody>
                        @foreach ($terlambat as $x)
                            <tr>
                                <td><a href="{{ route('transaksi-penjualan.show', $x->t) }}" class="font-medium hover:text-forest-700">{{ $x->t->konsumen->nama_lengkap }}</a><div class="text-xs text-slate-500">{{ $x->t->konsumen->no_hp }}</div></td>
                                <td>{{ $x->t->kavling->kode_kavling }}</td>
                                <td class="text-right font-semibold text-red-600 tabular-nums">{{ rupiah($x->tunggakan) }}</td>
                                <td><x-badge status="terlambat" :label="$x->hari . ' hari'"/></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    <section class="rounded-xl bg-forest-900 p-5 text-white shadow-xs">
        <h2 class="text-base font-semibold">Baseline Proyek</h2>
        <dl class="mt-4 space-y-3 text-sm">
            @foreach ($baseline as $l => $v)
                <div class="flex justify-between gap-3 border-b border-white/10 pb-3 last:border-0 last:pb-0">
                    <dt class="text-forest-200">{{ $l }}</dt><dd class="text-right font-medium">{{ $v }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const g = @js($grafik);
        const rp = (v) => window.rupiah(v);
        const singkat = (v) => v >= 1e9 ? (v / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' M' : v >= 1e6 ? (v / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' jt' : v.toLocaleString('id-ID');
        const sumbu = { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: singkat } } };

        new Chart(document.getElementById('grafik-status'), {
            type: 'doughnut',
            data: {
                labels: @js(collect(\App\Models\Kavling::STATUS)->map(fn ($s) => $s === 'dp' ? 'DP / Angsuran' : \App\Support\Status::label($s))->values()),
                datasets: [{ data: @js(collect(\App\Models\Kavling::STATUS)->map(fn ($s) => $status[$s] ?? 0)->values()), backgroundColor: @js(array_values($warnaStatus)), borderWidth: 2, borderColor: '#fff' }],
            },
            options: { cutout: '72%', plugins: { legend: { display: false } }, maintainAspectRatio: false },
        });

        new Chart(document.getElementById('grafik-jual'), {
            data: {
                labels: g.label,
                datasets: [
                    { type: 'bar', label: 'Nilai penjualan', data: g.jual, backgroundColor: '#1a4028', borderRadius: 4, yAxisID: 'y' },
                    { type: 'line', label: 'Unit', data: g.unit, borderColor: '#e8b74d', backgroundColor: '#e8b74d', cubicInterpolationMode: 'monotone', yAxisID: 'y1' },
                ],
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } }, tooltip: { callbacks: { label: (c) => c.dataset.yAxisID === 'y' ? ' ' + rp(c.raw) : ' ' + c.raw + ' unit' } } },
                scales: { ...sumbu, y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { precision: 0 } } },
            },
        });

        new Chart(document.getElementById('grafik-kas'), {
            type: 'bar',
            data: {
                labels: g.label,
                datasets: [
                    { label: 'Masuk', data: g.masuk, backgroundColor: '#4f9772', borderRadius: 4 },
                    { label: 'Keluar', data: g.keluar, backgroundColor: '#e8b74d', borderRadius: 4 },
                ],
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } }, tooltip: { callbacks: { label: (c) => ' ' + c.dataset.label + ': ' + rp(c.raw) } } },
                scales: sumbu,
            },
        });
    });
</script>
@endpush
