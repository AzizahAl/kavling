@extends('layouts.app')
@section('title', 'Agen')

@php
    $kosong = ['id' => null, 'nama_agen' => '', 'no_hp' => '', 'email' => '', 'komisi_nominal' => '', 'aktif' => true, 'catatan' => ''];
    $awal = old('_form') === 'agen' ? array_merge($kosong, request()->old(), ['id' => old('_id'), 'aktif' => (bool) old('aktif')]) : $kosong;
@endphp

@section('content')
<div x-data="{ a: @js($awal), kosong: @js($kosong), get aksiAgen() { return this.a.id ? '{{ url('data-agen') }}/' + this.a.id : '{{ route('agen.store') }}' } }">

<x-page-header title="Agen" subtitle="Kinerja & komisi dihitung dari lead dan transaksi." :breadcrumbs="['Data Master' => null, 'Agen' => null]">
    <x-slot:actions>
        <x-button variant="secondary" icon="chart" :href="route('lead.rekap')">Rekap Lead</x-button>
        <x-button icon="plus" x-on:click="a = { ...kosong }; $dispatch('open-modal', 'agen')">Tambah Agen</x-button>
    </x-slot:actions>
</x-page-header>

<div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
    <x-stat-card label="Agen" :value="$stats['agen']" icon="users" tone="utama"/>
    <x-stat-card label="Lead" :value="$stats['lead']"/>
    <x-stat-card label="Prospek" :value="$stats['prospek']"/>
    <x-stat-card label="Closing" :value="$stats['closing']"/>
    <x-stat-card label="Penjualan" :value="rupiah($stats['penjualan'])" :singkat="rupiah_singkat($stats['penjualan'])"/>
    <x-stat-card label="Sisa Komisi" :value="rupiah($stats['sisa'])" :singkat="rupiah_singkat($stats['sisa'])" :hint="'Hak ' . rupiah_singkat($stats['hak'])"/>
</div>

<div class="card">
    <x-filter-bar placeholder="Cari nama atau kode…">
        <x-select name="status" :options="['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']" :value="request('status')" placeholder="Semua status"/>
        <x-select name="urut" :options="['closing' => 'Closing terbanyak', 'penjualan' => 'Penjualan terbesar', 'sisa' => 'Sisa komisi terbesar']" :value="request('urut')" placeholder="Urut kode"/>
    </x-filter-bar>

    @if ($agens->isEmpty())
        <x-empty-state title="Belum ada agen">
            <x-button icon="plus" x-on:click="a = { ...kosong }; $dispatch('open-modal', 'agen')">Tambah Agen</x-button>
        </x-empty-state>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Agen</th><th class="text-right">Lead</th><th class="text-right">Prospek</th><th class="text-right">Closing</th><th class="text-right">Penjualan</th><th class="text-right">Komisi</th><th class="text-right">Hak / Dibayar</th><th class="text-right">Sisa</th><th class="w-px"></th></tr></thead>
                <tbody>
                    @foreach ($agens as $ag)
                        @php $n = $ag->angka; @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('agen.show', $ag) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ $ag->nama_agen }}</a>
                                    @unless ($ag->aktif)<x-badge status="nonaktif"/>@endunless
                                </div>
                                <div class="text-xs text-slate-500">{{ $ag->kode_agen }}{{ $ag->no_hp ? ' · ' . $ag->no_hp : '' }}</div>
                            </td>
                            <td class="text-right tabular-nums">{{ $n['lead'] }}</td>
                            <td class="text-right tabular-nums">{{ $n['prospek'] }}</td>
                            <td class="text-right font-semibold tabular-nums">{{ $n['closing'] }}</td>
                            <td class="text-right tabular-nums">{{ rupiah($n['nilai_penjualan']) }}<div class="text-xs text-slate-500">{{ $n['transaksi'] }} transaksi</div></td>
                            <td class="text-right tabular-nums">{{ rupiah($n['nominal']) }}<div class="text-xs text-slate-500">per transaksi</div></td>
                            <td class="text-right tabular-nums">{{ rupiah($n['komisi_hak']) }}<div class="text-xs text-slate-500">{{ rupiah($n['dibayar']) }}</div></td>
                            <td @class(['text-right font-semibold tabular-nums', 'text-red-600' => ($n['sisa'] ?? 0) < 0])>{{ rupiah($n['sisa']) }}</td>
                            <td>
                                <div class="flex justify-end gap-0.5">
                                    <x-icon-button icon="eye" label="Detail" :href="route('agen.show', $ag)"/>
                                    <x-icon-button icon="pencil" label="Ubah" x-on:click="a = {{ Js::from(['id' => $ag->id, 'nama_agen' => $ag->nama_agen, 'no_hp' => $ag->no_hp, 'email' => $ag->email, 'komisi_nominal' => $ag->komisi_nominal, 'aktif' => $ag->aktif, 'catatan' => $ag->catatan]) }}; $dispatch('open-modal', 'agen')"/>
                                    <x-delete-button :action="route('agen.destroy', $ag)" :title="'Hapus agen ' . $ag->nama_agen . '?'" message="Agen yang punya lead, transaksi, atau komisi tidak bisa dihapus. Nonaktifkan saja."/>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@include('agen._modal')
</div>
@endsection
