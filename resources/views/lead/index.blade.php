@extends('layouts.app')
@section('title', 'Lead Harian')

@php
    $kosong = ['id' => null, 'nama' => '', 'no_hp' => '', 'domisili' => '', 'sumber' => 'internet', 'tanggal_lead' => now()->toDateString(), 'agen_id' => (string) ($agens->keys()->first() ?? ''), 'kavling_minat_id' => '', 'catatan' => ''];
    $awal = old('_form') === 'lead'
        ? array_merge($kosong, ['id' => old('_id')], request()->old())
        : $kosong;
    $aksiAwal = in_array(old('_form'), ['prospek', 'closing']) ? ['jenis' => old('_form'), 'id' => old('_id'), 'nama' => old('_nama'), 'agen' => old('_agen')] : ['jenis' => null, 'id' => null, 'nama' => '', 'agen' => null];
    $tabs = [['Semua', request()->fullUrlWithQuery(['tahap' => null, 'page' => null]), ! request('tahap'), $perTahap->sum()]];
    foreach (\App\Models\Lead::TAHAP as $k => $l) {
        $tabs[] = [$l, request()->fullUrlWithQuery(['tahap' => $k, 'page' => null]), request('tahap') === $k, $perTahap[$k] ?? 0];
    }
    $dataLead = fn ($l) => ['id' => $l->id, 'nama' => $l->nama, 'no_hp' => $l->no_hp, 'domisili' => $l->domisili, 'sumber' => $l->sumber, 'tanggal_lead' => $l->tanggal_lead->toDateString(), 'agen_id' => (string) $l->agen_id, 'kavling_minat_id' => (string) $l->kavling_minat_id, 'catatan' => $l->catatan];
@endphp

@section('content')
<div x-data="{
        f: @js($awal), kosong: @js($kosong), aksi: @js($aksiAwal), transaksi: @js($transaksiBebas),
        get action() { return this.f.id ? '{{ url('lead-harian') }}/' + this.f.id : '{{ route('lead.store') }}' },
        get urlAksi() { return '{{ url('lead-harian') }}/' + this.aksi.id + '/' + this.aksi.jenis },
        get transaksiCocok() { return this.transaksi.filter(t => !t.agen_id || t.agen_id == this.aksi.agen) },
        tambah() { this.f = { ...this.kosong }; this.$dispatch('open-modal', 'lead') },
        ubah(l) { this.f = { ...l }; this.$dispatch('open-modal', 'lead') },
        maju(jenis, l) { this.aksi = { jenis, id: l.id, nama: l.nama, agen: l.agen_id }; this.$dispatch('open-modal', 'aksi') },
     }">

@include('agen._navigasi')
<x-page-header title="Lead Harian" subtitle="Lead → Prospek → Closing" :breadcrumbs="auth()->user()->isAdmin() ? ['Agen & Marketing' => route('agen.index'), 'Lead Harian' => null] : ['Marketing' => null, 'Lead Harian' => null]">
    <x-slot:actions>
        @unless (auth()->user()->isAdmin())<x-button variant="secondary" icon="chart" :href="route('lead.rekap')">Rekap</x-button>@endunless
        <x-button icon="plus" x-on:click="tambah()" :disabled="$agens->isEmpty()">Input Lead</x-button>
    </x-slot:actions>
</x-page-header>

@if ($agens->isEmpty())
    <div class="mb-5 flex items-center gap-3 rounded-kartu border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <x-icon name="warning" class="size-5 text-amber-500"/> Belum ada agen aktif. <a href="{{ route('agen.index') }}" class="font-medium underline">Tambah agen</a>
    </div>
@endif

