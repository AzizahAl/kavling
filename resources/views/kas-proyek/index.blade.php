@extends('layouts.app')
@section('title', 'Kas Proyek')

@php
    $kosong = ['id' => null, 'tanggal' => now()->toDateString(), 'jenis' => 'keluar', 'kategori' => '', 'uraian' => '', 'nominal' => '', 'sumber' => '', 'catatan' => ''];
    $awal = old('_form') === 'kas'
        ? ['id' => old('_id'), 'tanggal' => old('tanggal'), 'jenis' => old('jenis', 'keluar'), 'kategori' => old('kategori'), 'uraian' => old('uraian'), 'nominal' => old('nominal'), 'sumber' => old('sumber'), 'catatan' => old('catatan')]
        : $kosong;
    $dataKas = fn ($k) => ['id' => $k->id, 'tanggal' => $k->tanggal->toDateString(), 'jenis' => $k->jenis, 'kategori' => $k->kategori, 'uraian' => $k->uraian, 'nominal' => (float) $k->nominal, 'sumber' => $k->sumber, 'catatan' => $k->catatan];
    $opsiKategori = collect(['masuk', 'keluar'])->mapWithKeys(fn ($j) => [$j => collect(\App\Models\KasTransaksi::kategoriManual($j))->map(fn ($k) => ['v' => $k, 'l' => $k])->all()])->all();
    $tabs = collect(['' => 'Semua', 'masuk' => 'Masuk', 'keluar' => 'Keluar']);
    $jumlahFilter = collect(request()->only(['kategori', 'dari', 'sampai']))->filter(fn ($v) => filled($v))->count();
    $adaFilter = $jumlahFilter > 0 || filled(request('cari')) || filled(request('jenis'));
    $strip = '<span class="text-slate-300">—</span>';
@endphp

@section('content')
<div x-data="{
        f: @js($awal), kosong: @js($kosong), opsi: @js($opsiKategori),
        get action() { return this.f.id ? '{{ url('kas-proyek') }}/' + this.f.id : '{{ route('kas-proyek.store') }}' },
        get opsiKategori() { return this.opsi[this.f.jenis] ?? [] },
        buka(data) { this.f = { ...data }; this.$dispatch('open-modal', 'kas'); this.$nextTick(() => this.$dispatch('set-money', { name: 'nominal', value: this.f.nominal })) },
        gantiJenis(j) { if (this.f.id) return; this.f.jenis = j; if (! this.opsiKategori.some(o => o.v === this.f.kategori)) this.f.kategori = '' },
     }">

<x-page-header title="Kas Proyek" subtitle="Buku kas proyek. Pembayaran konsumen, refund, komisi, dan realisasi RAB tercatat otomatis." :breadcrumbs="['Keuangan' => null, 'Kas Proyek' => null]">
    <x-slot:actions>
        <x-button icon="plus" x-on:click="buka({ ...kosong })">Tambah Transaksi</x-button>
    </x-slot:actions>
</x-page-header>

