@extends('layouts.app')
@section('title', 'Kas Proyek')

@php
    $kosong = ['id' => null, 'tanggal' => now()->toDateString(), 'jenis' => 'keluar', 'kategori' => '', 'rab_id' => '', 'pos' => '', 'uraian' => '', 'nominal' => '', 'sumber' => '', 'catatan' => ''];
    $awal = old('_form') === 'kas' ? array_merge($kosong, request()->old(), ['id' => old('_id')]) : $kosong;
    $posRab = $rabs->pluck('pos', 'id');
@endphp

@section('content')
<div x-data="{
        f: @js($awal), kosong: @js($kosong), posRab: @js($posRab),
        get action() { return this.f.id ? '{{ url('kas-proyek') }}/' + this.f.id : '{{ route('kas-proyek.store') }}' },
        buka(data) { this.f = { ...data }; this.$dispatch('open-modal', 'kas'); this.$nextTick(() => this.$dispatch('set-money', { name: 'nominal', value: this.f.nominal })) },
        pilihRab() { if (this.f.rab_id && this.posRab[this.f.rab_id]) this.f.pos = this.posRab[this.f.rab_id] },
     }">

<x-page-header title="Kas Proyek" subtitle="Buku kas proyek. Pembayaran konsumen, refund, dan komisi tercatat otomatis."
               :breadcrumbs="['Keuangan' => null, 'Kas Proyek' => null]">
    <x-slot:actions>
        <button type="button" class="btn btn-secondary" x-on:click="buka({ ...kosong, jenis: 'masuk' })"><x-icon name="plus" class="h-4 w-4"/> Kas Masuk</button>
        <button type="button" class="btn btn-primary" x-on:click="buka({ ...kosong })"><x-icon name="plus" class="h-4 w-4"/> Pengeluaran</button>
    </x-slot:actions>
</x-page-header>

<div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
    <x-stat-card label="Total Masuk" :value="rupiah($totalMasuk)" icon="arrow-right"/>
    <x-stat-card label="Total Keluar" :value="rupiah($totalKeluar)" icon="arrow-left"/>
    <x-stat-card label="Saldo Kas Proyek" :value="rupiah($saldo)" :tone="$saldo < 0 ? 'danger' : 'dark'" icon="wallet"/>
</div>

<div class="mb-4 flex gap-2 overflow-x-auto pb-1">
    @foreach (['' => 'Semua', 'masuk' => 'Masuk', 'keluar' => 'Keluar'] as $j => $l)
        <a href="{{ request()->fullUrlWithQuery(['jenis' => $j ?: null, 'page' => null]) }}" @class(['btn btn-sm', 'btn-primary' => request('jenis', '') === $j, 'btn-secondary' => request('jenis', '') !== $j])>{{ $l }}</a>
    @endforeach
</div>

<div class="card">
    <x-filter-bar placeholder="Cari kode, uraian, sumber…">
        @if (request('jenis'))<input type="hidden" name="jenis" value="{{ request('jenis') }}">@endif
        <x-select name="asal" :options="\App\Models\KasTransaksi::ASAL" :value="request('asal')" placeholder="Semua asal" class="sm:w-48"/>
        <x-select name="pos" :options="\App\Models\AlokasiKas::POS" :value="request('pos')" placeholder="Semua pos" class="sm:w-48"/>
        <x-select name="kategori" :options="$kategoriList->mapWithKeys(fn ($k) => [$k => $k])" :value="request('kategori')" placeholder="Semua kategori" class="sm:w-44"/>
        <input type="month" name="bulan" value="{{ request('bulan') }}" class="form-input sm:w-44" title="Bulan">
    </x-filter-bar>

    @if ($baris->isEmpty())
        <x-empty-state title="Belum ada transaksi kas" message="Tidak ada catatan kas yang cocok."/>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Tanggal / Kode</th><th>Uraian</th><th>Kategori / Pos</th><th class="text-right">Masuk</th><th class="text-right">Keluar</th><th class="text-right">Saldo</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @foreach ($baris as $k)
                        <tr>
                            <td>{{ tanggal($k->tanggal) }}<div class="text-xs text-slate-500">{{ $k->kode }}</div></td>
                            <td class="wrap min-w-[240px]">
                                <span class="text-slate-900">{{ $k->uraian }}</span>
                                <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs text-slate-500">
                                    @if ($k->isOtomatis())
                                        <x-badge status="menunggu" :label="\App\Models\KasTransaksi::ASAL[$k->asal]" warna="emas"/>
                                    @endif
                                    @if ($k->sumber)<span>{{ $k->sumber }}</span>@endif
                                    @if ($k->rab)<span>· RAB: {{ $k->rab->uraian }}</span>@endif
                                </div>
                            </td>
                            <td>{{ $k->kategori }}@if ($k->pos)<div class="text-xs text-slate-500">{{ \App\Models\AlokasiKas::POS[$k->pos] }}</div>@endif</td>
                            <td class="text-right tabular-nums text-forest-600">{{ $k->jenis === 'masuk' ? rupiah($k->nominal) : '' }}</td>
                            <td class="text-right tabular-nums text-red-600">{{ $k->jenis === 'keluar' ? rupiah($k->nominal) : '' }}</td>
                            <td @class(['text-right font-medium tabular-nums', 'text-red-600' => $k->saldo_berjalan < 0])>{{ rupiah($k->saldo_berjalan) }}</td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    @if ($k->isOtomatis())
                                        @if ($link = $k->linkSumber())<a href="{{ $link }}" class="btn-icon" title="Buka sumber"><x-icon name="arrow-right" class="h-[18px] w-[18px]"/></a>@endif
                                    @else
                                        <button type="button" class="btn-icon" title="Ubah"
                                                x-on:click="buka(@js(['id' => $k->id, 'tanggal' => $k->tanggal->toDateString(), 'jenis' => $k->jenis, 'kategori' => $k->kategori, 'rab_id' => (string) $k->rab_id, 'pos' => (string) $k->pos, 'uraian' => $k->uraian, 'nominal' => (float) $k->nominal, 'sumber' => $k->sumber, 'catatan' => $k->catatan]))"><x-icon name="pencil" class="h-[18px] w-[18px]"/></button>
                                        <x-delete-button :action="route('kas-proyek.destroy', $k)" title="Hapus {{ $k->kode }}?"/>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                @if (request()->hasAny(['jenis', 'asal', 'pos', 'kategori', 'bulan', 'cari']))
                    <tfoot><tr><td colspan="3">Total hasil filter</td><td class="text-right text-forest-600">{{ rupiah($filterMasuk) }}</td><td class="text-right text-red-600">{{ rupiah($filterKeluar) }}</td><td colspan="2"></td></tr></tfoot>
                @endif
            </table>
        </div>
        @if ($baris->hasPages())<div class="border-t border-slate-100 px-4 py-3">{{ $baris->links() }}</div>@endif
    @endif
