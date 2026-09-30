@extends('layouts.app')
@section('title', 'Konsumen')

@section('content')
<div x-data>
<x-page-header title="Konsumen" subtitle="Identitas pembeli. Pembelian dicatat di Transaksi." :breadcrumbs="['Penjualan' => null, 'Konsumen' => null]">
    <x-slot:actions>
        <x-button variant="secondary" icon="plus" x-on:click="$dispatch('open-modal', 'konsumen')">Tambah Konsumen</x-button>
        <x-button icon="cart" :href="route('transaksi-penjualan.create')">Transaksi Baru</x-button>
    </x-slot:actions>
</x-page-header>

<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <x-stat-card label="Konsumen" :value="$stats['total']" icon="users" tone="utama"/>
    <x-stat-card label="Transaksi Aktif" :value="$stats['aktif']" icon="cart"/>
    <x-stat-card label="Lunas" :value="$stats['lunas']" icon="check-circle"/>
    <x-stat-card label="Pembayaran Masuk" :value="rupiah($stats['total_bayar'])" :singkat="rupiah_singkat($stats['total_bayar'])" icon="banknotes"/>
</div>

<div class="card">
    <x-filter-bar placeholder="Cari nama, ID, NIK, HP…">
        <x-select name="status" :options="['aktif' => 'Punya transaksi aktif', 'tanpa' => 'Tanpa transaksi aktif']" :value="request('status')" placeholder="Semua konsumen" class="sm:!w-56"/>
    </x-filter-bar>

    @if ($konsumens->isEmpty())
        <x-empty-state title="Belum ada konsumen" :message="request()->query() ? 'Ubah atau hapus filter.' : null">
            <x-button icon="plus" x-on:click="$dispatch('open-modal', 'konsumen')">Tambah Konsumen</x-button>
        </x-empty-state>
    @else
        <div class="table-wrap max-md:hidden">
            <table class="table">
                <thead><tr><th>Konsumen</th><th>NIK</th><th>No. HP</th><th>Kavling Aktif</th><th class="text-right">Total Bayar</th><th class="w-px"></th></tr></thead>
                <tbody>
                    @foreach ($konsumens as $k)
                        <tr>
                            <td>
                                <a href="{{ route('konsumen.show', $k) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ $k->nama_lengkap }}</a>
                                <div class="text-xs text-slate-500">{{ $k->id_konsumen }}@if ($k->label) · <span class="text-brand-700">{{ $k->label }}</span>@endif</div>
                            </td>
                            <td class="text-slate-600 tabular-nums">{{ $k->nik }}</td>
                            <td>{{ $k->no_hp }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse ($k->transaksis as $t)
                                        <a href="{{ route('transaksi-penjualan.show', $t) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-800 hover:text-brand-700">{{ $t->kavling->kode_kavling }} <x-badge :status="$t->status"/></a>
                                    @empty
                                        <span class="text-slate-400">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="text-right tabular-nums">{{ rupiah($k->total_bayar) }}</td>
                            <td>
                                <div class="flex justify-end gap-0.5">
                                    <x-icon-button icon="eye" label="Detail" :href="route('konsumen.show', $k)"/>
                                    <x-delete-button :action="route('konsumen.destroy', $k)" :title="'Hapus ' . $k->nama_lengkap . '?'" message="Konsumen yang punya riwayat transaksi tidak bisa dihapus."/>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <ul class="divide-y divide-slate-100 md:hidden">
            @foreach ($konsumens as $k)
                <li class="flex items-start gap-3 px-4 py-3.5">
                    <a href="{{ route('konsumen.show', $k) }}" class="min-w-0 flex-1">
                        <p class="truncate font-medium text-slate-900">{{ $k->nama_lengkap }}</p>
                        <p class="text-xs text-slate-500">{{ $k->id_konsumen }} · {{ $k->no_hp }}</p>
                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                            @foreach ($k->transaksis as $t)
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-700">{{ $t->kavling->kode_kavling }} <x-badge :status="$t->status"/></span>
                            @endforeach
                            <span class="text-xs text-slate-500 tabular-nums">{{ rupiah($k->total_bayar) }}</span>
                        </div>
                    </a>
                    <x-delete-button :action="route('konsumen.destroy', $k)" :title="'Hapus ' . $k->nama_lengkap . '?'" message="Konsumen yang punya riwayat transaksi tidak bisa dihapus."/>
                </li>
            @endforeach
        </ul>
        {{ $konsumens->links() }}
    @endif
</div>

<x-modal name="konsumen" title="Tambah Konsumen" max-width="xl" :show="$errors->any()">
    <form method="POST" action="{{ route('konsumen.store') }}" class="flex min-h-0 flex-1 flex-col">
        @csrf
        <x-modal-body>@include('konsumen._fields', ['k' => null])</x-modal-body>
        <x-modal-footer><x-button type="submit">Simpan</x-button></x-modal-footer>
    </form>
</x-modal>
</div>
@endsection