{{-- Kartu ringkasan: seluruh data; saldo = saldo berjalan baris terakhir --}}
<div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
    @foreach ([
        ['Total Masuk', $totalMasuk, 'arrow-right', 'border-l-green-600', 'bg-green-50 text-green-700', 'text-green-700'],
        ['Total Keluar', $totalKeluar, 'arrow-left', 'border-l-red-500', 'bg-red-50 text-red-600', 'text-red-600'],
        ['Saldo Proyek', $saldo, 'wallet', $saldo < 0 ? 'border-l-red-500' : 'border-l-brand-600', $saldo < 0 ? 'bg-red-50 text-red-600' : 'bg-brand-50 text-brand-700', $saldo < 0 ? 'text-red-600' : 'text-slate-900'],
    ] as [$label, $nilai, $ikon, $aksen, $kelasIkon, $kelasNilai])
        <div class="flex items-center gap-3 rounded-kartu border border-l-4 border-slate-200 {{ $aksen }} bg-white px-4 py-3.5 shadow-kartu">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-lg {{ $kelasIkon }}"><x-icon :name="$ikon" class="size-5"/></span>
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500">{{ $label }}</p>
                <p class="truncate text-lg font-semibold tabular-nums {{ $kelasNilai }}">{{ rupiah($nilai) }}</p>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    {{-- Panel: tab jenis di kiri; cari & filter di kanan. Semua bisa dipakai bersamaan. --}}
    <form method="GET" action="{{ route('kas-proyek.index') }}" role="search" x-data="{ filter: false }"
          x-on:change="if ($event.target.type !== 'search') $el.requestSubmit()"
          class="flex flex-col gap-2.5 border-b border-slate-100 p-3 sm:p-4 lg:flex-row lg:items-center">
        <input type="hidden" name="jenis" value="{{ request('jenis') }}">
        <nav class="inline-flex w-fit gap-1 rounded-kontrol bg-slate-200/60 p-1" aria-label="Jenis kas">
            @foreach ($tabs as $j => $l)
                @php $aktif = (string) request('jenis', '') === (string) $j; @endphp
                <a href="{{ request()->fullUrlWithQuery(['jenis' => $j ?: null, 'page' => null]) }}" @if ($aktif) aria-current="page" @endif
                   @class(['inline-flex h-8 items-center rounded-lg px-3 text-[13px] font-medium transition-colors',
                           'bg-white text-slate-900 shadow-kartu' => $aktif, 'text-slate-600 hover:bg-white/60 hover:text-slate-900' => ! $aktif])>{{ $l }}</a>
            @endforeach
        </nav>
        <div class="flex min-w-0 flex-1 items-center gap-2 lg:justify-end">
            <div class="relative min-w-0 flex-1 lg:max-w-xs">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"/>
                <input type="search" name="cari" value="{{ request('cari') }}" placeholder="Cari kode, uraian, sumber…" class="form-input pl-9" enterkeyhint="search" aria-label="Cari">
            </div>
            <div class="relative" x-on:click.outside="filter = false" x-on:keydown.escape="filter = false">
                <button type="button" class="btn btn-secondary px-3" x-on:click="filter = ! filter" :aria-expanded="filter">
                    <x-icon name="sliders" class="size-4"/> Filter
                    @if ($jumlahFilter)<span class="flex size-5 items-center justify-center rounded-full bg-brand-600 text-[11px] font-semibold text-white">{{ $jumlahFilter }}</span>@endif
                </button>
                <div x-show="filter" x-cloak x-transition.opacity.duration.150ms
                     class="absolute right-0 z-20 mt-2 w-72 space-y-3 rounded-kartu border border-slate-200 bg-white p-4 shadow-menu">
                    <x-field label="Kategori" name="kategori">
                        <x-select name="kategori" :options="$kategoriList->mapWithKeys(fn ($k) => [$k => $k])" :value="request('kategori')" placeholder="Semua kategori"/>
                    </x-field>
                    <div class="grid grid-cols-2 gap-2">
                        <x-field label="Dari" name="dari"><x-input type="date" name="dari" :value="request('dari')"/></x-field>
                        <x-field label="Sampai" name="sampai"><x-input type="date" name="sampai" :value="request('sampai')"/></x-field>
                    </div>
                    @if ($jumlahFilter)
                        <a href="{{ request()->fullUrlWithQuery(['kategori' => null, 'dari' => null, 'sampai' => null, 'page' => null]) }}" class="btn btn-ghost btn-sm w-full justify-center text-slate-500"><x-icon name="filter-x" class="size-4"/> Hapus filter</a>
                    @endif
                </div>
            </div>
            <button type="submit" class="sr-only">Cari</button>
        </div>
    </form>

    @if ($baris->isEmpty())
        <x-empty-state :title="$adaFilter ? 'Tidak ada catatan yang cocok' : 'Belum ada catatan kas'" :message="$adaFilter ? 'Ubah tab, pencarian, atau filter.' : null">
            @unless ($adaFilter)<x-button icon="plus" x-on:click="buka({ ...kosong })">Tambah Transaksi</x-button>@endunless
        </x-empty-state>
    @else
        {{-- Muat di laptop; di HP tabel digulir di dalam wadahnya --}}
        <div class="table-wrap">
            <table class="table min-w-[66rem] [&_td]:px-2.5 [&_th]:px-2.5">
                <thead>
                    <tr>
                        <th>Tanggal</th><th>Kode</th><th>Kategori</th><th>Uraian</th>
                        <th class="text-right">Masuk</th><th class="text-right">Keluar</th><th class="text-right">Saldo</th>
                        <th>Sumber/Transaksi</th><th>Catatan</th><th class="w-px text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($baris as $k)
                        @php $sumber = $k->sumberTampil(); @endphp
                        <tr>
                            <td class="text-slate-600">{{ tanggal($k->tanggal) }}</td>
                            <td class="font-medium text-slate-900 tabular-nums">{{ $k->kode }}</td>
                            <td><x-badge :status="'kat-' . $k->kategori" :label="$k->kategori" :warna="$k->jenis === 'masuk' ? 'hijau' : 'abu'" :ikon="$k->ikonKategori()"/></td>
                            <td class="max-w-56">
                                <span class="block truncate text-slate-900" x-data x-tip="@js($k->uraian)" tabindex="0">{{ $k->uraian }}</span>
                            </td>
                            <td class="text-right font-medium text-green-700 tabular-nums">{!! $k->jenis === 'masuk' ? '+' . e(rupiah($k->nominal)) : $strip !!}</td>
                            <td class="text-right font-medium text-red-600 tabular-nums">{!! $k->jenis === 'keluar' ? '−' . e(rupiah($k->nominal)) : $strip !!}</td>
                            <td @class(['text-right font-semibold tabular-nums', 'text-red-600' => $k->saldo_berjalan < 0, 'text-green-700' => $k->saldo_berjalan > 0, 'text-slate-500' => $k->saldo_berjalan == 0])>{{ rupiah($k->saldo_berjalan) }}</td>
                            <td class="max-w-44">
                                @if ($sumber)
                                    <span class="block truncate text-xs text-slate-600" title="{{ $sumber }}">{{ $sumber }}</span>
                                @else {!! $strip !!} @endif
                            </td>
                            <td class="max-w-40">
                                @if ($k->catatan)
                                    <span class="block truncate text-xs text-slate-600" x-data x-tip="@js($k->catatan)" tabindex="0">{{ $k->catatan }}</span>
                                @else {!! $strip !!} @endif
                            </td>
                            <td>@include('kas-proyek._aksi')</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-2 px-3 pt-3 text-sm text-slate-500 sm:px-4">
            <span>{{ $jumlahHasil }} catatan</span>
        </div>
        {{ $baris->links() }}
    @endif
