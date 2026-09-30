@extends('layouts.app')
@section('title', 'Kavling ' . $kavling->kode_kavling)

@section('content')
@php $aktif = $kavling->transaksiPenjualans->firstWhere('status', '!=', 'batal'); @endphp

<x-page-header :title="'Kavling ' . $kavling->kode_kavling" :subtitle="$kavling->tipe . ($kavling->ukuran ? ' · ' . $kavling->ukuran : '')"
               :back="route('kavling.index')" :breadcrumbs="['Master Kavling' => route('kavling.index'), $kavling->kode_kavling => null]">
    <x-slot:actions>
        @if (! $aktif && $kavling->harga_jual)
            <a href="{{ route('transaksi-penjualan.create', ['kavling' => $kavling->id]) }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4"/> Buat Transaksi</a>
        @endif
    </x-slot:actions>
</x-page-header>

<div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 sm:gap-4">
    <x-stat-card label="Status" :value="$kavling->label_status" icon="grid"/>
    <x-stat-card label="Luas" :value="$kavling->luas ? angka($kavling->luas) . ' m²' : 'Belum final'"/>
    <x-stat-card :label="$aktif ? 'Harga / m² (terkunci)' : 'Harga / m² (tahap aktif)'" :value="rupiah($aktif?->harga_per_m2 ?? $kavling->harga_per_m2)"/>
    <x-stat-card label="Harga Jual" :value="($aktif?->nilai_jual ?? $kavling->harga_jual) ? rupiah($aktif?->nilai_jual ?? $kavling->harga_jual) : '—'" tone="dark"/>
</div>

@if ($kavling->catatan)
    <div class="mb-6 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600"><span class="font-medium text-slate-800">Catatan:</span> {{ $kavling->catatan }}</div>
@endif

<x-card title="Riwayat Transaksi" :padding="false">
    @if ($kavling->transaksiPenjualans->isEmpty())
        <x-empty-state title="Belum ada transaksi" message="Kavling ini belum pernah dipesan."/>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>ID Transaksi</th><th>Tanggal</th><th>Pembeli</th><th>Agen</th><th class="text-right">Harga Jual</th><th class="text-right">Terbayar</th><th>Status</th><th>PPJB</th></tr></thead>
                <tbody>
                    @foreach ($kavling->transaksiPenjualans as $t)
                        <tr>
                            <td><a href="{{ route('transaksi-penjualan.show', $t) }}" class="font-semibold text-forest-700 hover:underline">{{ $t->kode_transaksi }}</a></td>
                            <td class="whitespace-nowrap">{{ tanggal($t->tanggal) }}</td>
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
