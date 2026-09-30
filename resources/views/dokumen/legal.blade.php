@extends('layouts.app')
@section('title', 'Checklist Legal')

@php
    $awal = old('_id') ? array_merge(request()->old(), ['id' => old('_id'), 'kavling' => old('_kavling')]) : null;
    $opsiStatus = ['belum' => 'Belum', 'proses' => 'Proses', 'selesai' => 'Selesai'];
    $aktifCount = $kavlings->filter(fn ($k) => $k->transaksiAktif)->count();
@endphp

@section('content')
<div x-data="{
        c: @js($awal ?? ['id' => null]),
        q: '', blok: '', ppjb: '', hanyaTransaksi: '',
        get action() { return '{{ url('checklist-legal') }}/' + this.c.id },
        ubah(d) { this.c = { ...d }; this.$dispatch('open-modal', 'legal') },
        tampil(teks, blok, ppjb, ada) {
            return (! this.q || teks.toLowerCase().includes(this.q.toLowerCase())) && (! this.blok || this.blok === blok)
                && (! this.ppjb || this.ppjb === ppjb) && (! this.hanyaTransaksi || (this.hanyaTransaksi === 'ada') === ada);
        },
        get adaFilter() { return this.q || this.blok || this.ppjb || this.hanyaTransaksi },
     }">

<x-page-header title="Checklist Legal" subtitle="PPJB selesai = kavling Terjual." :breadcrumbs="['Dokumen' => null, 'Checklist Legal' => null]"/>

<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach (\App\Models\ChecklistLegal::ITEM as $item => $label)
        <x-stat-card :label="$label" :value="$ringkas[$item] . ' / ' . $aktifCount" hint="selesai" :tone="$item === 'ppjb' ? 'utama' : 'default'"/>
    @endforeach
</div>

<div class="card">
    <div class="flex flex-col gap-2.5 border-b border-slate-100 p-3 sm:flex-row sm:flex-wrap sm:items-center sm:gap-3 sm:p-4">
        <div class="relative min-w-0 flex-1 sm:max-w-xs">
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"/>
            <input type="search" x-model="q" placeholder="Cari kavling atau pembeli…" class="form-input pl-9" aria-label="Cari">
        </div>
        <div class="grid grid-cols-2 gap-2.5 sm:flex sm:gap-3">
            <x-select name="f_blok" :options="$kavlings->pluck('blok')->unique()->sort()->mapWithKeys(fn ($b) => [$b => 'Blok ' . $b])" placeholder="Semua blok" x-model="blok" class="sm:w-40"/>
            <x-select name="f_ppjb" :options="$opsiStatus" placeholder="Semua PPJB" x-model="ppjb" class="sm:w-40"/>
            <x-select name="f_trx" :options="['ada' => 'Ada transaksi', 'tidak' => 'Belum ada']" placeholder="Semua kavling" x-model="hanyaTransaksi" class="col-span-2 sm:w-44"/>
        </div>
        <button type="button" class="btn btn-ghost btn-sm text-slate-500 sm:ml-auto" x-show="adaFilter" x-cloak x-on:click="q = ''; blok = ''; ppjb = ''; hanyaTransaksi = ''">
            <x-icon name="filter-x" class="size-4"/> Hapus filter
        </button>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Kavling</th><th>Pembeli</th>@foreach (\App\Models\ChecklistLegal::ITEM as $l)<th>{{ $l }}</th>@endforeach<th class="w-px"></th></tr></thead>
            <tbody>
                @foreach ($kavlings as $kv)
                    @php $t = $kv->transaksiAktif; $c = $t?->checklist; @endphp
                    <tr x-show="tampil({{ Js::from($kv->kode_kavling . ' ' . ($t?->konsumen->nama_lengkap ?? '')) }}, '{{ $kv->blok }}', '{{ $c->ppjb_status ?? '' }}', {{ $c ? 'true' : 'false' }})">
                        <td><a href="{{ route('kavling.show', $kv) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ $kv->kode_kavling }}</a>
                            <div class="mt-0.5"><x-badge :status="$kv->status" :label="$kv->label_status"/></div></td>
                        <td>
                            @if ($t)
                                <a href="{{ route('transaksi-penjualan.show', $t) }}" class="font-medium text-slate-800 hover:text-brand-700">{{ $t->konsumen->nama_lengkap }}</a>
                                <div class="text-xs text-slate-500">{{ $t->kode_transaksi }}</div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        @foreach (array_keys(\App\Models\ChecklistLegal::ITEM) as $item)
                            <td>
                                @if ($c)
                                    <x-badge :status="$c->{$item . '_status'}"/>
                                    @if ($c->{$item . '_tanggal'})<div class="mt-0.5 text-xs text-slate-500">{{ tanggal($c->{$item . '_tanggal'}) }}</div>@endif
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        @endforeach
                        <td>
                            @if ($c)
                                <div class="flex justify-end gap-0.5">
                                    <x-button variant="ghost" size="sm" :href="route('dokumen.lihat', [$t, 'spk'])">SPK</x-button>
                                    <x-button variant="ghost" size="sm" :href="route('dokumen.lihat', [$t, 'ppjb'])">PPJB</x-button>
                                    <x-icon-button icon="pencil" label="Ubah checklist"
                                                   x-on:click="ubah({{ Js::from(collect(array_keys(\App\Models\ChecklistLegal::ITEM))->flatMap(fn ($i) => [$i . '_status' => $c->{$i . '_status'}, $i . '_tanggal' => $c->{$i . '_tanggal'}?->toDateString()])->merge(['id' => $c->id, 'kavling' => $kv->kode_kavling, 'catatan' => $c->catatan])) }})"/>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<x-modal name="legal" judul-js="'Checklist Legal · ' + (c.kavling || '')" :show="(bool) old('_id') && $errors->any()">
    <form method="POST" :action="action" class="flex min-h-0 flex-1 flex-col">
        @csrf @method('PUT')
        <input type="hidden" name="_id" :value="c.id"><input type="hidden" name="_kavling" :value="c.kavling">
        <x-modal-body>
            @foreach (\App\Models\ChecklistLegal::ITEM as $item => $label)
                <div class="grid grid-cols-2 gap-3">
                    <x-field :label="$label" :name="$item . '_status'" required>
                        <x-select :name="$item . '_status'" :options="$opsiStatus" x-model="c.{{ $item }}_status"/>
                    </x-field>
                    <x-field label="Tanggal" :name="$item . '_tanggal'">
                        <x-input type="date" :name="$item . '_tanggal'" x-model="c.{{ $item }}_tanggal" :max="now()->toDateString()"/>
                    </x-field>
                </div>
            @endforeach
            <p class="flex gap-2 rounded-kontrol bg-brand-50 px-3 py-2 text-xs text-brand-800"><x-icon name="info" class="size-4"/>PPJB Selesai: kavling Terjual, tahap harga dihitung ulang, komisi agen jadi hak.</p>
            <x-field label="Catatan" name="catatan"><x-input name="catatan" x-model="c.catatan"/></x-field>
        </x-modal-body>
        <x-modal-footer><x-button type="submit">Simpan</x-button></x-modal-footer>
    </form>
</x-modal>
</div>
@endsection