</div>

<x-modal name="kas" max-width="lg" :show="old('_form') === 'kas' && $errors->any()">
    <form method="POST" :action="action">
        @csrf
        <template x-if="f.id"><input type="hidden" name="_method" value="PUT"></template>
        <input type="hidden" name="_form" value="kas"><input type="hidden" name="_id" :value="f.id">
        <input type="hidden" name="jenis" :value="f.jenis">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-semibold text-slate-900" x-text="(f.id ? 'Ubah ' : 'Catat ') + (f.jenis === 'masuk' ? 'Kas Masuk' : 'Pengeluaran')"></h3>
            <button type="button" class="btn-icon -mr-2" x-on:click="$dispatch('close-modal', 'kas')"><x-icon name="x"/></button>
        </div>
        <div class="space-y-4 p-5">
            <p x-show="f.jenis === 'masuk'" class="rounded-lg bg-sky-50 px-3 py-2 text-xs text-sky-800">Kas masuk manual (mis. modal/pinjaman) tidak ikut dialokasikan. Pembayaran konsumen dicatat dari halaman transaksi.</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Tanggal" name="tanggal" required>
                    <input type="date" name="tanggal" id="tanggal" x-model="f.tanggal" max="{{ now()->toDateString() }}" class="form-input">
                </x-field>
                <x-field label="Nominal" name="nominal" required><x-money name="nominal"/></x-field>
            </div>
            <template x-if="f.jenis === 'keluar'">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field label="Item RAB" name="rab_id" hint="Opsional. Mengisi realisasi RAB.">
                        <select name="rab_id" id="rab_id" x-model="f.rab_id" x-on:change="pilihRab()" class="form-input">
                            <option value="">— Bukan item RAB —</option>
                            @foreach ($rabs as $r)<option value="{{ $r->id }}">{{ $r->kategori }} · {{ $r->uraian }}</option>@endforeach
                        </select>
                    </x-field>
                    <x-field label="Pos Alokasi" name="pos" required>
                        <select name="pos" id="pos" x-model="f.pos" :disabled="!!f.rab_id" class="form-input">
                            <option value="">— Pilih pos —</option>
                            @foreach (\App\Models\AlokasiKas::POS as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                        </select>
                        <input type="hidden" name="pos" :value="f.pos" x-bind:disabled="!f.rab_id">
                    </x-field>
                </div>
            </template>
            <x-field label="Kategori" name="kategori" required>
                <input type="text" name="kategori" id="kategori" x-model="f.kategori" list="daftar-kategori" class="form-input" placeholder="Mis. Legalitas, Operasional, Modal">
                <datalist id="daftar-kategori">@foreach ($kategoriList->merge($rabs->pluck('kategori'))->unique() as $k)<option value="{{ $k }}">@endforeach</datalist>
            </x-field>
            <x-field label="Uraian" name="uraian" required>
                <input type="text" name="uraian" id="uraian" x-model="f.uraian" class="form-input">
            </x-field>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Sumber / Penerima" name="sumber"><input type="text" name="sumber" id="sumber" x-model="f.sumber" class="form-input"></x-field>
                <x-field label="Catatan" name="catatan"><input type="text" name="catatan" id="catatan" x-model="f.catatan" class="form-input"></x-field>
            </div>
        </div>
        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'kas')">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</x-modal>
</div>
@endsection
