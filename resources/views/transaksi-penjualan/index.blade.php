@extends('layouts.app')
@section('title', 'Transaksi Penjualan')

@php
    $labelStatus = ['menunggu' => 'Menunggu Bayar', 'reservasi' => 'Reservasi', 'booking' => 'Booking', 'dp' => 'DP', 'angsuran' => 'Angsuran', 'lunas' => 'Lunas', 'batal' => 'Batal'];
    // [label, nilai lengkap, nilai singkat, kelas warna nilai]
    $kartu = [
        ['Total Transaksi', angka($stats['aktif']), null, 'text-slate-900'],
        ['Reservasi', angka($stats['reservasi']), null, 'text-slate-900'],
        ['Booking', angka($stats['booking']), null, 'text-slate-900'],
        ['DP / Angsuran', angka($stats['dp']), null, 'text-slate-900'],
        ['Total Nilai Jual', rupiah($stats['nilai_jual']), rupiah_singkat($stats['nilai_jual']), 'text-slate-900'],
        ['Total Bayar', rupiah($stats['terbayar']), rupiah_singkat($stats['terbayar']), 'text-green-700'],
        ['Total Sisa', rupiah($stats['piutang']), rupiah_singkat($stats['piutang']), $stats['piutang'] > 0 ? 'text-red-600' : 'text-slate-900'],
        ['Transaksi Lunas', angka($stats['lunas']), null, 'text-slate-900'],
    ];
    $adaFilter = collect(request()->only(['cari', 'status', 'jenis', 'agen', 'periode']))->filter(fn ($v) => filled($v))->isNotEmpty();
@endphp

@section('content')
<x-page-header title="Transaksi Penjualan" subtitle="Total bayar & sisa dihitung dari pembayaran." :breadcrumbs="['Penjualan' => null, 'Transaksi Penjualan' => null]">
    <x-slot:actions>
        <x-button icon="plus" :href="route('transaksi-penjualan.create')">Tambah Transaksi</x-button>
    </x-slot:actions>
</x-page-header>

{{-- Kartu ringkas: 8 kolom di laptop, 4 di tablet, 2 di HP. Tidak termasuk transaksi menunggu pembayaran & batal. --}}
<div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-4 xl:grid-cols-8">
    @foreach ($kartu as [$label, $nilai, $singkat, $warna])
        <div class="min-w-0 rounded-kartu border border-slate-200 bg-white px-3 py-2.5">
            <p class="truncate text-[11px] font-medium text-slate-500">{{ $label }}</p>
            @if ($singkat)
                <p class="mt-0.5 truncate text-base font-semibold tabular-nums {{ $warna }}" x-data x-tip="@js($nilai)" title="{{ $nilai }}">{{ $singkat }}</p>
            @else
                <p class="mt-0.5 truncate text-base font-semibold tabular-nums {{ $warna }}">{{ $nilai }}</p>
            @endif
        </div>
    @endforeach
</div>

@if ($menunggu->isNotEmpty())
    <x-card class="mb-4" title="Menunggu Pembayaran Reservasi" :padding="false">
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

