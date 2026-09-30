@extends('layouts.app')
@section('title', $konsumen->nama_lengkap)

@section('content')
<x-page-header :title="$konsumen->nama_lengkap" :subtitle="$konsumen->id_konsumen . ($konsumen->label ? ' · ' . $konsumen->label : '')"
               :back="route('konsumen.index')" :breadcrumbs="['Data Konsumen' => route('konsumen.index'), $konsumen->id_konsumen => null]">
    <x-slot:actions>
        <button type="button" class="btn btn-secondary" x-data x-on:click="$dispatch('open-modal', 'ubah-konsumen')"><x-icon name="pencil" class="h-4 w-4"/> Ubah Data</button>
        <a href="{{ route('transaksi-penjualan.create', ['konsumen' => $konsumen->id]) }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4"/> Transaksi Baru</a>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <x-card title="Identitas" class="lg:col-span-1">
        <dl class="space-y-3 text-sm">
            @foreach ([['NIK', $konsumen->nik], ['No. HP', $konsumen->no_hp], ['Email', $konsumen->email], ['Pekerjaan', $konsumen->pekerjaan], ['Alamat', $konsumen->alamat], ['Catatan', $konsumen->catatan]] as [$l, $v])
                <div>
                    <dt class="text-xs text-slate-500">{{ $l }}</dt>
                    <dd class="mt-0.5 font-medium break-words text-slate-800">{{ $v ?: '—' }}</dd>
                </div>
            @endforeach
        </dl>
    </x-card>

    <div class="space-y-6 lg:col-span-2">
        @forelse ($konsumen->transaksis as $t)
            <x-card>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('transaksi-penjualan.show', $t) }}" class="text-lg font-semibold text-slate-900 hover:text-forest-700">Kavling {{ $t->kavling->kode_kavling }}</a>
                            <x-badge :status="$t->status"/>
                        </div>
                        <p class="mt-0.5 text-sm text-slate-500">{{ $t->kode_transaksi }} · {{ tanggal($t->tanggal) }} · {{ $t->isAngsuran() ? "Angsuran {$t->tenor} bulan" : 'Cash' }} · Agen {{ $t->agen->nama_agen ?? '—' }}</p>
                    </div>
                    <a href="{{ route('transaksi-penjualan.show', $t) }}" class="btn btn-sm btn-secondary">Detail <x-icon name="arrow-right" class="h-3.5 w-3.5"/></a>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                    <div><p class="text-xs text-slate-500">Harga Jual</p><p class="font-semibold tabular-nums">{{ rupiah($t->nilai_jual) }}</p></div>
                    <div><p class="text-xs text-slate-500">Pokok Terbayar</p><p class="font-semibold tabular-nums text-forest-600">{{ rupiah($t->pokokTerbayar()) }}</p></div>
                    <div><p class="text-xs text-slate-500">Sisa</p><p class="font-semibold tabular-nums">{{ rupiah($t->sisa()) }}</p></div>
                    <div><p class="text-xs text-slate-500">Total Uang Masuk</p><p class="font-semibold tabular-nums">{{ rupiah($t->totalMasuk()) }}</p></div>
                </div>
                @unless ($t->isBatal())
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-forest-500" style="width: {{ $t->persenLunas() }}%"></div></div>
                @endunless
            </x-card>
        @empty
            <x-card>
                <x-empty-state title="Belum ada transaksi" message="Konsumen ini belum membeli kavling.">
                    <a href="{{ route('transaksi-penjualan.create', ['konsumen' => $konsumen->id]) }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4"/> Buat Transaksi</a>
                </x-empty-state>
            </x-card>
        @endforelse

        @php $semuaBayar = $konsumen->transaksis->flatMap(fn ($t) => $t->pembayarans->each->setRelation('transaksi', $t))->sortByDesc('tanggal'); @endphp
        @if ($semuaBayar->isNotEmpty())
            <x-card title="Riwayat Pembayaran" :padding="false">
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>No. Kwitansi</th><th>Tanggal</th><th>Kavling</th><th>Jenis</th><th class="text-right">Nominal</th><th class="text-right">Kwitansi</th></tr></thead>
                        <tbody>
                            @foreach ($semuaBayar as $p)
                                <tr>
                                    <td class="font-medium">{{ $p->kode }}</td>
                                    <td class="whitespace-nowrap">{{ tanggal($p->tanggal) }}</td>
                                    <td>{{ $p->transaksi->kavling->kode_kavling }}</td>
                                    <td>{{ $p->label_jenis }}</td>
                                    <td class="text-right tabular-nums">{{ rupiah($p->nominal) }}</td>
                                    <td class="text-right"><a href="{{ route('pembayaran.kwitansi', $p) }}" class="btn-icon" title="Lihat kwitansi"><x-icon name="receipt" class="h-[18px] w-[18px]"/></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif
    </div>
</div>

<x-modal name="ubah-konsumen" title="Ubah Data Konsumen" max-width="xl" :show="$errors->any()">
    <form method="POST" action="{{ route('konsumen.update', $konsumen) }}">
        @csrf @method('PUT')
        <div class="p-5">@include('konsumen._fields', ['k' => $konsumen])</div>
        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'ubah-konsumen')">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</x-modal>
@endsection
