@extends('layouts.app')
@section('title', 'Agen & Marketing')

@php
    $kosong = ['id' => null, 'nama_agen' => '', 'no_hp' => '', 'email' => '', 'komisi_persen' => '', 'aktif' => true, 'catatan' => ''];
    $awal = old('_form') === 'agen' ? array_merge($kosong, request()->old(), ['id' => old('_id'), 'aktif' => (bool) old('aktif')]) : $kosong;
@endphp

@section('content')
<div x-data="{ a: @js($awal), get aksiAgen() { return this.a.id ? '{{ url('data-agen') }}/' + this.a.id : '{{ route('agen.store') }}' } }">

<x-page-header title="Agen & Marketing" subtitle="Lead, prospek, closing, penjualan, dan komisi dihitung otomatis dari lead harian dan transaksi."
               :breadcrumbs="['Data Master' => null, 'Agen & Marketing' => null]">
    <x-slot:actions>
        <a href="{{ route('lead.rekap') }}" class="btn btn-secondary"><x-icon name="chart" class="h-4 w-4"/> Rekap Lead</a>
        <button type="button" class="btn btn-primary" x-on:click="a = @js($kosong); $dispatch('open-modal', 'agen')"><x-icon name="plus" class="h-4 w-4"/> Tambah Agen</button>
    </x-slot:actions>
</x-page-header>

<div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6 sm:gap-4">
    <x-stat-card label="Agen" :value="$stats['agen']" icon="users" tone="dark"/>
    <x-stat-card label="Lead" :value="$stats['lead']"/>
    <x-stat-card label="Prospek" :value="$stats['prospek']"/>
    <x-stat-card label="Closing" :value="$stats['closing']"/>
    <x-stat-card label="Nilai Penjualan" :value="rupiah_singkat($stats['penjualan'])" :hint="rupiah($stats['penjualan'])"/>
    <x-stat-card label="Sisa Komisi" :value="rupiah_singkat($stats['sisa'])" :hint="'Hak ' . rupiah($stats['hak'])" tone="gold"/>
</div>

<div class="card">
    <x-filter-bar placeholder="Cari nama atau kode agen…">
        <x-select name="status" :options="['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']" :value="request('status')" placeholder="Semua status" class="sm:w-40"/>
        <x-select name="urut" :options="['closing' => 'Closing terbanyak', 'penjualan' => 'Penjualan terbesar', 'sisa' => 'Sisa komisi terbesar']" :value="request('urut')" placeholder="Urut kode" class="sm:w-52"/>
    </x-filter-bar>

    @if ($agens->isEmpty())
        <x-empty-state title="Belum ada agen" message="Tambahkan agen marketing untuk mulai mencatat lead dan komisi.">
            <button type="button" class="btn btn-primary" x-on:click="a = @js($kosong); $dispatch('open-modal', 'agen')"><x-icon name="plus" class="h-4 w-4"/> Tambah Agen</button>
        </x-empty-state>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Agen</th><th class="text-right">Lead</th><th class="text-right">Prospek</th><th class="text-right">Closing</th><th class="text-right">Nilai Penjualan</th><th class="text-right">Komisi</th><th class="text-right">Hak / Dibayar</th><th class="text-right">Sisa</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @foreach ($agens as $ag)
                        @php $n = $ag->angka; @endphp
                        <tr>
                            <td>
                                <a href="{{ route('agen.show', $ag) }}" class="font-semibold text-slate-900 hover:text-forest-700">{{ $ag->nama_agen }}</a>
                                @unless ($ag->aktif)<x-badge status="batal" label="Nonaktif" class="ml-1"/>@endunless
                                <div class="text-xs text-slate-500">{{ $ag->kode_agen }}{{ $ag->no_hp ? ' · ' . $ag->no_hp : '' }}</div>
                            </td>
                            <td class="text-right tabular-nums">{{ $n['lead'] }}</td>
                            <td class="text-right tabular-nums">{{ $n['prospek'] }}</td>
                            <td class="text-right font-semibold tabular-nums">{{ $n['closing'] }}</td>
                            <td class="text-right tabular-nums">{{ rupiah($n['nilai_penjualan']) }}<div class="text-xs text-slate-500">{{ $n['transaksi'] }} transaksi</div></td>
                            <td class="text-right tabular-nums">{{ $n['persen'] !== null ? persen($n['persen'], false, 2) : '—' }}</td>
                            <td class="text-right tabular-nums">{{ $n['komisi_hak'] !== null ? rupiah($n['komisi_hak']) : '—' }}<div class="text-xs text-slate-500">dibayar {{ rupiah($n['dibayar']) }}</div></td>
                            <td @class(['text-right font-semibold tabular-nums', 'text-red-600' => ($n['sisa'] ?? 0) < 0])>{{ $n['sisa'] !== null ? rupiah($n['sisa']) : '—' }}</td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('agen.show', $ag) }}" class="btn-icon" title="Detail"><x-icon name="eye" class="h-[18px] w-[18px]"/></a>
                                    <button type="button" class="btn-icon" title="Ubah" x-on:click="a = @js(['id' => $ag->id, 'nama_agen' => $ag->nama_agen, 'no_hp' => $ag->no_hp, 'email' => $ag->email, 'komisi_persen' => $ag->komisi_persen, 'aktif' => $ag->aktif, 'catatan' => $ag->catatan]); $dispatch('open-modal', 'agen')"><x-icon name="pencil" class="h-[18px] w-[18px]"/></button>
                                    <x-delete-button :action="route('agen.destroy', $ag)" title="Hapus agen {{ $ag->nama_agen }}?" message="Agen yang sudah punya lead, transaksi, atau komisi tidak bisa dihapus — nonaktifkan saja."/>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    @if (\App\Services\Pengaturan::get('komisi_default_persen') === null)
        <p class="border-t border-slate-100 px-5 py-3 text-xs text-amber-700">Komisi bawaan belum diatur di <a href="{{ route('proyek.index') }}#grup-penjualan" class="underline">Pengaturan Proyek</a>. Agen tanpa persen sendiri ditampilkan "—".</p>
    @endif
</div>

@include('agen._modal')
</div>
@endsection
