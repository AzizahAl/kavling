@extends('layouts.app')
@section('title', 'Angsuran & Piutang')

@section('content')
<x-page-header title="Angsuran & Piutang" subtitle="Sisa pembayaran seluruh transaksi aktif yang belum lunas, diurutkan dari yang paling lama terlambat."
               :breadcrumbs="['Penjualan' => null, 'Angsuran & Piutang' => null]"/>

<div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 sm:gap-4">
    <x-stat-card label="Total Piutang" :value="rupiah_singkat($stats['piutang'])" :hint="rupiah($stats['piutang'])" tone="dark" icon="wallet"/>
    <x-stat-card label="Tunggakan" :value="rupiah($stats['tunggakan'])" :tone="$stats['tunggakan'] > 0 ? 'danger' : 'default'" icon="warning"/>
    <x-stat-card label="Transaksi Terlambat" :value="$stats['terlambat']" icon="clock"/>
    <x-stat-card label="Jatuh Tempo 7 Hari" :value="rupiah($stats['minggu'])" tone="gold" icon="calendar"/>
</div>

<div class="mb-4 flex gap-2 overflow-x-auto pb-1">
    @foreach (['' => 'Semua', 'terlambat' => 'Terlambat', 'minggu' => 'Jatuh tempo 7 hari'] as $s => $l)
        <a href="{{ request()->fullUrlWithQuery(['status' => $s ?: null]) }}" @class(['btn btn-sm', 'btn-primary' => request('status', '') === $s, 'btn-secondary' => request('status', '') !== $s])>{{ $l }}</a>
    @endforeach
</div>

<div class="card">
    <x-filter-bar placeholder="Cari transaksi, konsumen, kavling…">
        @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
        <x-select name="jenis" :options="['angsuran' => 'Angsuran', 'cash' => 'Cash']" :value="request('jenis')" placeholder="Semua metode" class="col-span-2 sm:w-44"/>
    </x-filter-bar>

    @if ($baris->isEmpty())
        <x-empty-state icon="check-circle" title="Tidak ada piutang" message="Tidak ada transaksi aktif yang cocok dengan filter."/>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Transaksi</th><th>Metode</th><th class="text-right">Harga</th><th class="text-right">Sisa</th><th>Cicilan Berikutnya</th><th class="text-right">Tunggakan</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($baris as $b)
                        <tr @class(['bg-red-50/40' => $b->telat])>
                            <td><a href="{{ route('transaksi-penjualan.show', $b->t) }}" class="font-semibold text-forest-700 hover:underline">{{ $b->t->konsumen->nama_lengkap }}</a>
                                <div class="text-xs text-slate-500">{{ $b->t->kode_transaksi }} · {{ $b->t->kavling->kode_kavling }} · {{ $b->t->konsumen->no_hp }}</div></td>
                            <td>{{ $b->t->isAngsuran() ? $b->lunas_ke . '/' . $b->jumlah . ' cicilan' : 'Cash' }}</td>
                            <td class="text-right tabular-nums">{{ rupiah($b->t->nilai_jual) }}</td>
                            <td class="text-right font-medium tabular-nums">{{ rupiah($b->sisa) }}</td>
                            <td>
                                @if ($b->berikut)
                                    {{ rupiah($b->berikut->sisa) }}<div class="text-xs text-slate-500">ke-{{ $b->berikut->ke }} · {{ tanggal($b->berikut->jatuh_tempo) }}</div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="text-right tabular-nums {{ $b->tunggakan > 0 ? 'font-semibold text-red-600' : 'text-slate-400' }}">{{ $b->tunggakan > 0 ? rupiah($b->tunggakan) : '—' }}</td>
                            <td>
                                @if ($b->telat)
                                    <x-badge status="terlambat" :label="'Telat ' . $b->hari_telat . ' hari'"/>
                                @else
                                    <x-badge :status="$b->t->status"/>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot><tr><td colspan="3">Total</td><td class="text-right">{{ rupiah($baris->sum('sisa')) }}</td><td></td><td class="text-right">{{ rupiah($baris->sum('tunggakan')) }}</td><td></td></tr></tfoot>
            </table>
        </div>
    @endif
</div>
@endsection