<div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
    @foreach (['Hari Ini' => $hariIni, 'Bulan ' . tanggal(now(), 'F') => $bulanIni] as $judul => $a)
        <div @class(['rounded-kartu border p-4 shadow-kartu sm:p-5', 'border-brand-700 bg-brand-700 text-white' => $loop->first, 'border-slate-200 bg-white' => ! $loop->first])>
            <p @class(['text-xs font-medium', 'text-brand-100' => $loop->first, 'text-slate-500' => ! $loop->first])>{{ $judul }}</p>
            <div class="mt-2 grid grid-cols-3 gap-2">
                @foreach (['lead' => 'Lead', 'prospek' => 'Prospek', 'closing' => 'Closing'] as $k => $l)
                    <div>
                        <p class="text-2xl font-semibold tabular-nums">{{ $a[$k] }}</p>
                        <p @class(['text-xs', 'text-brand-100' => $loop->parent->first, 'text-slate-500' => ! $loop->parent->first])>{{ $l }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<x-tabs :items="$tabs"/>

<div class="card">
    <x-filter-bar placeholder="Cari nama, HP, kode…" :abaikan="['tahap']">
        @if (request('tahap'))<input type="hidden" name="tahap" value="{{ request('tahap') }}">@endif
        <x-select name="agen" :options="$agens" :value="request('agen')" placeholder="Semua agen"/>
        <x-select name="sumber" :options="\App\Models\Lead::SUMBER" :value="request('sumber')" placeholder="Semua sumber"/>
        <x-input type="date" name="tanggal" :value="request('tanggal')" aria-label="Tanggal aktivitas"/>
    </x-filter-bar>

    @if ($leads->isEmpty())
        <x-empty-state title="Belum ada lead" :message="request()->query() ? 'Ubah atau hapus filter.' : null">
            @if ($agens->isNotEmpty())<x-button icon="plus" x-on:click="tambah()">Input Lead</x-button>@endif
        </x-empty-state>
    @else
        <div class="table-wrap max-md:hidden">
            <table class="table">
                <thead><tr><th>Calon Konsumen</th><th>Sumber</th><th>Agen</th><th>Perjalanan</th><th>Tahap</th><th class="w-px"></th></tr></thead>
                <tbody>
                    @foreach ($leads as $l)
                        <tr>
                            <td>
                                <span class="font-medium text-slate-900">{{ $l->nama }}</span>
                                <div class="text-xs text-slate-500">{{ $l->kode }}{{ $l->no_hp ? ' · ' . $l->no_hp : '' }}{{ $l->kavlingMinat ? ' · minat ' . $l->kavlingMinat->kode_kavling : '' }}</div>
                            </td>
                            <td>{{ $l->label_sumber }}</td>
                            <td>{{ $l->agen->nama_agen }}<div class="text-xs text-slate-500">Input: {{ $l->penginput->name ?? '—' }}</div></td>
                            <td class="text-xs leading-5 text-slate-600">
                                <div>Lead {{ tanggal($l->tanggal_lead) }}</div>
                                @if ($l->tanggal_prospek)<div>Prospek {{ tanggal($l->tanggal_prospek) }}</div>@endif
                                @if ($l->tanggal_closing)<div>Closing {{ tanggal($l->tanggal_closing) }} · <a href="{{ route('transaksi-penjualan.show', $l->transaksi_id) }}" class="tautan">{{ $l->transaksi?->kavling?->kode_kavling }}</a></div>@endif
                            </td>
                            <td><x-badge :status="$l->tahap"/></td>
                            <td>@include('lead._aksi', ['l' => $l, 'data' => $dataLead($l)])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <ul class="divide-y divide-slate-100 md:hidden">
            @foreach ($leads as $l)
                <li class="px-4 py-3.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="truncate font-medium text-slate-900">{{ $l->nama }}</span>
                                <x-badge :status="$l->tahap"/>
                            </div>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $l->label_sumber }} · {{ $l->agen->nama_agen }} · {{ tanggal($l->tanggal_lead) }}</p>
                            @if ($l->no_hp)<a href="tel:{{ $l->no_hp }}" class="mt-0.5 inline-flex items-center gap-1 text-xs text-brand-700"><x-icon name="phone" class="size-3.5"/>{{ $l->no_hp }}</a>@endif
                        </div>
                    </div>
                    <div class="mt-2">@include('lead._aksi', ['l' => $l, 'data' => $dataLead($l), 'hp' => true])</div>
                </li>
            @endforeach
        </ul>
        {{ $leads->links() }}
    @endif
