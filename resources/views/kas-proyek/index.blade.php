@extends('layouts.app')
@section('title', 'Kas Proyek')

@php
    $kosong = ['id' => null, 'tanggal' => now()->toDateString(), 'jenis' => 'keluar', 'kategori' => '', 'rab_id' => '', 'pos' => '', 'uraian' => '', 'nominal' => '', 'sumber' => '', 'catatan' => ''];
    $awal = old('_form') === 'kas' ? array_merge($kosong, request()->old(), ['id' => old('_id')]) : $kosong;
    $posRab = $rabs->pluck('pos', 'id');
    $tabs = collect(['' => 'Semua', 'masuk' => 'Masuk', 'keluar' => 'Keluar'])
        ->map(fn ($l, $j) => [$l, request()->fullUrlWithQuery(['jenis' => $j ?: null, 'page' => null]), request('jenis', '') === $j, null])->values()->all();
    $dataKas = fn ($k) => ['id' => $k->id, 'tanggal' => $k->tanggal->toDateString(), 'jenis' => $k->jenis, 'kategori' => $k->kategori, 'rab_id' => (string) $k->rab_id, 'pos' => (string) $k->pos, 'uraian' => $k->uraian, 'nominal' => (float) $k->nominal, 'sumber' => $k->sumber, 'catatan' => $k->catatan];
@endphp

@section('content')
<div x-data="{
        f: @js($awal), kosong: @js($kosong), posRab: @js($posRab),
        get action() { return this.f.id ? '{{ url('kas-proyek') }}/' + this.f.id : '{{ route('kas-proyek.store') }}' },
        buka(data) { this.f = { ...data }; this.$dispatch('open-modal', 'kas'); this.$nextTick(() => this.$dispatch('set-money', { name: 'nominal', value: this.f.nominal })) },
        pilihRab() { if (this.f.rab_id && this.posRab[this.f.rab_id]) this.f.pos = this.posRab[this.f.rab_id] },
     }">

<x-page-header title="Kas Proyek" subtitle="Pembayaran, refund, dan komisi tercatat otomatis." :breadcrumbs="['Keuangan' => null, 'Kas Proyek' => null]">
    <x-slot:actions>
        <x-button variant="secondary" icon="plus" x-on:click="buka({ ...kosong, jenis: 'masuk' })">Kas Masuk</x-button>
        <x-button icon="plus" x-on:click="buka({ ...kosong })">Pengeluaran</x-button>
    </x-slot:actions>
</x-page-header>

<div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
    <x-stat-card label="Total Masuk" :value="rupiah($totalMasuk)" icon="arrow-right"/>
    <x-stat-card label="Total Keluar" :value="rupiah($totalKeluar)" icon="arrow-left"/>
    <x-stat-card label="Saldo Kas" :value="rupiah($saldo)" :tone="$saldo < 0 ? 'bahaya' : 'utama'" icon="wallet"/>
</div>

<x-tabs :items="$tabs"/>

<div class="card">
    <x-filter-bar placeholder="Cari kode, uraian, sumber…" :abaikan="['jenis']">
        @if (request('jenis'))<input type="hidden" name="jenis" value="{{ request('jenis') }}">@endif
        <x-select name="asal" :options="\App\Models\KasTransaksi::ASAL" :value="request('asal')" placeholder="Semua asal"/>
        <x-select name="pos" :options="\App\Models\AlokasiKas::POS" :value="request('pos')" placeholder="Semua pos"/>
        <x-select name="kategori" :options="$kategoriList->mapWithKeys(fn ($k) => [$k => $k])" :value="request('kategori')" placeholder="Semua kategori"/>
        <x-input type="month" name="bulan" :value="request('bulan')" aria-label="Bulan"/>
    </x-filter-bar>

    @if ($baris->isEmpty())
        <x-empty-state title="Belum ada catatan kas" :message="request()->query() ? 'Ubah atau hapus filter.' : null"/>
    @else
        <div class="table-wrap max-md:hidden">
            <table class="table">
                <thead><tr><th>Tanggal</th><th>Uraian</th><th>Kategori</th><th class="text-right">Masuk</th><th class="text-right">Keluar</th><th class="text-right">Saldo</th><th class="w-px"></th></tr></thead>
                <tbody>
                    @foreach ($baris as $k)
                        <tr>
                            <td>{{ tanggal($k->tanggal) }}<div class="text-xs text-slate-500">{{ $k->kode }}</div></td>
                            <td class="wrap min-w-[260px]">
                                <span class="text-slate-900">{{ $k->uraian }}</span>
                                <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs text-slate-500">
                                    @if ($k->isOtomatis())<x-badge status="otomatis" :label="\App\Models\KasTransaksi::ASAL[$k->asal]"/>@endif
                                    @if ($k->sumber)<span>{{ $k->sumber }}</span>@endif
                                    @if ($k->rab)<span>· RAB: {{ $k->rab->uraian }}</span>@endif
                                </div>
                            </td>
                            <td>{{ $k->kategori }}@if ($k->pos)<div class="text-xs text-slate-500">{{ \App\Models\AlokasiKas::POS[$k->pos] }}</div>@endif</td>
                            <td class="text-right text-green-700 tabular-nums">{{ $k->jenis === 'masuk' ? rupiah($k->nominal) : '' }}</td>
                            <td class="text-right text-red-600 tabular-nums">{{ $k->jenis === 'keluar' ? rupiah($k->nominal) : '' }}</td>
                            <td @class(['text-right font-medium tabular-nums', 'text-red-600' => $k->saldo_berjalan < 0, 'text-slate-900' => $k->saldo_berjalan >= 0])>{{ rupiah($k->saldo_berjalan) }}</td>
                            <td>@include('kas-proyek._aksi')</td>
                        </tr>
                    @endforeach
                </tbody>
                @if (request()->hasAny(['jenis', 'asal', 'pos', 'kategori', 'bulan', 'cari']))
                    <tfoot><tr><td colspan="3">Total hasil filter</td><td class="text-right text-green-700 tabular-nums">{{ rupiah($filterMasuk) }}</td><td class="text-right text-red-600 tabular-nums">{{ rupiah($filterKeluar) }}</td><td colspan="2"></td></tr></tfoot>
                @endif
            </table>
        </div>

        <ul class="divide-y divide-slate-100 md:hidden">
            @foreach ($baris as $k)
                <li class="flex items-start gap-3 px-4 py-3.5">
                    <span @class(['mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full', 'bg-green-50 text-green-700' => $k->jenis === 'masuk', 'bg-red-50 text-red-600' => $k->jenis === 'keluar'])>
                        <x-icon :name="$k->jenis === 'masuk' ? 'arrow-right' : 'arrow-left'" class="size-4"/>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm text-slate-900">{{ $k->uraian }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ tanggal($k->tanggal) }} · {{ $k->kategori }}@if ($k->isOtomatis()) · otomatis @endif</p>
                        <p @class(['mt-1 text-sm font-semibold tabular-nums', 'text-green-700' => $k->jenis === 'masuk', 'text-red-600' => $k->jenis === 'keluar'])>{{ $k->jenis === 'masuk' ? '+' : '−' }}{{ rupiah($k->nominal) }}</p>
                    </div>
                    @include('kas-proyek._aksi')
                </li>
            @endforeach
        </ul>
        {{ $baris->links() }}
    @endif
