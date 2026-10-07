@extends('layouts.app')
@section('title', 'Pengaturan Proyek')

@php
    $kosongPenting = collect(['alamat_proyek', 'nama_pemilik_lahan', 'nama_pengelola', 'kota_dokumen'])
        ->filter(fn ($k) => $nilai[$k] === null || $nilai[$k] === '')
        ->map(fn ($k) => $definisi[$k][1]);

    // Susunan tampilan (data & kunci pengaturan tetap). id "grup-…" dipakai tautan dari menu lain, mis. #grup-alokasi.
    $bagian = [
        'proyek'    => ['Identitas Proyek', 'building', 'Data dasar proyek dan pihak yang tercetak di SPK & PPJB.'],
        'penjualan' => ['Biaya & Pembayaran', 'banknotes', 'Biaya di luar harga kavling, uang muka, dan lama angsuran.'],
        'komisi'    => ['Komisi Agen', 'coins', 'Nominal komisi standar per transaksi dan aturannya bila transaksi batal.'],
        'aturan'    => ['Aturan Transaksi', 'check-badge', 'Kapan kavling terjual, komisi menjadi hak, dan kavling dilepas.'],
        'alokasi'   => ['Alokasi Kas', 'cashflow', 'Pembagian setiap uang masuk dari konsumen ke pos-pos kas. Total harus 100%.'],
        'laba'      => ['Bagi Laba', 'scale', 'Pembagian laba bersih dan anggaran yang harus tercukupi sebelum laba dibagi.'],
        'penomoran' => ['Penomoran', 'document', 'Awalan kode yang dibuat otomatis oleh sistem.'],
        'formulir'  => ['Formulir', 'printer', 'Dokumen resmi dan formulir yang kolom serta angkanya mengikuti data sistem.'],
    ];
    // Kunci per bagian: untuk membuka bagian yang berisi kesalahan validasi
    $kunciBagian = [
        'proyek'    => ['nama_proyek', 'kota_dokumen', 'alamat_proyek', 'nama_pemilik_lahan', 'luas_lahan_are', 'jumlah_kavling', 'lebar_jalan_m', 'status_legal_lahan', 'nama_pengelola', 'nik_pengelola', 'hp_pengelola', 'alamat_pengelola'],
        'penjualan' => ['biaya_reservasi', 'biaya_booking', 'potongan_booking', 'masa_reservasi_hari', 'dp_minimal_persen', 'dp_anjuran_persen', 'tenor_maksimal'],
        'komisi'    => ['komisi_nominal', 'komisi_saat_batal'],
        'aturan'    => ['terjual_saat', 'komisi_hak_saat', 'batas_tahan_jam'],
        'alokasi'   => ['alokasi_tanah', 'alokasi_legal_infra', 'alokasi_marketing', 'alokasi_cadangan', 'alokasi_operasional'],
        'laba'      => ['laba_pengelola_persen', 'laba_pemilik_persen', 'kategori_rab_legal_infra'],
        'penomoran' => ['prefix_konsumen', 'prefix_transaksi', 'prefix_pembayaran', 'prefix_kavling'],
    ];
    $bagianError = collect($kunciBagian)->search(fn ($keys) => $errors->hasAny($keys));
    $bagianAwal = $bagianError ?: (array_key_exists((string) request('bagian'), $bagian) ? request('bagian') : 'proyek');
    $grupFormulir = collect(\App\Support\Formulir::DAFTAR)->groupBy(fn ($f) => $f[3], preserveKeys: true);
    // Label singkat & satuan
    $label = [
        'nama_proyek' => 'Nama Proyek', 'alamat_proyek' => 'Alamat / Lokasi', 'luas_lahan_are' => 'Luas Lahan', 'jumlah_kavling' => 'Jumlah Kavling',
        'lebar_jalan_m' => 'Lebar Jalan Dalam', 'status_legal_lahan' => 'Status Legal Lahan', 'nama_pemilik_lahan' => 'Nama Pemilik Lahan', 'kota_dokumen' => 'Kota Penandatanganan',
        'nama_pengelola' => 'Nama', 'nik_pengelola' => 'NIK', 'alamat_pengelola' => 'Alamat', 'hp_pengelola' => 'No. HP',
        'biaya_reservasi' => 'Biaya Reservasi', 'biaya_booking' => 'Booking Fee', 'potongan_booking' => 'Potongan Booking bila Batal',
        'dp_minimal_persen' => 'DP Minimal', 'dp_anjuran_persen' => 'DP Anjuran', 'tenor_maksimal' => 'Tenor Maksimum', 'masa_reservasi_hari' => 'Masa Berlaku Reservasi',
        'komisi_nominal' => 'Komisi per Transaksi', 'komisi_saat_batal' => 'Bila Transaksi Batal',
        'terjual_saat' => 'Kavling Dihitung Terjual Saat', 'komisi_hak_saat' => 'Komisi Menjadi Hak Saat', 'batas_tahan_jam' => 'Batas Tahan Kavling',
        'alokasi_tanah' => 'Tanah / Pemilik Lahan', 'alokasi_legal_infra' => 'Legalitas & Infrastruktur', 'alokasi_marketing' => 'Marketing & Komisi',
        'alokasi_cadangan' => 'Kas Cadangan', 'alokasi_operasional' => 'Operasional',
        'laba_pengelola_persen' => 'Bagian Pengelola', 'laba_pemilik_persen' => 'Bagian Pemilik Lahan',
        'prefix_konsumen' => 'ID Konsumen', 'prefix_transaksi' => 'ID Transaksi', 'prefix_pembayaran' => 'No. Kwitansi', 'prefix_kavling' => 'Kode Kavling',
    ];
    $satuan = ['luas_lahan_are' => 'are', 'lebar_jalan_m' => 'm', 'jumlah_kavling' => 'unit', 'tenor_maksimal' => 'bulan', 'masa_reservasi_hari' => 'hari', 'batas_tahan_jam' => 'jam'];

    // Alokasi & bagi laba: dihitung langsung saat diketik
    $alokasi = ['alokasi_tanah' => '#1a4028', 'alokasi_legal_infra' => '#2e8b62', 'alokasi_marketing' => '#7ec7a5', 'alokasi_cadangan' => '#94a3b8', 'alokasi_operasional' => '#475569'];
    $laba = ['laba_pengelola_persen' => '#2e8b62', 'laba_pemilik_persen' => '#94a3b8'];
    $nilaiAwal = collect(array_merge(array_keys($alokasi), array_keys($laba)))->mapWithKeys(fn ($k) => [$k => (string) old($k, $nilai[$k])]);
    $kategoriLegal = collect(old('kategori_rab_legal_infra') !== null ? explode(',', old('kategori_rab_legal_infra')) : ($nilai['kategori_rab_legal_infra'] ?? []))
        ->map(fn ($k) => trim($k))->filter()->values();
    $prefixAwal = collect(['prefix_konsumen', 'prefix_transaksi', 'prefix_pembayaran', 'prefix_kavling'])->mapWithKeys(fn ($k) => [$k => (string) old($k, $nilai[$k])]);