</div>

{{-- Popup input / ubah lead --}}
<x-modal name="lead" judul-js="f.id ? 'Ubah Lead' : 'Input Lead'" max-width="xl" :show="old('_form') === 'lead' && $errors->any()">
    <form method="POST" :action="action" class="flex min-h-0 flex-1 flex-col">
        @csrf
        <template x-if="f.id"><input type="hidden" name="_method" value="PUT"></template>
        <input type="hidden" name="_form" value="lead"><input type="hidden" name="_id" :value="f.id">
        <x-modal-body>
            <x-field label="Nama Calon Konsumen" name="nama" required><x-input name="nama" x-model="f.nama" autocomplete="off"/></x-field>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="No. HP / WA" name="no_hp"><x-input name="no_hp" type="tel" inputmode="tel" x-model="f.no_hp"/></x-field>
                <x-field label="Domisili" name="domisili"><x-input name="domisili" x-model="f.domisili"/></x-field>
                <x-field label="Sumber" name="sumber" required><x-select name="sumber" :options="\App\Models\Lead::SUMBER" x-model="f.sumber"/></x-field>
                <x-field label="Tanggal Lead" name="tanggal_lead" required><x-input type="date" name="tanggal_lead" x-model="f.tanggal_lead" :max="now()->toDateString()"/></x-field>
                <x-field label="Agen" name="agen_id" required><x-select name="agen_id" :options="$agens" x-model="f.agen_id"/></x-field>
                <x-field label="Minat Kavling" name="kavling_minat_id"><x-select name="kavling_minat_id" :options="$kavlings" placeholder="Belum tahu" x-model="f.kavling_minat_id"/></x-field>
            </div>
            <x-field label="Catatan" name="catatan"><x-textarea name="catatan" rows="2" x-model="f.catatan"/></x-field>
        </x-modal-body>
        <x-modal-footer><button type="submit" class="btn btn-primary" x-text="f.id ? 'Simpan' : 'Simpan Lead'"></button></x-modal-footer>
    </form>
</x-modal>

{{-- Popup naik tahap --}}
<x-modal name="aksi" judul-js="(aksi.jenis === 'closing' ? 'Closing · ' : 'Jadikan Prospek · ') + aksi.nama" :show="in_array(old('_form'), ['prospek', 'closing']) && $errors->any()">
    <form method="POST" :action="urlAksi" class="flex min-h-0 flex-1 flex-col">
        @csrf
        <input type="hidden" name="_form" :value="aksi.jenis"><input type="hidden" name="_id" :value="aksi.id">
        <input type="hidden" name="_nama" :value="aksi.nama"><input type="hidden" name="_agen" :value="aksi.agen">
        <x-modal-body>
            <x-field label="Tanggal" name="tanggal" required><x-input type="date" name="tanggal" :value="now()->toDateString()" :max="now()->toDateString()"/></x-field>
            <template x-if="aksi.jenis === 'closing'">
                <x-field label="Transaksi Penjualan" name="transaksi_id" required hint="Transaksi aktif agen ini yang belum terhubung ke lead lain.">
                    <x-select name="transaksi_id" data-opsi-expr="transaksiCocok" placeholder="Pilih transaksi"/>
                    <p class="form-hint" x-show="!transaksiCocok.length">Belum ada yang cocok. <a href="{{ route('transaksi-penjualan.create') }}" class="tautan">Buat transaksi</a></p>
                </x-field>
            </template>
            <x-field label="Catatan" name="catatan"><x-input name="catatan" id="catatan_aksi"/></x-field>
        </x-modal-body>
        <x-modal-footer><button type="submit" class="btn btn-primary" x-text="aksi.jenis === 'closing' ? 'Simpan Closing' : 'Jadikan Prospek'"></button></x-modal-footer>
    </form>
</x-modal>
</div>
@endsection
