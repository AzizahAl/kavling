@extends('layouts.app')
@section('title', $konsumen->nama_lengkap)

@section('content')
@php
    $dokAwal = old('_dok_id')
        ? collect(array_keys(\App\Models\ChecklistLegal::ITEM))->flatMap(fn ($i) => [$i . '_status' => old($i . '_status'), $i . '_tanggal' => old($i . '_tanggal')])
            ->merge(['id' => old('_dok_id'), 'kavling' => old('_dok_kavling'), 'catatan' => old('catatan'), 'spk_alasan' => null, 'url_spk' => null, 'url_ppjb' => null])->all()
        : ['id' => null];
@endphp
<div x-data="{ dok: @js($dokAwal), ubahDokumen(d) { this.dok = { ...d }; this.$dispatch('open-modal', 'dokumen') } }">
<x-page-header :title="$konsumen->nama_lengkap" :subtitle="$konsumen->id_konsumen . ($konsumen->label ? ' · ' . $konsumen->label : '')"
               :back="route('konsumen.index')" :breadcrumbs="['Data Konsumen' => route('konsumen.index'), $konsumen->id_konsumen => null]">
    <x-slot:actions>
        @if ($konsumen->transaksis->isEmpty())
            <x-delete-button :action="route('konsumen.destroy', $konsumen)" :title="'Hapus ' . $konsumen->nama_lengkap . '?'" message="Data konsumen akan dihapus." label="Hapus"/>
        @endif
        <x-button variant="secondary" icon="pencil" x-on:click="$dispatch('open-modal', 'ubah-konsumen')">Ubah</x-button>
        <x-button icon="plus" :href="route('transaksi-penjualan.create', ['konsumen' => $konsumen->id])">Transaksi Baru</x-button>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
    <x-card title="Identitas">
        <dl class="space-y-3 text-sm">
            @foreach ([['NIK', $konsumen->nik], ['No. HP', $konsumen->no_hp], ['Email', $konsumen->email], ['Pekerjaan', $konsumen->pekerjaan], ['Alamat', $konsumen->alamat], ['Catatan', $konsumen->catatan]] as [$l, $v])
                <div>
                    <dt class="text-xs text-slate-500">{{ $l }}</dt>
                    <dd class="mt-0.5 font-medium break-words text-slate-800">{{ $v ?: '—' }}</dd>
                </div>
            @endforeach
        </dl>
    </x-card>

    <div class="min-w-0 space-y-5 lg:col-span-2">
        @forelse ($konsumen->transaksis as $t)
            <x-card>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('transaksi-penjualan.show', $t) }}" class="text-base font-semibold text-slate-900 hover:text-brand-700">Kavling {{ $t->kavling->kode_kavling }}</a>
                            <x-status-bayar :t="$t"/>
                        </div>
                        <p class="mt-0.5 text-sm text-slate-500">{{ $t->kode_transaksi }} · {{ tanggal($t->tanggal) }} · {{ $t->isAngsuran() ? "Angsuran {$t->tenor} bln" : 'Cash' }} · {{ $t->agen->nama_agen ?? 'Tanpa agen' }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if ($t->checklist && ! $t->isBatal())
                            <x-button variant="secondary" size="sm" icon="check-badge" x-on:click="ubahDokumen({{ Js::from($t->checklist->dataForm()) }})">Dokumen</x-button>
                        @endif
                        <x-button variant="secondary" size="sm" :href="route('transaksi-penjualan.show', $t)">Detail <x-icon name="arrow-right" class="size-3.5"/></x-button>
                    </div>
                </div>
                @if ($t->checklist)
                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5 text-xs">
                        @foreach (\App\Models\ChecklistLegal::ITEM as $item => $label)
                            <span class="inline-flex items-center gap-1.5 text-slate-600">{{ $label }} <x-badge :status="$t->checklist->{$item . '_status'} ?? 'belum'"/>@if ($t->checklist->{$item . '_tanggal'})<span class="text-slate-500">{{ tanggal($t->checklist->{$item . '_tanggal'}) }}</span>@endif</span>
                        @endforeach
                    </div>
                @endif
                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                    <div><dt class="text-xs text-slate-500">Harga Jual</dt><dd class="font-semibold tabular-nums">{{ rupiah($t->nilai_jual) }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Terbayar</dt><dd class="font-semibold text-brand-700 tabular-nums">{{ rupiah($t->pokokTerbayar()) }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Sisa</dt><dd class="font-semibold tabular-nums">{{ rupiah($t->sisa()) }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Uang Masuk</dt><dd class="font-semibold tabular-nums">{{ rupiah($t->totalMasuk()) }}</dd></div>
                </dl>
                @unless ($t->isBatal())
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand-500" style="width: {{ $t->persenLunas() }}%"></div></div>
                @endunless
            </x-card>
        @empty
            <x-card>
                <x-empty-state title="Belum ada transaksi">
                    <x-button icon="plus" :href="route('transaksi-penjualan.create', ['konsumen' => $konsumen->id])">Transaksi Baru</x-button>
                </x-empty-state>
            </x-card>
        @endforelse

        @php $semuaBayar = $konsumen->transaksis->flatMap(fn ($t) => $t->pembayarans->each->setRelation('transaksi', $t))->sortByDesc('tanggal'); @endphp
        @if ($semuaBayar->isNotEmpty())
            <x-card title="Riwayat Pembayaran" :padding="false">
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>No. Kwitansi</th><th>Tanggal</th><th>Kavling</th><th>Jenis</th><th class="text-right">Nominal</th><th class="w-px"></th></tr></thead>
                        <tbody>
                            @foreach ($semuaBayar as $p)
                                <tr>
                                    <td class="font-medium text-slate-900">{{ $p->kode }}</td>
                                    <td>{{ tanggal($p->tanggal) }}</td>
                                    <td>{{ $p->transaksi->kavling->kode_kavling }}</td>
                                    <td>{{ $p->label_jenis }}</td>
                                    <td class="text-right tabular-nums">{{ rupiah($p->nominal) }}</td>
                                    <td><x-icon-button icon="receipt" label="Kwitansi" :href="route('pembayaran.kwitansi', $p)"/></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif
    </div>
</div>

<x-modal name="ubah-konsumen" title="Ubah Konsumen" max-width="xl" :show="$errors->any() && ! old('_dok_id')">
    <form method="POST" action="{{ route('konsumen.update', $konsumen) }}" class="flex min-h-0 flex-1 flex-col">
        @csrf @method('PUT')
        <x-modal-body>@include('konsumen._fields', ['k' => $konsumen])</x-modal-body>
        <x-modal-footer><x-button type="submit">Simpan</x-button></x-modal-footer>
    </form>
</x-modal>

@include('konsumen._dokumen-modal')
</div>
@endsection
