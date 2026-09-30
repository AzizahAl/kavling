@extends('layouts.app')
@section('title', 'Checklist Legal')

@php
    $awal = old('_id') ? array_merge(request()->old(), ['id' => old('_id'), 'kavling' => old('_kavling')]) : null;
@endphp

@section('content')
<div x-data="{
        c: @js($awal ?? ['id' => null]),
        get action() { return '{{ url('checklist-legal') }}/' + this.c.id },
        ubah(d) { this.c = { ...d }; this.$dispatch('open-modal', 'legal') },
     }">

<x-page-header title="Checklist Legal & Dokumen" subtitle="Status dokumen per kavling. PPJB yang ditandai Selesai membuat kavling berstatus Terjual."
               :breadcrumbs="['Dokumen' => null, 'Checklist Legal' => null]"/>

<div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 sm:gap-4">
    @foreach (\App\Models\ChecklistLegal::ITEM as $item => $label)
        <x-stat-card :label="$label . ' Selesai'" :value="$ringkas[$item] . ' / ' . $kavlings->filter(fn ($k) => $k->transaksiAktif)->count()" :tone="$item === 'ppjb' ? 'dark' : 'default'"/>
    @endforeach
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Kavling</th><th>Pembeli</th>@foreach (\App\Models\ChecklistLegal::ITEM as $l)<th>{{ $l }}</th>@endforeach<th class="text-right">Aksi</th></tr></thead>
            <tbody>
                @foreach ($kavlings as $kv)
                    @php $t = $kv->transaksiAktif; $c = $t?->checklist; @endphp
                    <tr>
                        <td><a href="{{ route('kavling.show', $kv) }}" class="font-semibold text-slate-900 hover:text-forest-700">{{ $kv->kode_kavling }}</a>
                            <div><x-badge :status="$kv->status" :label="$kv->label_status"/></div></td>
                        <td>
                            @if ($t)
                                <a href="{{ route('transaksi-penjualan.show', $t) }}" class="font-medium hover:text-forest-700">{{ $t->konsumen->nama_lengkap }}</a>
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
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('dokumen.lihat', [$t, 'spk']) }}" class="btn btn-sm btn-ghost" title="Surat Pemesanan Kavling">SPK</a>
                                    <a href="{{ route('dokumen.lihat', [$t, 'ppjb']) }}" class="btn btn-sm btn-ghost" title="Perjanjian Pengikatan Jual Beli">PPJB</a>
                                    <button type="button" class="btn-icon" title="Ubah checklist"
                                            x-on:click="ubah(@js(collect(array_keys(\App\Models\ChecklistLegal::ITEM))->flatMap(fn ($i) => [$i . '_status' => $c->{$i . '_status'}, $i . '_tanggal' => $c->{$i . '_tanggal'}?->toDateString()])->merge(['id' => $c->id, 'kavling' => $kv->kode_kavling, 'catatan' => $c->catatan])))"><x-icon name="pencil" class="h-[18px] w-[18px]"/></button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<x-modal name="legal" max-width="lg" :show="(bool) old('_id') && $errors->any()">
    <form method="POST" :action="action">
        @csrf @method('PUT')
        <input type="hidden" name="_id" :value="c.id"><input type="hidden" name="_kavling" :value="c.kavling">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-semibold text-slate-900" x-text="'Checklist Legal ' + (c.kavling || '')"></h3>
            <button type="button" class="btn-icon -mr-2" x-on:click="$dispatch('close-modal', 'legal')"><x-icon name="x"/></button>
        </div>
        <div class="space-y-4 p-5">
            @foreach (\App\Models\ChecklistLegal::ITEM as $item => $label)
                <div class="grid grid-cols-2 gap-3">
                    <x-field :label="$label" :name="$item . '_status'">
                        <select name="{{ $item }}_status" x-model="c.{{ $item }}_status" class="form-input">
                            <option value="belum">Belum</option><option value="proses">Proses</option><option value="selesai">Selesai</option>
                        </select>
                    </x-field>
                    <x-field label="Tanggal" :name="$item . '_tanggal'">
                        <input type="date" name="{{ $item }}_tanggal" x-model="c.{{ $item }}_tanggal" max="{{ now()->toDateString() }}" class="form-input">
                    </x-field>
                </div>
            @endforeach
            <p class="rounded-lg bg-gold-50 px-3 py-2 text-xs text-gold-800">PPJB = Selesai berarti PPJB sudah ditandatangani: kavling menjadi Terjual, dihitung untuk kenaikan tahap harga, dan komisi agen menjadi hak.</p>
            <x-field label="Catatan" name="catatan"><input type="text" name="catatan" x-model="c.catatan" class="form-input"></x-field>
        </div>
        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'legal')">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</x-modal>
</div>
@endsection
