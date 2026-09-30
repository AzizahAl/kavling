@extends('layouts.app')
@section('title', 'Kavling ' . $kavling->kode_kavling)

@section('content')
@php $aktif = $kavling->transaksiPenjualans->firstWhere('status', '!=', 'batal'); @endphp

<x-page-header :title="'Kavling ' . $kavling->kode_kavling" :subtitle="$kavling->tipe . ($kavling->ukuran ? ' · ' . $kavling->ukuran : '')"
               :back="route('kavling.index')" :breadcrumbs="['Kavling' => route('kavling.index'), $kavling->kode_kavling => null]">
    <x-slot:actions>
        @if (! $aktif && $kavling->harga_jual)
            <x-button icon="plus" :href="route('transaksi-penjualan.create', ['kavling' => $kavling->id])">Transaksi Baru</x-button>
        @endif
    </x-slot:actions>
</x-page-header>

<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <x-stat-card label="Status" :value="$kavling->label_status" icon="grid"/>
    <x-stat-card label="Luas" :value="$kavling->luas ? angka($kavling->luas) . ' m²' : 'Belum final'"/>
    <x-stat-card :label="$aktif ? 'Harga/m² (terkunci)' : 'Harga/m²'" :value="rupiah($aktif?->harga_per_m2 ?? $kavling->harga_per_m2)"/>
    @php $hj = $aktif?->nilai_jual ?? $kavling->harga_jual; @endphp
    <x-stat-card label="Harga Jual" :value="$hj ? rupiah($hj) : '—'" :singkat="$hj ? rupiah_singkat($hj) : '—'" tone="utama"/>
</div>

@if ($kavling->catatan)
    <div class="mb-5 flex items-start gap-2 rounded-kartu border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-kartu">
        <x-icon name="info" class="mt-0.5 size-4 text-slate-400"/>{{ $kavling->catatan }}
    </div>
@endif

<x-card title="Riwayat Transaksi" :padding="false">
    @if ($kavling->transaksiPenjualans->isEmpty())
        <x-empty-state title="Belum ada transaksi"/>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Transaksi</th><th>Tanggal</th><th>Konsumen</th><th>Agen</th><th class="text-right">Harga Jual</th><th class="text-right">Terbayar</th><th>Status</th><th>PPJB</th></tr></thead>
                <tbody>
                    @foreach ($kavling->transaksiPenjualans as $t)
                        <tr>
                            <td><a href="{{ route('transaksi-penjualan.show', $t) }}" class="tautan">{{ $t->kode_transaksi }}</a></td>
                            <td>{{ tanggal($t->tanggal) }}</td>
                            <td>{{ $t->konsumen->nama_lengkap }}</td>
                            <td>{{ $t->agen->nama_agen ?? '—' }}</td>
                            <td class="text-right tabular-nums">{{ rupiah($t->nilai_jual) }}</td>
                            <td class="text-right tabular-nums">{{ rupiah($t->pokokTerbayar()) }}</td>
                            <td><x-badge :status="$t->status"/></td>
                            <td><x-badge :status="$t->checklist->ppjb_status ?? 'belum'"/></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-card>
@endsection