@endphp

@section('content')
<x-page-header title="Pengaturan Proyek" subtitle="Angka dan aturan bisnis yang dipakai di seluruh aplikasi." :breadcrumbs="['Pengaturan' => null, 'Pengaturan Proyek' => null]"/>

@if ($kosongPenting->isNotEmpty())
    <div class="mb-5 flex items-start gap-3 rounded-kartu border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <x-icon name="warning" class="mt-0.5 size-5 text-amber-500"/>
        <div class="min-w-0">
            <p class="font-medium">Data untuk SPK & PPJB belum lengkap</p>
            <p class="mt-0.5 text-amber-800">{{ $kosongPenting->implode(' · ') }}</p>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('proyek.update') }}" x-ref="form" novalidate
      x-data="{
          v: @js($nilaiAwal), warna: @js($alokasi + $laba), kategori: @js($kategoriLegal), prefix: @js($prefixAwal),
          aktif: @js($bagianAwal), awal: '', kotor: @js($errors->any()), menyimpan: false,
          angka(x) { return Number(String(x ?? '').replace(/\s/g, '').replace(',', '.')) || 0 },
          total(keys) { return keys.reduce((a, k) => a + this.angka(this.v[k]), 0) },
          pas(keys) { return Math.abs(this.total(keys) - 100) < 0.001 },
          fmt(n) { return n.toLocaleString('id-ID', { maximumFractionDigits: 2 }) },
          kunciForm() { const d = new FormData(this.$refs.form); d.delete('_token'); d.delete('_method'); return new URLSearchParams(d).toString() },
          periksa() { this.$nextTick(() => setTimeout(() => { this.kotor = this.kunciForm() !== this.awal }, 0)) },
          init() {
              setTimeout(() => { this.awal = this.kunciForm(); if (! this.kotor) this.kotor = false }, 50);
              window.addEventListener('beforeunload', e => { if (this.kotor && ! this.menyimpan) { e.preventDefault(); e.returnValue = '' } });
              const dariHash = location.hash.startsWith('#grup-') ? location.hash.slice(6) : null;
              if (dariHash && @js(array_keys($bagian)).includes(dariHash) && ! @js((bool) $bagianError)) this.aktif = dariHash;
          },
          pilih(kode) { this.aktif = kode; history.replaceState(null, '', '#grup-' + kode); window.scrollTo({ top: 0, behavior: 'smooth' }) },
          batalkan() { this.menyimpan = true; location.reload() },
      }"
      x-on:input="periksa()" x-on:change="periksa()" x-on:money-changed="periksa()"
      x-on:submit="menyimpan = true">
    @csrf @method('PUT')

    {{-- Navigasi bagian: tab geser di HP --}}
    <nav class="-mx-4 mb-4 overflow-x-auto px-4 lg:hidden" aria-label="Bagian pengaturan">
        <div class="inline-flex min-w-max gap-1 rounded-kontrol bg-slate-200/60 p-1">
            @foreach ($bagian as $kode => [$judul, $ikon])
                <a href="#grup-{{ $kode }}" x-on:click.prevent="pilih('{{ $kode }}')" class="inline-flex h-8 items-center gap-1.5 rounded-lg px-3 text-[13px] font-medium whitespace-nowrap transition-colors"
                   :class="aktif === '{{ $kode }}' ? 'bg-white text-slate-900 shadow-kartu' : 'text-slate-600'">{{ $judul }}</a>
            @endforeach
        </div>
    </nav>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[220px_minmax(0,1fr)]">
        {{-- Navigasi bagian: kiri di laptop, menandai bagian aktif saat digulir --}}
        <nav class="hidden lg:block" aria-label="Bagian pengaturan">
            <div class="sticky top-24 space-y-0.5 text-sm">
                @foreach ($bagian as $kode => [$judul, $ikon])
                    <a href="#grup-{{ $kode }}" x-on:click.prevent="pilih('{{ $kode }}')"
                       class="flex items-center gap-2.5 rounded-lg border-l-2 px-3 py-2 transition-colors"
                       :class="aktif === '{{ $kode }}' ? 'border-brand-600 bg-white font-medium text-brand-800 shadow-kartu' : 'border-transparent text-slate-600 hover:bg-white/70 hover:text-slate-900'">
                        <x-icon :name="$ikon" class="size-4 opacity-70"/>{{ $judul }}
                    </a>
                @endforeach
            </div>
        </nav>

        <div class="min-w-0 space-y-5">
            @foreach ($bagian as $kode => [$judul, $ikon, $ket])
                <section id="grup-{{ $kode }}" x-show="aktif === '{{ $kode }}'" @if ($kode !== $bagianAwal) x-cloak @endif class="scroll-mt-24 rounded-kartu border border-slate-200 bg-white shadow-kartu">
                    <header class="flex items-start gap-3 border-b border-slate-100 px-5 py-4">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700"><x-icon :name="$ikon" class="size-[18px]"/></span>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-sm font-semibold text-slate-900">{{ $judul }}</h2>
                            <p class="text-xs text-slate-500">{{ $ket }}</p>
                        </div>
                        @if (in_array($kode, ['alokasi', 'laba']))
                            @php $keys = array_keys($kode === 'alokasi' ? $alokasi : $laba); @endphp
                            <span class="badge shrink-0" :class="pas(@js($keys)) ? '{{ \App\Support\Status::kelas('selesai') }}' : '{{ \App\Support\Status::kelas('batal') }}'">
                                Total <span class="tabular-nums" x-text="fmt(total(@js($keys))) + '%'"></span>
                            </span>
                        @endif
                    </header>

                    <div class="space-y-5 p-5">
                        @switch($kode)
                            @case('proyek')
                                <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                                    @foreach (['nama_proyek', 'kota_dokumen', 'alamat_proyek', 'nama_pemilik_lahan', 'luas_lahan_are', 'jumlah_kavling', 'lebar_jalan_m', 'status_legal_lahan'] as $k)
                                        @include('proyek._isian', ['kunci' => $k, 'label' => $label[$k], 'satuan' => $satuan[$k] ?? null, 'lebar' => $k === 'alamat_proyek' ? 'sm:col-span-2' : ''])
                                    @endforeach
                                </div>
                                <div class="border-t border-slate-100 pt-4">
                                    <p class="mb-3 text-xs font-semibold tracking-wide text-slate-500 uppercase">Pengelola / Penjual · Pihak Pertama di PPJB</p>
                                    <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                                        @foreach (['nama_pengelola', 'nik_pengelola', 'hp_pengelola', 'alamat_pengelola'] as $k)
                                            @include('proyek._isian', ['kunci' => $k, 'label' => $label[$k], 'lebar' => $k === 'alamat_pengelola' ? 'sm:col-span-2' : ''])
                                        @endforeach
                                    </div>
                                </div>
                                @break

                            @case('penjualan')
                                <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                                    @foreach (['biaya_reservasi', 'biaya_booking', 'potongan_booking', 'masa_reservasi_hari', 'dp_minimal_persen', 'dp_anjuran_persen', 'tenor_maksimal'] as $k)
                                        @include('proyek._isian', ['kunci' => $k, 'label' => $label[$k], 'satuan' => $satuan[$k] ?? null])
                                    @endforeach
                                </div>
                                @break

                            @case('komisi')
                                <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                                    @include('proyek._isian', ['kunci' => 'komisi_nominal', 'label' => $label['komisi_nominal']])
                                    @include('proyek._isian', ['kunci' => 'komisi_saat_batal', 'label' => $label['komisi_saat_batal']])
                                </div>
                                @break

                            @case('aturan')
                                <div class="space-y-5">
                                    @include('proyek._isian', ['kunci' => 'terjual_saat', 'label' => $label['terjual_saat']])
                                    @include('proyek._isian', ['kunci' => 'komisi_hak_saat', 'label' => $label['komisi_hak_saat']])
                                    <div class="grid gap-x-5 sm:grid-cols-2">
                                        @include('proyek._isian', ['kunci' => 'batas_tahan_jam', 'label' => $label['batas_tahan_jam'], 'satuan' => 'jam'])
                                    </div>
                                </div>
                                @break

                            @case('alokasi')
                            @case('laba')
                                @php $daftar = $kode === 'alokasi' ? $alokasi : $laba; $keys = array_keys($daftar); @endphp
                                {{-- Batang pembagian --}}
                                <div>
                                    <div class="flex h-3 overflow-hidden rounded-full bg-slate-100" :class="pas(@js($keys)) ? '' : 'ring-2 ring-red-400 ring-offset-1'">
                                        @foreach ($daftar as $k => $w)
                                            <div class="h-full transition-all duration-300" style="background: {{ $w }}" :style="{ width: Math.min(100, angka(v['{{ $k }}'])) / Math.max(100, total(@js($keys))) * 100 + '%' }" title="{{ $label[$k] }}"></div>
                                        @endforeach
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600">
                                        @foreach ($daftar as $k => $w)
                                            <span class="inline-flex items-center gap-1.5"><span class="size-2.5 rounded-sm" style="background: {{ $w }}"></span>{{ $label[$k] }} <span class="font-medium tabular-nums text-slate-900" x-text="fmt(angka(v['{{ $k }}'])) + '%'"></span></span>
                                        @endforeach
                                    </div>
                                    <p x-show="! pas(@js($keys))" x-cloak class="mt-2 flex items-center gap-1.5 text-xs font-medium text-red-600">
                                        <x-icon name="alert" class="size-4"/> Total harus 100% (sekarang <span x-text="fmt(total(@js($keys))) + '%'"></span>).
                                    </p>
                                </div>
                                <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                                    @foreach ($daftar as $k => $w)
                                        @include('proyek._isian', ['kunci' => $k, 'label' => $label[$k], 'model' => "v['{$k}']"])
                                    @endforeach
                                </div>
                                @if ($kode === 'laba')
                                    <fieldset class="border-t border-slate-100 pt-4">
                                        <legend class="form-label">Kategori RAB Target Legal & Infrastruktur</legend>
                                        <p class="form-hint -mt-1 mb-2">Laba baru bisa dibagi bila alokasi Legal & Infrastruktur mencukupi anggaran RAB kategori terpilih.</p>
                                        <input type="hidden" name="kategori_rab_legal_infra" :value="kategori.join(', ')">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach (array_keys(\App\Models\Rab::KATEGORI) as $kat)
                                                <label class="cursor-pointer">
                                                    <input type="checkbox" value="{{ $kat }}" x-model="kategori" class="peer sr-only">
                                                    <span class="inline-flex h-8 items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 text-[13px] text-slate-600 transition-colors peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-800 peer-focus-visible:ring-2 peer-focus-visible:ring-brand-500/40 hover:border-slate-300">
                                                        {{ $kat }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('kategori_rab_legal_infra')<p class="form-error" role="alert"><x-icon name="alert" class="mt-px size-3.5"/>{{ $message }}</p>@enderror
                                    </fieldset>
                                @endif
                                @break

                            @case('formulir')
                                @foreach (['transaksi' => ['Dokumen Transaksi', 'Terisi otomatis dari setiap transaksi dan bisa diunduh dari detail transaksi atau Data Konsumen › Dokumen. Di sini tersedia versi kosong.'],
                                           'toolkit' => ['Formulir & Materi Marketing', 'Kolom formulir sama dengan form di sistem; angka profil & pricelist dibaca langsung dari Pengaturan, Master Kavling, dan Skema Harga.']] as $g => [$judulG, $ketG])
                                    <div @class(['border-t border-slate-100 pt-5' => ! $loop->first])>
                                        <p class="text-xs font-semibold tracking-wide text-slate-500 uppercase">{{ $judulG }}</p>
                                        <p class="mt-0.5 mb-3 text-xs text-slate-500">{{ $ketG }}</p>
                                        <ul class="divide-y divide-slate-100 rounded-kontrol border border-slate-200">
                                            @foreach ($grupFormulir[$g] ?? [] as $jenis => [$nama, $kertas, $halaman, , $ketF])
                                                <li class="flex flex-wrap items-center gap-3 px-4 py-3">
                                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500"><x-icon name="document" class="size-[18px]"/></span>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-sm font-medium text-slate-900">{{ $nama }}</p>
                                                        <p class="text-xs text-slate-500">{{ $ketF }} · {{ strtoupper($kertas) }} · {{ count($halaman) }} halaman</p>
                                                    </div>
                                                    <div class="flex shrink-0 gap-1.5">
                                                        <x-button variant="secondary" size="sm" icon="eye" :href="route('formulir.lihat', $jenis)">Lihat</x-button>
                                                        <x-button variant="secondary" size="sm" icon="download" :href="route('formulir.unduh', $jenis)">PDF</x-button>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                                @break

                            @case('penomoran')
                                <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                                    @foreach (['prefix_konsumen' => '-' . now()->year . '-0001', 'prefix_transaksi' => '-' . now()->year . '-0001', 'prefix_pembayaran' => '-' . now()->year . '-0001', 'prefix_kavling' => '-A01'] as $k => $contoh)
                                        <div>
                                            @include('proyek._isian', ['kunci' => $k, 'label' => $label[$k], 'model' => "prefix['{$k}']"])
                                            <p class="mt-1 text-xs text-slate-500">Contoh: <span class="font-medium text-slate-700 tabular-nums" x-text="(prefix['{{ $k }}'] || '…').toUpperCase() + '{{ $contoh }}'"></span></p>
                                        </div>
                                    @endforeach
                                </div>
                                @break
                        @endswitch
                    </div>
                </section>
            @endforeach

            {{-- Baris simpan: tetap terlihat di bawah layar --}}
            <div x-show="aktif !== 'formulir' || kotor" class="sticky bottom-0 z-10 -mx-4 flex items-center justify-between gap-3 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur-md sm:mx-0 sm:rounded-kartu sm:border sm:shadow-menu">
                <p class="flex min-w-0 items-center gap-2 text-sm" :class="kotor ? 'text-amber-700' : 'text-slate-500'">
                    <span class="size-2 shrink-0 rounded-full" :class="kotor ? 'bg-amber-500' : 'bg-slate-300'"></span>
                    <span class="truncate" x-text="kotor ? 'Ada perubahan belum disimpan' : 'Semua perubahan tersimpan'"></span>
                </p>
                <div class="flex shrink-0 gap-2">
                    <button type="button" class="btn btn-secondary" x-on:click="batalkan()" :disabled="! kotor">Batalkan</button>
                    <button type="submit" class="btn btn-primary" :disabled="! kotor"><x-icon name="check" class="size-4"/> Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
