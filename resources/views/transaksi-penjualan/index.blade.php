@extends('layouts.app')
@section('title', 'Transaksi')

@php
    $labelTab = ['menunggu' => 'Menunggu Bayar', 'reservasi' => 'Reservasi', 'booking' => 'Booking', 'dp' => 'DP', 'angsuran' => 'Angsuran', 'lunas' => 'Lunas', 'batal' => 'Batal'];
    $tabs = [['Semua', request()->fullUrlWithQuery(['status' => null, 'page' => null]), ! request('status'), $stats['per_status']->except('menunggu')->sum()]];
    foreach (\App\Models\TransaksiPenjualan::STATUS as $s) {
        $tabs[] = [$labelTab[$s] ?? $s, request()->fullUrlWithQuery(['status' => $s, 'page' => null]), request('status') === $s, $stats['per_status'][$s] ?? 0];
    }
@endphp

@section('content')
<x-page-header title="Transaksi" subtitle="Total bayar & sisa dihitung dari pembayaran." :breadcrumbs="['Penjualan' => null, 'Transaksi' => null]">
    <x-slot:actions>
        <x-button icon="plus" :href="route('transaksi-penjualan.create')">Transaksi Baru</x-button>
    </x-slot:actions>
</x-page-header>

<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <x-stat-card label="Transaksi Aktif" :value="$stats['aktif']" :hint="($stats['per_status']['lunas'] ?? 0) . ' lunas · ' . ($stats['per_status']['batal'] ?? 0) . ' batal'" tone="utama" icon="cart"/>
    <x-stat-card label="Nilai Penjualan" :value="rupiah($stats['nilai_jual'])" :singkat="rupiah_singkat($stats['nilai_jual'])" icon="tag"/>
    <x-stat-card label="Terbayar" :value="rupiah($stats['terbayar'])" :singkat="rupiah_singkat($stats['terbayar'])" icon="check-circle"/>
    <x-stat-card label="Piutang" :value="rupiah($stats['piutang'])" :singkat="rupiah_singkat($stats['piutang'])" icon="wallet"/>
</div>

@if ($menunggu->isNotEmpty())
    <x-card class="mb-5" title="Menunggu Pembayaran Reservasi" subtitle="Kavling ditahan sementara. Belum dihitung sebagai penjualan, pendapatan, komisi, maupun tahap harga." :padding="false">
        <ul class="divide-y divide-slate-100">
            @foreach ($menunggu as $m)
                <li class="flex flex-wrap items-center gap-3 px-4 py-3 sm:px-5" x-data="hitungMundur(@js($m->batas_tahan?->toIso8601String()))">
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('transaksi-penjualan.show', $m) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ $m->konsumen->nama_lengkap }}</a>
                        <p class="text-xs text-slate-500">{{ $m->kode_transaksi }} · Kavling {{ $m->kavling->kode_kavling }} · {{ $m->agen->nama_agen ?? 'Tanpa agen' }}</p>
                    </div>
                    <div class="text-right text-xs">
                        <span class="text-slate-500">Sisa waktu tahan</span>
                        <p class="font-semibold tabular-nums" :class="sisa < 3600 ? 'text-red-600' : 'text-amber-700'" x-text="teks">…</p>
                    </div>
                    <x-icon-button icon="chevron-right" label="Detail" :href="route('transaksi-penjualan.show', $m)"/>
                </li>
            @endforeach
        </ul>
    </x-card>
@endif

<x-tabs :items="$tabs"/>

<div class="card">
    <x-filter-bar placeholder="Cari ID, konsumen, kavling…" :abaikan="['status']">
        @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
        <x-select name="jenis" :options="['cash' => 'Cash', 'angsuran' => 'Angsuran']" :value="request('jenis')" placeholder="Semua metode"/>
        <x-select name="agen" :options="$agens" :value="request('agen')" placeholder="Semua agen"/>
        <x-input type="month" name="bulan" :value="request('bulan')" aria-label="Bulan transaksi"/>
    </x-filter-bar>

    @if ($transaksis->isEmpty())
        <x-empty-state title="Belum ada transaksi" :message="request()->query() ? 'Ubah atau hapus filter.' : null">
            <x-button icon="plus" :href="route('transaksi-penjualan.create')">Transaksi Baru</x-button>
        </x-empty-state>
    @else
        <div class="table-wrap max-md:hidden">
            <table class="table">
                <thead><tr><th>Transaksi</th><th>Konsumen</th><th>Kavling</th><th>Metode</th><th class="text-right">Harga Jual</th><th class="text-right">Terbayar</th><th class="text-right">Sisa</th><th>Status</th><th class="w-px"></th></tr></thead>
                <tbody>
                    @foreach ($transaksis as $t)
                        <tr>
                            <td><a href="{{ route('transaksi-penjualan.show', $t) }}" class="tautan">{{ $t->kode_transaksi }}</a><div class="text-xs text-slate-500">{{ tanggal($t->tanggal) }}</div></td>
                            <td><span class="font-medium text-slate-900">{{ $t->konsumen->nama_lengkap }}</span><div class="text-xs text-slate-500">{{ $t->agen->nama_agen ?? 'Tanpa agen' }}</div></td>
                            <td class="font-medium text-slate-900">{{ $t->kavling->kode_kavling }}</td>
                            <td>{{ $t->isAngsuran() ? "Angsuran {$t->tenor} bln" : 'Cash' }}</td>
                            <td class="text-right tabular-nums">{{ rupiah($t->nilai_jual) }}</td>
                            <td class="text-right tabular-nums">
                                {{ rupiah($t->pokokTerbayar()) }}
                                @unless ($t->isBatal())<div class="mt-1 ml-auto h-1 w-20 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand-500" style="width: {{ $t->persenLunas() }}%"></div></div>@endunless
                            </td>
                            <td class="text-right tabular-nums">{{ $t->isBatal() ? '—' : rupiah($t->sisa()) }}</td>
                            <td><x-status-bayar :t="$t"/></td>
                            <td><x-icon-button icon="chevron-right" label="Detail" :href="route('transaksi-penjualan.show', $t)"/></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <ul class="divide-y divide-slate-100 md:hidden">
            @foreach ($transaksis as $t)
                <li>
                    <a href="{{ route('transaksi-penjualan.show', $t) }}" class="flex items-start gap-3 px-4 py-3.5 transition-colors active:bg-slate-50">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="truncate font-medium text-slate-900">{{ $t->konsumen->nama_lengkap }}</span>
                                <x-status-bayar :t="$t"/>
                            </div>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $t->kavling->kode_kavling }} · {{ $t->kode_transaksi }} · {{ tanggal($t->tanggal) }}</p>
                            <div class="mt-2 flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">Sisa <span class="font-semibold text-slate-900 tabular-nums">{{ $t->isBatal() ? '—' : rupiah($t->sisa()) }}</span></span>
                                <span class="tabular-nums text-slate-500">{{ angka($t->persenLunas()) }}% dari {{ rupiah_singkat($t->nilai_jual) }}</span>
                            </div>
                            @unless ($t->isBatal())<div class="mt-1.5 h-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand-500" style="width: {{ $t->persenLunas() }}%"></div></div>@endunless
                        </div>
                        <x-icon name="chevron-right" class="mt-1 size-4 text-slate-400"/>
                    </a>
                </li>
            @endforeach
        </ul>
        {{ $transaksis->links() }}
    @endif
</div>
@endsection
