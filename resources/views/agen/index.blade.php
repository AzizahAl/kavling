@extends('layouts.app')
@section('title', 'Data Agen & Marketing')

@php
    $kosong = ['id' => null, 'kode' => $kodeBaru, 'nama_agen' => '', 'no_hp' => '', 'komisi_nominal' => '', 'login' => '', 'aktif' => true, 'punya_akun' => false];
    $awal = old('_form') === 'agen'
        ? ['id' => old('_id'), 'kode' => old('_kode', $kodeBaru), 'nama_agen' => old('nama_agen'), 'no_hp' => old('no_hp'), 'komisi_nominal' => old('komisi_nominal'), 'login' => old('login'), 'aktif' => (bool) old('aktif'), 'punya_akun' => (bool) old('_punya_akun')]
        : $kosong;
    $dataForm = fn ($ag) => ['id' => $ag->id, 'kode' => $ag->kode_agen, 'nama_agen' => $ag->nama_agen, 'no_hp' => $ag->no_hp, 'komisi_nominal' => $ag->komisi_nominal !== null ? (float) $ag->komisi_nominal : '', 'login' => $ag->user?->email ?? '', 'aktif' => $ag->user ? $ag->user->aktif : $ag->aktif, 'punya_akun' => (bool) $ag->user];
    $jumlahFilter = collect(request()->only(['akun', 'sisa']))->filter(fn ($v) => filled($v))->count();
    $warnaSisa = fn ($sisa) => $sisa > 0 ? 'text-red-600' : 'text-slate-500';
@endphp

@section('content')
<div x-data="{ a: @js($awal), kosong: @js($kosong), get aksiAgen() { return this.a.id ? '{{ url('data-agen') }}/' + this.a.id : '{{ route('agen.store') }}' } }">

@include('agen._navigasi')
<x-page-header title="Data Agen & Marketing" subtitle="Kinerja, komisi, dan akun login agen. Semua angka dihitung dari lead, transaksi, dan pembayaran komisi."
               :breadcrumbs="['Data Master' => null, 'Agen & Marketing' => null]"/>

<div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
    <x-stat-card label="Total Agen" :value="$stats['agen']" icon="users" tone="utama"/>
    <x-stat-card label="Total Lead" :value="angka($stats['lead'])" icon="funnel"/>
    <x-stat-card label="Total Prospek" :value="angka($stats['prospek'])"/>
    <x-stat-card label="Total Closing" :value="angka($stats['closing'])" icon="check-badge"/>
    <x-stat-card label="Nilai Penjualan" :value="rupiah($stats['penjualan'])" :singkat="rupiah_singkat($stats['penjualan'])" icon="cart"/>
    <x-stat-card label="Total Komisi" :value="rupiah($stats['komisi'])" :singkat="rupiah_singkat($stats['komisi'])" icon="wallet"/>
</div>

