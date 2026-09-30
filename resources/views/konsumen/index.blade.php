@extends('layouts.app')
@section('title', 'Data Konsumen')

@section('content')
<x-page-header title="Data Konsumen" subtitle="Identitas konsumen. Pembelian kavling dicatat di Transaksi Penjualan."
               :breadcrumbs="['Penjualan' => null, 'Data Konsumen' => null]">
    <x-slot:actions>
        <button type="button" class="btn btn-secondary" x-data x-on:click="$dispatch('open-modal', 'konsumen')"><x-icon name="plus" class="h-4 w-4"/> Tambah Konsumen</button>
        <a href="{{ route('transaksi-penjualan.create') }}" class="btn btn-primary"><x-icon name="cart" class="h-4 w-4"/> Transaksi Baru</a>
    </x-slot:actions>
</x-page-header>

<div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 sm:gap-4">
    <x-stat-card label="Total Konsumen" :value="$stats['total']" icon="users" tone="dark"/>
    <x-stat-card label="Punya Transaksi Aktif" :value="$stats['aktif']" icon="cart"/>
    <x-stat-card label="Sudah Lunas" :value="$stats['lunas']" icon="check-circle"/>
    <x-stat-card label="Total Pembayaran Masuk" :value="rupiah_singkat($stats['total_bayar'])" :hint="rupiah($stats['total_bayar'])" icon="banknotes" tone="gold"/>
</div>

<div class="card">
    <x-filter-bar placeholder="Cari nama, ID, NIK, atau HP…">
        <x-select name="status" :options="['aktif' => 'Punya transaksi aktif', 'tanpa' => 'Tanpa transaksi aktif']" :value="request('status')" placeholder="Semua konsumen" class="col-span-2 sm:w-56"/>
    </x-filter-bar>

    @if ($konsumens->isEmpty())
        <x-empty-state title="Belum ada konsumen" message="Tambahkan konsumen baru, atau langsung buat transaksi penjualan.">
            <button type="button" class="btn btn-primary" x-data x-on:click="$dispatch('open-modal', 'konsumen')"><x-icon name="plus" class="h-4 w-4"/> Tambah Konsumen</button>
        </x-empty-state>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Konsumen</th><th>NIK</th><th>Kontak</th><th>Kavling Aktif</th><th class="text-right">Total Bayar</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @foreach ($konsumens as $k)
                        <tr>
                            <td>
                                <a href="{{ route('konsumen.show', $k) }}" class="font-semibold text-slate-900 hover:text-forest-700">{{ $k->nama_lengkap }}</a>
                                <div class="text-xs text-slate-500">{{ $k->id_konsumen }} @if ($k->label)· <span class="text-gold-700">{{ $k->label }}</span>@endif</div>
                            </td>
                            <td class="tabular-nums text-slate-600">{{ $k->nik }}</td>
                            <td class="whitespace-nowrap">{{ $k->no_hp }}</td>
                            <td>
                                @forelse ($k->transaksis as $t)
                                    <a href="{{ route('transaksi-penjualan.show', $t) }}" class="mr-1 inline-flex items-center gap-1">
                                        <span class="font-medium">{{ $t->kavling->kode_kavling }}</span><x-badge :status="$t->status"/>
                                    </a>
                                @empty
                                    <span class="text-slate-400">—</span>
                                @endforelse
                            </td>
                            <td class="text-right tabular-nums">{{ rupiah($k->total_bayar) }}</td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('konsumen.show', $k) }}" class="btn-icon" title="Detail"><x-icon name="eye" class="h-[18px] w-[18px]"/></a>
                                    <x-delete-button :action="route('konsumen.destroy', $k)" title="Hapus {{ $k->nama_lengkap }}?" message="Konsumen yang punya riwayat transaksi tidak bisa dihapus."/>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($konsumens->hasPages())<div class="border-t border-slate-100 px-4 py-3">{{ $konsumens->links() }}</div>@endif
    @endif
</div>

<x-modal name="konsumen" title="Tambah Konsumen" max-width="xl" :show="$errors->any()">
    <form method="POST" action="{{ route('konsumen.store') }}">
        @csrf
        <div class="p-5">@include('konsumen._fields')</div>
        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'konsumen')">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Konsumen</button>
        </div>
    </form>
</x-modal>
@endsection
