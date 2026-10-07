@extends('layouts.app')
@section('title', 'Data Konsumen')

@php
    // Label status sama dengan filter di Transaksi Penjualan
    $labelStatus = ['belum' => 'Belum Transaksi', 'menunggu' => 'Menunggu Bayar', 'reservasi' => 'Reservasi', 'booking' => 'Booking', 'dp' => 'DP', 'angsuran' => 'Angsuran', 'lunas' => 'Lunas', 'batal' => 'Batal'];
    // Opsi status dokumen sama dengan popup Ubah Dokumen
    $opsiDok = ['belum' => 'Belum', 'proses' => 'Proses', 'selesai' => 'Selesai'];
    $opsiDokumen = collect(\App\Models\ChecklistLegal::ITEM)->flatMap(fn ($label, $item) => collect($opsiDok)->mapWithKeys(fn ($l, $s) => ["{$item}:{$s}" => "{$label} · {$l}"]))->all();
    // [label, nilai lengkap, nilai singkat, kelas warna nilai]
    $kartu = [
        ['Total Konsumen', angka($stats['total']), null, 'text-slate-900'],
        ['Reservasi', angka($stats['reservasi']), null, 'text-slate-900'],
        ['Booking', angka($stats['booking']), null, 'text-slate-900'],
        ['DP', angka($stats['dp']), null, 'text-slate-900'],
        ['Cash/Lunas', angka($stats['lunas']), null, 'text-slate-900'],
        ['Angsuran', angka($stats['angsuran']), null, 'text-slate-900'],
        ['Total Nilai Penjualan', rupiah($stats['nilai_jual']), rupiah_singkat($stats['nilai_jual']), 'text-slate-900'],
        ['Total Pembayaran Masuk', rupiah($stats['terbayar']), rupiah_singkat($stats['terbayar']), 'text-green-700'],
    ];
    $adaFilter = collect(request()->only(['cari', 'status', 'agen', 'dokumen']))->filter(fn ($v) => filled($v))->isNotEmpty();
    $strip = '<span class="text-slate-400">—</span>';
    // Popup Ubah Dokumen: isi ulang dari input lama bila validasi gagal
    $dokAwal = old('_dok_id')
        ? collect(array_keys(\App\Models\ChecklistLegal::ITEM))->flatMap(fn ($i) => [$i . '_status' => old($i . '_status'), $i . '_tanggal' => old($i . '_tanggal')])
            ->merge(['id' => old('_dok_id'), 'kavling' => old('_dok_kavling'), 'catatan' => old('catatan'), 'spk_alasan' => null, 'url_spk' => null, 'url_ppjb' => null])->all()
        : ['id' => null];
@endphp

@section('content')
<div x-data="{ dok: @js($dokAwal), ubahDokumen(d) { this.dok = { ...d }; this.$dispatch('open-modal', 'dokumen') } }">
<x-page-header title="Data Konsumen" subtitle="Identitas pembeli. Status, pembayaran, dan dokumen dibaca dari transaksi." :breadcrumbs="['Penjualan' => null, 'Data Konsumen' => null]"/>

{{-- Kartu ringkas (sama dengan Transaksi Penjualan): 8 kolom di laptop, 4 di tablet, 2 di HP. Tanpa transaksi menunggu & batal. --}}
<div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-4 xl:grid-cols-8">
    @foreach ($kartu as [$label, $nilai, $singkat, $warna])
        <div class="min-w-0 rounded-kartu border border-slate-200 bg-white px-3 py-2.5">
            <p class="truncate text-[11px] font-medium text-slate-500" title="{{ $label }}">{{ $label }}</p>
            @if ($singkat)
                <p class="mt-0.5 truncate text-base font-semibold tabular-nums {{ $warna }}" x-data x-tip="@js($nilai)" title="{{ $nilai }}">{{ $singkat }}</p>
            @else
                <p class="mt-0.5 truncate text-base font-semibold tabular-nums {{ $warna }}">{{ $nilai }}</p>
            @endif
        </div>
    @endforeach
</div>