<div class="card">
    {{-- Panel: tambah, cari, filter, jumlah data --}}
    <form method="GET" action="{{ route('agen.index') }}" role="search" x-data="{ filter: false }"
          x-on:change="if ($event.target.tagName === 'SELECT') $el.requestSubmit()"
          class="flex flex-wrap items-center gap-2.5 border-b border-slate-100 p-3 sm:gap-3 sm:p-4">
        <x-button icon="plus" x-on:click="a = { ...kosong }; $dispatch('open-modal', 'agen')" class="max-sm:w-full">Tambah Agen</x-button>
        <div class="relative min-w-0 flex-1 sm:max-w-xs">
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"/>
            <input type="search" name="cari" value="{{ request('cari') }}" placeholder="Cari nama atau kode…" class="form-input pl-9" enterkeyhint="search" aria-label="Cari nama atau kode">
        </div>
        <div class="relative" x-on:click.outside="filter = false" x-on:keydown.escape="filter = false">
            <button type="button" class="btn btn-secondary px-3" x-on:click="filter = ! filter" :aria-expanded="filter">
                <x-icon name="sliders" class="size-4"/> Filter
                @if ($jumlahFilter)<span class="flex size-5 items-center justify-center rounded-full bg-brand-600 text-[11px] font-semibold text-white">{{ $jumlahFilter }}</span>@endif
            </button>
            <div x-show="filter" x-cloak x-transition.opacity.duration.150ms
                 class="absolute right-0 z-20 mt-2 w-64 space-y-3 rounded-kartu border border-slate-200 bg-white p-4 shadow-menu max-sm:right-auto max-sm:left-0">
                <x-field label="Status Akun" name="akun">
                    <x-select name="akun" :options="['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'tanpa' => 'Belum punya akun']" :value="request('akun')" placeholder="Semua"/>
                </x-field>
                <x-field label="Sisa Komisi" name="sisa">
                    <x-select name="sisa" :options="['ada' => 'Masih ada sisa', 'lunas' => 'Tidak ada sisa']" :value="request('sisa')" placeholder="Semua"/>
                </x-field>
            </div>
        </div>
        <button type="submit" class="sr-only">Cari</button>
        <div class="flex w-full items-center justify-between gap-2 text-sm text-slate-500 sm:ml-auto sm:w-auto">
            <span>Menampilkan <span class="font-medium text-slate-700">{{ $agens->count() }}</span> dari {{ $semua }} agen</span>
            @if ($jumlahFilter || filled(request('cari')))
                <a href="{{ route('agen.index') }}" class="btn btn-ghost btn-sm text-slate-500"><x-icon name="filter-x" class="size-4"/> Hapus filter</a>
            @endif
        </div>
    </form>

    @if ($agens->isEmpty())
        <x-empty-state :title="$semua ? 'Tidak ada agen yang cocok' : 'Belum ada agen'" :message="$semua ? 'Ubah pencarian atau filter.' : 'Tambah agen beserta akun login-nya.'"/>
    @else
        {{-- Laptop & tablet: tabel --}}
        <div class="table-wrap max-md:hidden">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode Agen</th><th>Nama Agen</th><th>No HP</th>
                        <th class="text-right">Lead</th><th class="text-right">Prospek</th><th class="text-right">Closing</th>
                        <th class="text-right">Nilai Penjualan</th><th class="text-right">Komisi</th><th class="text-right">Komisi Terhitung</th>
                        <th class="text-right">Dibayar</th><th class="text-right">Sisa Komisi</th><th class="w-px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($agens as $ag)
                        @php $n = $ag->angka; @endphp
                        <tr>
                            <td class="whitespace-nowrap font-medium text-slate-900 tabular-nums">{{ $ag->kode_agen }}</td>
                            <td>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <a href="{{ route('agen.show', $ag) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ $ag->nama_agen }}</a>
                                    @include('agen._status-akun')
                                </div>
                            </td>
                            <td class="whitespace-nowrap">{{ $ag->no_hp ?: '—' }}</td>
                            <td class="text-right tabular-nums">{{ angka($n['lead']) }}</td>
                            <td class="text-right tabular-nums">{{ angka($n['prospek']) }}</td>
                            <td class="text-right font-semibold tabular-nums">{{ angka($n['closing']) }}</td>
                            <td class="whitespace-nowrap text-right tabular-nums">{{ rupiah($n['nilai_penjualan']) }}</td>
                            <td class="whitespace-nowrap text-right tabular-nums">{{ rupiah($n['nominal']) }}</td>
                            <td class="whitespace-nowrap text-right tabular-nums">{{ rupiah($n['komisi_hak']) }}</td>
                            <td class="whitespace-nowrap text-right font-medium text-green-700 tabular-nums">{{ rupiah($n['dibayar']) }}</td>
                            <td class="whitespace-nowrap text-right font-semibold tabular-nums {{ $warnaSisa($n['sisa']) }}">{{ rupiah($n['sisa']) }}</td>
                            <td>@include('agen._aksi')</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- HP: kartu --}}
        <ul class="divide-y divide-slate-100 md:hidden">
            @foreach ($agens as $ag)
                @php $n = $ag->angka; @endphp
                <li class="px-4 py-3.5">
                    <div class="flex items-start gap-3">
                        <a href="{{ route('agen.show', $ag) }}" class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="font-semibold text-slate-900">{{ $ag->nama_agen }}</span>
                                @include('agen._status-akun')
                            </div>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $ag->kode_agen }} · {{ $ag->no_hp ?: '—' }}</p>
                        </a>
                        @include('agen._aksi')
                    </div>
                    <dl class="mt-2.5 grid grid-cols-3 gap-x-3 gap-y-2 text-xs">
                        <div><dt class="text-slate-500">Lead</dt><dd class="font-medium text-slate-900 tabular-nums">{{ angka($n['lead']) }}</dd></div>
                        <div><dt class="text-slate-500">Prospek</dt><dd class="font-medium text-slate-900 tabular-nums">{{ angka($n['prospek']) }}</dd></div>
                        <div><dt class="text-slate-500">Closing</dt><dd class="font-semibold text-slate-900 tabular-nums">{{ angka($n['closing']) }}</dd></div>
                        <div class="col-span-2"><dt class="text-slate-500">Nilai Penjualan</dt><dd class="font-medium text-slate-900 tabular-nums">{{ rupiah($n['nilai_penjualan']) }}</dd></div>
                        <div><dt class="text-slate-500">Komisi</dt><dd class="font-medium text-slate-900 tabular-nums">{{ rupiah($n['nominal']) }}</dd></div>
                        <div><dt class="text-slate-500">Terhitung</dt><dd class="font-medium text-slate-900 tabular-nums">{{ rupiah($n['komisi_hak']) }}</dd></div>
                        <div><dt class="text-slate-500">Dibayar</dt><dd class="font-medium text-green-700 tabular-nums">{{ rupiah($n['dibayar']) }}</dd></div>
                        <div><dt class="text-slate-500">Sisa</dt><dd class="font-semibold tabular-nums {{ $warnaSisa($n['sisa']) }}">{{ rupiah($n['sisa']) }}</dd></div>
                    </dl>
                </li>
            @endforeach
        </ul>
    @endif
</div>

@include('agen._modal')
</div>
@endsection
