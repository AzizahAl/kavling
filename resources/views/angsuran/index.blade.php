@extends('layouts.app')
@section('title', 'Angsuran & Piutang')

@php
    $tabs = collect(['' => 'Semua', 'terlambat' => 'Terlambat', 'minggu' => 'Jatuh tempo 7 hari'])
        ->map(fn ($l, $s) => [$l, request()->fullUrlWithQuery(['status' => $s ?: null]), request('status', '') === $s, null])->values()->all();
@endphp

@section('content')
<x-page-header title="Angsuran & Piutang" subtitle="Transaksi aktif yang belum lunas." :breadcrumbs="['Penjualan' => null, 'Angsuran & Piutang' => null]"/>

<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <x-stat-card label="Total Piutang" :value="rupiah($stats['piutang'])" :singkat="rupiah_singkat($stats['piutang'])" tone="utama" icon="wallet"/>
    <x-stat-card label="Tunggakan" :value="rupiah($stats['tunggakan'])" :singkat="rupiah_singkat($stats['tunggakan'])" :tone="$stats['tunggakan'] > 0 ? 'bahaya' : 'default'" icon="warning"/>
    <x-stat-card label="Terlambat" :value="$stats['terlambat'] . ' transaksi'" icon="clock"/>
    <x-stat-card label="Jatuh Tempo 7 Hari" :value="rupiah($stats['minggu'])" :singkat="rupiah_singkat($stats['minggu'])" icon="calendar"/>
</div>

<x-tabs :items="$tabs"/>

<div class="card">
    <x-filter-bar placeholder="Cari transaksi, konsumen, kavling…" :abaikan="['status']">
        @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
        <x-select name="jenis" :options="['angsuran' => 'Angsuran', 'cash' => 'Cash']" :value="request('jenis')" placeholder="Semua metode"/>
    </x-filter-bar>

    @if ($baris->isEmpty())
        <x-empty-state icon="check-circle" title="Tidak ada piutang" message="Tidak ada yang cocok dengan filter."/>
    @else
        <div class="table-wrap max-md:hidden">
            <table class="table">
                <thead><tr><th>Konsumen</th><th>Metode</th><th class="text-right">Harga</th><th class="text-right">Sisa</th><th>Cicilan Berikutnya</th><th class="text-right">Tunggakan</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($baris as $b)
                        <tr @class(['bg-red-50/50' => $b->telat])>
                            <td><a href="{{ route('transaksi-penjualan.show', $b->t) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ $b->t->konsumen->nama_lengkap }}</a>
                                <div class="text-xs text-slate-500">{{ $b->t->kode_transaksi }} · {{ $b->t->kavling->kode_kavling }} · {{ $b->t->konsumen->no_hp }}</div></td>
                            <td>{{ $b->t->isAngsuran() ? $b->lunas_ke . '/' . $b->jumlah . ' cicilan' : 'Cash' }}</td>
                            <td class="text-right tabular-nums">{{ rupiah($b->t->nilai_jual) }}</td>
                            <td class="text-right font-medium tabular-nums">{{ rupiah($b->sisa) }}</td>
                            <td>
                                @if ($b->berikut)
                                    <span class="tabular-nums">{{ rupiah($b->berikut->sisa) }}</span><div class="text-xs text-slate-500">Ke-{{ $b->berikut->ke }} · {{ tanggal($b->berikut->jatuh_tempo) }}</div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="text-right tabular-nums {{ $b->tunggakan > 0 ? 'font-semibold text-red-600' : 'text-slate-400' }}">{{ $b->tunggakan > 0 ? rupiah($b->tunggakan) : '—' }}</td>
                            <td>@if ($b->telat)<x-badge status="terlambat" :label="'Telat ' . $b->hari_telat . ' hari'"/>@else<x-badge :status="$b->t->status"/>@endif</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot><tr><td colspan="3">Total</td><td class="text-right tabular-nums">{{ rupiah($baris->sum('sisa')) }}</td><td></td><td class="text-right tabular-nums">{{ rupiah($baris->sum('tunggakan')) }}</td><td></td></tr></tfoot>
            </table>
        </div>

        <ul class="divide-y divide-slate-100 md:hidden">
            @foreach ($baris as $b)
                <li>
                    <a href="{{ route('transaksi-penjualan.show', $b->t) }}" class="block px-4 py-3.5 active:bg-slate-50">
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate font-medium text-slate-900">{{ $b->t->konsumen->nama_lengkap }}</span>
                            @if ($b->telat)<x-badge status="terlambat" :label="'Telat ' . $b->hari_telat . ' hr'"/>@else<x-badge :status="$b->t->status"/>@endif
                        </div>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $b->t->kavling->kode_kavling }} · {{ $b->t->isAngsuran() ? $b->lunas_ke . '/' . $b->jumlah . ' cicilan' : 'Cash' }}</p>
                        <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                            <div><span class="text-slate-500">Sisa</span><p class="font-semibold text-slate-900 tabular-nums">{{ rupiah($b->sisa) }}</p></div>
                            <div class="text-right"><span class="text-slate-500">{{ $b->tunggakan > 0 ? 'Tunggakan' : 'Berikutnya' }}</span>
                                <p @class(['font-semibold tabular-nums', 'text-red-600' => $b->tunggakan > 0, 'text-slate-900' => ! $b->tunggakan])>{{ $b->tunggakan > 0 ? rupiah($b->tunggakan) : ($b->berikut ? rupiah($b->berikut->sisa) . ' · ' . tanggal($b->berikut->jatuh_tempo, 'j M') : '—') }}</p></div>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