</div>

<x-modal name="kas" judul-js="(f.id ? 'Ubah ' : 'Catat ') + (f.jenis === 'masuk' ? 'Kas Masuk' : 'Pengeluaran')" :show="old('_form') === 'kas' && $errors->any()">
    <form method="POST" :action="action" class="flex min-h-0 flex-1 flex-col">
        @csrf
        <template x-if="f.id"><input type="hidden" name="_method" value="PUT"></template>
        <input type="hidden" name="_form" value="kas"><input type="hidden" name="_id" :value="f.id">
        <input type="hidden" name="jenis" :value="f.jenis">
        <x-modal-body>
            <p x-show="f.jenis === 'masuk'" class="flex gap-2 rounded-kontrol bg-sky-50 px-3 py-2 text-xs text-sky-800"><x-icon name="info" class="size-4"/>Kas masuk manual (mis. modal) tidak dialokasikan. Pembayaran konsumen dicatat di Transaksi.</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Tanggal" name="tanggal" required><x-input type="date" name="tanggal" x-model="f.tanggal" :max="now()->toDateString()"/></x-field>
                <x-field label="Nominal" name="nominal" required><x-money name="nominal"/></x-field>
            </div>
            <template x-if="f.jenis === 'keluar'">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field label="Item RAB" name="rab_id" hint="Opsional. Mengisi realisasi RAB.">
                        <x-select name="rab_id" :options="$rabs->mapWithKeys(fn ($r) => [$r->id => $r->kategori . ' · ' . $r->uraian])" placeholder="Bukan item RAB" x-model="f.rab_id" x-on:change="pilihRab()"/>
                    </x-field>
                    <x-field label="Pos Alokasi" name="pos" required>
                        <x-select name="pos" :options="\App\Models\AlokasiKas::POS" placeholder="Pilih pos" x-model="f.pos" x-bind:disabled="!!f.rab_id"/>
                        {{-- Saat item RAB dipilih, pos mengikuti RAB dan tetap terkirim --}}
                        <input type="hidden" name="pos" :value="f.pos" x-bind:disabled="!f.rab_id">
                    </x-field>
                </div>
            </template>
            <x-field label="Kategori" name="kategori" required>
                <x-input name="kategori" x-model="f.kategori" list="daftar-kategori" placeholder="Mis. Legalitas, Operasional, Modal"/>
                <datalist id="daftar-kategori">@foreach ($kategoriList->merge($rabs->pluck('kategori'))->unique() as $kat)<option value="{{ $kat }}">@endforeach</datalist>
            </x-field>
            <x-field label="Uraian" name="uraian" required><x-input name="uraian" x-model="f.uraian"/></x-field>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Sumber / Penerima" name="sumber"><x-input name="sumber" x-model="f.sumber"/></x-field>
                <x-field label="Catatan" name="catatan"><x-input name="catatan" x-model="f.catatan"/></x-field>
            </div>
        </x-modal-body>
        <x-modal-footer><x-button type="submit">Simpan</x-button></x-modal-footer>
    </form>
</x-modal>
</div>
@endsection