<div class="card">
    {{-- Filter satu baris: terkirim otomatis saat pilihan berubah, pencarian dengan Enter --}}
    <form method="GET" action="{{ route('transaksi-penjualan.index') }}" role="search"
          x-on:change="if ($event.target.type !== 'search') $el.requestSubmit()"
          class="grid grid-cols-2 gap-2 border-b border-slate-100 p-3 sm:grid-cols-3 lg:flex lg:flex-wrap lg:items-center xl:flex-nowrap">
        <div class="relative col-span-2 min-w-0 sm:col-span-3 lg:w-56 lg:flex-none xl:w-60">
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"/>
            <input type="search" name="cari" value="{{ request('cari') }}" placeholder="Cari ID, konsumen, kavling…" class="form-input pl-9" enterkeyhint="search" aria-label="Cari transaksi">
        </div>
        <x-select name="status" :options="$labelStatus" :value="request('status')" placeholder="Semua status" aria-label="Status" class="lg:w-36"/>
        <x-select name="jenis" :options="['cash' => 'Cash', 'angsuran' => 'Angsuran']" :value="request('jenis')" placeholder="Semua metode" aria-label="Metode" class="lg:w-36"/>
        @if (auth()->user()->isAdmin())
            <x-select name="agen" :options="$agens" :value="request('agen')" placeholder="Semua agen" aria-label="Agen" class="lg:w-40"/>
        @endif
        <x-input type="month" name="periode" :value="request('periode')" aria-label="Periode (bulan transaksi)" title="Periode (bulan transaksi)" class="min-w-0 lg:w-44"/>
        <button type="submit" class="sr-only">Cari</button>
        <a href="{{ route('transaksi-penjualan.index') }}" @class(['btn btn-secondary justify-center lg:ml-auto', 'pointer-events-none opacity-50' => ! $adaFilter]) @unless ($adaFilter) aria-disabled="true" tabindex="-1" @endunless>
            <x-icon name="filter-x" class="size-4"/> Reset
        </a>
    </form>

    @if ($transaksis->isEmpty())
        <x-empty-state title="Belum ada transaksi" :message="$adaFilter ? 'Ubah atau reset filter.' : null">
            <x-button icon="plus" :href="route('transaksi-penjualan.create')">Tambah Transaksi</x-button>
        </x-empty-state>
    @else
        {{-- Sel dua baris agar muat di laptop; di HP tabel digulir di dalam wadahnya --}}
        <div class="table-wrap">
            <table class="table min-w-[60rem] [&_td]:px-3 [&_th]:px-3">
                <thead>
                    <tr>
                        <th>ID Transaksi</th><th>Konsumen</th><th>Kavling</th><th>Agen</th><th>Status</th><th>Metode</th>
                        <th class="text-right">Nilai Jual</th><th class="text-right">Total Bayar</th><th class="text-right">Sisa</th><th class="w-px"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaksis as $t)
                        <tr>
                            <td>
                                <a href="{{ route('transaksi-penjualan.show', $t) }}" class="tautan font-medium">{{ $t->kode_transaksi }}</a>
                                <div class="text-xs text-slate-500">{{ tanggal($t->tanggal) }}</div>
                            </td>
                            <td class="max-w-48">
                                <span class="block truncate font-medium text-slate-900" title="{{ $t->konsumen->nama_lengkap }}">{{ $t->konsumen->nama_lengkap }}</span>
                                <div class="text-xs text-slate-500">{{ $t->konsumen->id_konsumen ?? '—' }}</div>
                            </td>
                            <td class="font-medium text-slate-900">{{ $t->kavling->kode_kavling }}</td>
                            <td class="max-w-36"><span class="block truncate" title="{{ $t->agen->nama_agen ?? '' }}">{{ $t->agen->nama_agen ?? '—' }}</span></td>
                            <td><x-status-bayar :t="$t"/></td>
                            <td>
                                {{ $t->isAngsuran() ? 'Angsuran' : 'Cash' }}
                                <div class="text-xs text-slate-500">{{ $t->isAngsuran() ? $t->tenor . ' bulan' : '—' }}</div>
                            </td>
                            <td class="text-right font-medium text-slate-900 tabular-nums">{{ rupiah($t->nilai_jual) }}</td>
                            <td class="text-right text-green-700 tabular-nums">{{ rupiah($t->pokokTerbayar()) }}</td>
                            <td @class(['text-right tabular-nums', 'text-slate-400' => $t->isBatal(), 'font-medium text-red-600' => ! $t->isBatal() && $t->sisa() > 0, 'text-slate-500' => ! $t->isBatal() && $t->sisa() <= 0])>{{ $t->isBatal() ? '—' : rupiah($t->sisa()) }}</td>
                            <td><x-icon-button icon="chevron-right" label="Detail" :href="route('transaksi-penjualan.show', $t)"/></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $transaksis->links() }}
    @endif
</div>
@endsection