</div>

{{-- Popup tambah / ubah transaksi kas manual --}}
<x-modal name="kas" judul-js="f.id ? 'Ubah Transaksi Kas' : 'Tambah Transaksi Kas'" :show="old('_form') === 'kas' && $errors->any()">
    <form method="POST" :action="action" class="flex min-h-0 flex-1 flex-col" novalidate>
        @csrf
        <template x-if="f.id"><input type="hidden" name="_method" value="PUT"></template>
        <input type="hidden" name="_form" value="kas"><input type="hidden" name="_id" :value="f.id">
        <input type="hidden" name="jenis" :value="f.jenis">
        <x-modal-body>
            <x-field label="Jenis" name="jenis" required>
                <div class="grid grid-cols-2 gap-1 rounded-kontrol bg-slate-200/60 p-1">
                    @foreach (['masuk' => ['Masuk', 'arrow-right', 'text-green-700'], 'keluar' => ['Keluar', 'arrow-left', 'text-red-600']] as $j => [$l, $ikon, $warna])
                        <button type="button" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-colors"
                                :class="f.jenis === '{{ $j }}' ? 'bg-white shadow-kartu {{ $warna }}' : 'text-slate-600 hover:bg-white/60'"
                                x-on:click="gantiJenis('{{ $j }}')" :disabled="f.id && f.jenis !== '{{ $j }}'" :aria-pressed="f.jenis === '{{ $j }}'">
                            <x-icon :name="$ikon" class="size-4"/> {{ $l }}
                        </button>
                    @endforeach
                </div>
                <p class="form-hint" x-show="f.id" x-cloak>Jenis tidak bisa diganti karena kode INC/EXP mengikuti jenis.</p>
            </x-field>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Tanggal" name="tanggal" required><x-input type="date" name="tanggal" x-model="f.tanggal" :max="now()->toDateString()"/></x-field>
                <x-field label="Kategori" name="kategori" required>
                    <x-select name="kategori" data-opsi-expr="opsiKategori" placeholder="Pilih kategori" x-model="f.kategori"/>
                </x-field>
            </div>
            <p x-show="f.jenis === 'masuk' && f.kategori === 'Penjualan'" x-cloak class="flex gap-2 rounded-kontrol bg-amber-50 px-3 py-2 text-xs text-amber-900">
                <x-icon name="warning" class="size-4 shrink-0 text-amber-500"/>Pembayaran konsumen sebaiknya dicatat lewat Transaksi Penjualan agar tercatat di transaksinya dan dialokasikan ke pos. Input di sini tidak dialokasikan.
            </p>
            <x-field label="Uraian" name="uraian" required><x-input name="uraian" x-model="f.uraian"/></x-field>
            <x-field label="Nominal" name="nominal" required><x-money name="nominal"/></x-field>
            <x-field label="Sumber/Transaksi" name="sumber"><x-input name="sumber" x-model="f.sumber" placeholder="Mis. nama penerima, no. nota"/></x-field>
            <x-field label="Catatan" name="catatan"><x-textarea name="catatan" rows="2" x-model="f.catatan"/></x-field>
        </x-modal-body>
        <x-modal-footer><x-button type="submit" x-text="f.id ? 'Simpan' : 'Tambah'">Simpan</x-button></x-modal-footer>
    </form>
</x-modal>
</div>
@endsection
