@extends('layouts.app')
@section('title', 'Pengaturan Proyek')

@php
    $kosongPenting = collect(['refund_dp_persen', 'refund_angsuran_persen', 'komisi_default_persen', 'target_kewajiban_tanah', 'alamat_proyek', 'nama_pemilik_lahan', 'nama_pengelola', 'kota_dokumen'])
        ->filter(fn ($k) => $nilai[$k] === null || $nilai[$k] === '')
        ->map(fn ($k) => $definisi[$k][1]);
    $ikonGrup = ['proyek' => 'building', 'harga' => 'tag', 'penjualan' => 'cart', 'alokasi' => 'cashflow', 'laba' => 'scale', 'penomoran' => 'document'];
    $alokasiAwal = collect(['tanah' => 'alokasi_tanah', 'legal' => 'alokasi_legal_infra', 'marketing' => 'alokasi_marketing', 'cadangan' => 'alokasi_cadangan', 'ops' => 'alokasi_operasional'])
        ->map(fn ($k) => (string) old($k, $nilai[$k]));
@endphp

@section('content')
<x-page-header title="Pengaturan Proyek" subtitle="Angka aturan bisnis proyek." :breadcrumbs="['Data Master' => null, 'Pengaturan' => null]"/>

@if ($kosongPenting->isNotEmpty())
    <div class="mb-5 flex items-start gap-3 rounded-kartu border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <x-icon name="warning" class="mt-0.5 size-5 text-amber-500"/>
        <div class="min-w-0">
            <p class="font-medium">Belum diisi</p>
            <p class="mt-0.5 text-amber-800">{{ $kosongPenting->implode(' · ') }}</p>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('proyek.update') }}"
      x-data="{
          q: '',
          alokasi: @js($alokasiAwal),
          get totalAlokasi() { return Object.values(this.alokasi).reduce((a, b) => a + (Number(String(b).replace(',', '.')) || 0), 0) },
          cocok(teks) { return ! this.q || teks.toLowerCase().includes(this.q.toLowerCase()) },
      }">
    @csrf @method('PUT')

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[210px_minmax(0,1fr)]">
        <nav class="hidden lg:block" aria-label="Bagian pengaturan">
            <div class="sticky top-24 space-y-0.5 text-sm">
                @foreach ($grup as $kode => $judul)
                    <a href="#grup-{{ $kode }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-slate-600 transition-colors hover:bg-white hover:text-brand-700">
                        <x-icon :name="$ikonGrup[$kode] ?? 'cog'" class="size-4 opacity-70"/>{{ $judul }}
                    </a>
                @endforeach
            </div>
        </nav>

        <div class="min-w-0 space-y-5">
            <div class="card p-3 sm:p-4">
                <div class="relative sm:max-w-xs">
                    <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"/>
                    <input type="search" x-model="q" placeholder="Cari pengaturan…" class="form-input pl-9" aria-label="Cari pengaturan">
                </div>
            </div>

            @foreach ($grup as $kode => $judul)
                @php $labelGrup = collect($definisi)->filter(fn ($d) => $d[0] === $kode)->map(fn ($d) => $d[1])->implode(' '); @endphp
                <x-card :title="$judul" id="grup-{{ $kode }}" class="scroll-mt-24" x-show="cocok({{ Js::from($judul . ' ' . $labelGrup) }})">
                    @if ($kode === 'alokasi')
                        <x-slot:actions>
                            <span class="badge" :class="Math.abs(totalAlokasi - 100) < 0.001 ? '{{ \App\Support\Status::kelas('selesai') }}' : '{{ \App\Support\Status::kelas('batal') }}'">
                                Total <span x-text="totalAlokasi.toLocaleString('id-ID')"></span>%
                            </span>
                        </x-slot:actions>
                    @endif

                    <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                        @foreach ($definisi as $kunci => [$g, $label, $tipe, , $ket])
                            @continue($g !== $kode)
                            @php
                                $model = ['alokasi_tanah' => 'tanah', 'alokasi_legal_infra' => 'legal', 'alokasi_marketing' => 'marketing', 'alokasi_cadangan' => 'cadangan', 'alokasi_operasional' => 'ops'][$kunci] ?? null;
                                $val = is_array($nilai[$kunci]) ? implode(', ', $nilai[$kunci]) : $nilai[$kunci];
                                $wajib = in_array($kunci, ['harga_awal_m2', 'unit_per_kenaikan', 'jumlah_tahap', 'jumlah_kavling', 'tenor_maksimal', 'prefix_konsumen', 'prefix_transaksi', 'prefix_pembayaran', 'prefix_kavling']);
                            @endphp
                            <x-field :label="$label" :name="$kunci" :hint="$ket" :required="$wajib"
                                     :class="$tipe === 'daftar' ? 'sm:col-span-2' : ''" x-show="cocok({{ Js::from($judul . ' ' . $label) }})">
                                @switch($tipe)
                                    @case('rupiah')
                                        <x-money :name="$kunci" :value="$val"/>
                                        @break
                                    @case('persen')
                                        <div class="relative">
                                            {{-- Teks dengan keyboard angka: koma desimal (2,5) diterima --}}
                                            <input type="text" inputmode="decimal" autocomplete="off" name="{{ $kunci }}" id="{{ $kunci }}"
                                                   value="{{ old($kunci, $val) }}" @if ($model) x-model="alokasi.{{ $model }}" @endif placeholder="0"
                                                   @if ($errors->has($kunci)) aria-invalid="true" @endif
                                                   @class(['form-input pr-9 tabular-nums', 'is-invalid' => $errors->has($kunci)])>
                                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-slate-400">%</span>
                                        </div>
                                        @break
                                    @case('angka')
                                        <x-input :name="$kunci" type="text" inputmode="numeric" :value="$val" class="tabular-nums"/>
                                        @break
                                    @case('desimal')
                                        <x-input :name="$kunci" type="text" inputmode="decimal" :value="$val" class="tabular-nums"/>
                                        @break
                                    @default
                                        <x-input :name="$kunci" :value="$val"/>
                                @endswitch
                            </x-field>
                        @endforeach
                    </div>

                    @if ($kode === 'harga')
                        <div class="mt-5 overflow-hidden rounded-kontrol border border-slate-200">
                            <div class="table-wrap">
                                <table class="table">
                                    <thead><tr><th>Tahap</th><th>Unit Terjual</th><th class="text-right">Harga/m²</th><th>Status</th></tr></thead>
                                    <tbody>
                                        @foreach ($tahap as $t)
                                            <tr>
                                                <td class="font-medium text-slate-900">{{ $t['nama_tahap'] }}</td>
                                                <td>{{ $t['unit_mulai'] }}–{{ $t['unit_sampai'] }}</td>
                                                <td class="text-right tabular-nums">{{ rupiah($t['harga_per_m2']) }}</td>
                                                <td>
                                                    @if ($t['nomor'] === $tahapAktif)<x-badge status="aktif"/>
                                                    @elseif ($t['nomor'] < $tahapAktif)<x-badge status="menunggu" label="Terlewati"/>
                                                    @else<x-badge status="menunggu"/>@endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <p class="form-hint">{{ $terjual }} kavling terjual. Tabel ikut berubah setelah disimpan.</p>
                    @endif
                </x-card>
            @endforeach

            <div class="sticky bottom-0 z-10 -mx-4 flex justify-end gap-2 border-t border-slate-200 bg-white/90 px-4 py-3 backdrop-blur-md sm:mx-0 sm:rounded-kartu sm:border sm:shadow-menu">
                <x-button variant="secondary" :href="route('proyek.index')">Batal</x-button>
                <x-button type="submit" icon="check">Simpan</x-button>
            </div>
        </div>
    </div>
</form>
@endsection