<div class="card">
    {{-- Panel: cari, filter, reset, tambah. Pilihan langsung terkirim, pencarian dengan Enter. --}}
    <form method="GET" action="{{ route('konsumen.index') }}" role="search"
          x-on:change="if ($event.target.type !== 'search') $el.requestSubmit()"
          class="grid grid-cols-2 gap-2 border-b border-slate-100 p-3 sm:grid-cols-3 lg:flex lg:flex-wrap lg:items-center xl:flex-nowrap">
        <div class="relative col-span-2 min-w-0 sm:col-span-3 lg:w-60 lg:flex-none">
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"/>
            <input type="search" name="cari" value="{{ request('cari') }}" placeholder="Cari nama, ID, kavling…" class="form-input pl-9" enterkeyhint="search" aria-label="Cari konsumen">
        </div>
        <x-select name="status" :options="$labelStatus" :value="request('status')" placeholder="Semua Status" aria-label="Status" class="lg:w-40"/>
        <x-select name="agen" :options="$agens" :value="request('agen')" placeholder="Semua Agen" aria-label="Agen" class="lg:w-40"/>
        <x-select name="dokumen" :options="$opsiDokumen" :value="request('dokumen')" placeholder="Filter Dokumen" aria-label="Filter dokumen" class="lg:w-40"/>
        <button type="submit" class="sr-only">Cari</button>
        <a href="{{ route('konsumen.index') }}" @class(['btn btn-secondary justify-center', 'pointer-events-none opacity-50' => ! $adaFilter]) @unless ($adaFilter) aria-disabled="true" tabindex="-1" @endunless>
            <x-icon name="filter-x" class="size-4"/> Reset Filter
        </a>
        <x-button icon="plus" x-on:click="$dispatch('open-modal', 'konsumen')" class="col-span-2 justify-center sm:col-span-1 lg:ml-auto">Tambah Konsumen</x-button>
    </form>

    @if ($konsumens->isEmpty())
        <x-empty-state :title="$adaFilter ? 'Tidak ada konsumen yang cocok' : 'Belum ada konsumen'" :message="$adaFilter ? 'Ubah pencarian atau reset filter.' : 'Tambah konsumen untuk mulai mencatat transaksi.'">
            @unless ($adaFilter)<x-button icon="plus" x-on:click="$dispatch('open-modal', 'konsumen')">Tambah Konsumen</x-button>@endunless
        </x-empty-state>
    @else
        {{-- Sel dua baris agar muat di laptop; di HP tabel digulir di dalam wadahnya --}}
        <div class="table-wrap">
            <table class="table min-w-[60rem] [&_td]:px-2.5 [&_th]:px-2.5">
                <thead>
                    <tr>
                        <th>ID Konsumen / Nama</th><th>Kavling</th><th>Tanggal Masuk</th><th>Status / Agen</th>
                        <th>Reservasi</th><th>Booking/SPK</th><th>PPJB</th><th>AJB/Legal</th><th class="w-px"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($konsumens as $k)
                        @php
                            $t = $k->tampil;
                            $c = $t?->checklist;
                            $resBayar = $t ? $t->terbayarJenis('reservasi') : 0;
                            $resStatus = $resBayar <= 0 ? ['belum', 'Belum', 'abu', null]
                                : ($resBayar >= (float) $t->biaya_reservasi ? ['terbayar', 'Terbayar', 'sukses', 'check'] : ['sebagian', 'Sebagian', 'kuning', null]);
                        @endphp
                        <tr>
                            <td class="max-w-52">
                                <a href="{{ route('konsumen.show', $k) }}" class="tautan text-xs font-medium tabular-nums">{{ $k->id_konsumen }}</a>
                                <span class="block truncate font-medium text-slate-900" title="{{ $k->nama_lengkap }}">{{ $k->nama_lengkap }}</span>
                            </td>
                            <td>
                                @if ($t)
                                    <span class="font-medium text-slate-900">{{ $t->kavling->kode_kavling }}</span>
                                    @if ($k->kavling_lain)<div class="text-xs text-slate-500">+{{ $k->kavling_lain }} kavling lain</div>@endif
                                @else {!! $strip !!} @endif
                            </td>
                            <td class="text-slate-600">{{ tanggal($k->created_at) }}</td>
                            <td class="max-w-48 whitespace-normal">
                                @if ($t)
                                    <x-status-bayar :t="$t" class="max-w-full"/>
                                    <div class="mt-0.5 truncate text-xs text-slate-500">{{ $t->agen->nama_agen ?? 'Tanpa agen' }}</div>
                                @else
                                    <x-badge status="belum" label="Belum Transaksi" warna="abu"/>
                                    <div class="mt-0.5 text-xs text-slate-400">—</div>
                                @endif
                            </td>
                            <td>
                                @if ($t)
                                    <x-badge :status="$resStatus[0]" :label="$resStatus[1]" :warna="$resStatus[2]" :ikon="$resStatus[3]"/>
                                    <div class="mt-0.5 text-xs text-slate-500 tabular-nums">{{ rupiah($resBayar) }}</div>
                                @else {!! $strip !!} @endif
                            </td>
                            <td>
                                @if ($t)
                                    <x-badge :status="$c?->spk_status ?? 'belum'"/>
                                    <div class="mt-0.5 text-xs text-slate-500 tabular-nums">{{ rupiah($t->terbayarJenis('booking')) }}</div>
                                @else {!! $strip !!} @endif
                            </td>
                            @foreach (['ppjb', 'ajb'] as $item)
                                <td>
                                    @if ($t)
                                        <x-badge :status="$c?->{$item . '_status'} ?? 'belum'"/>
                                        @if ($c?->{$item . '_tanggal'})<div class="mt-0.5 text-xs text-slate-500">{{ tanggal($c->{$item . '_tanggal'}) }}</div>@endif
                                    @else {!! $strip !!} @endif
                                </td>
                            @endforeach
                            <td>
                                <div class="flex justify-end gap-0.5">
                                    @if ($c && ! $t->isBatal())
                                        <x-icon-button icon="check-badge" label="Ubah dokumen" x-on:click="ubahDokumen({{ Js::from($c->dataForm()) }})"/>
                                    @endif
                                    <x-icon-button icon="chevron-right" label="Detail" :href="route('konsumen.show', $k)"/>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $konsumens->links() }}
    @endif
</div>

<x-modal name="konsumen" title="Tambah Konsumen" max-width="xl" :show="$errors->any() && ! old('_dok_id')">
    <form method="POST" action="{{ route('konsumen.store') }}" class="flex min-h-0 flex-1 flex-col">
        @csrf
        <x-modal-body>@include('konsumen._fields', ['k' => null])</x-modal-body>
        <x-modal-footer><x-button type="submit">Simpan</x-button></x-modal-footer>
    </form>
</x-modal>

@include('konsumen._dokumen-modal')
</div>
@endsection
