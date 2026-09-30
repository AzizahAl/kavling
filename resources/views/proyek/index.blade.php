@extends('layouts.app')
@section('title', 'Pengaturan Proyek')

@section('content')
<x-page-header title="Pengaturan Proyek"
               subtitle="Semua angka aturan bisnis diatur di sini, tidak perlu mengubah kode."
               :breadcrumbs="['Data Master' => null, 'Pengaturan Proyek' => null]"/>

@php
    $kosongPenting = collect(['refund_dp_persen', 'refund_angsuran_persen', 'komisi_default_persen', 'target_kewajiban_tanah', 'alamat_proyek', 'nama_pemilik_lahan', 'nama_pengelola', 'kota_dokumen'])
        ->filter(fn ($k) => $nilai[$k] === null || $nilai[$k] === '')
        ->map(fn ($k) => $definisi[$k][1]);
@endphp

@if ($kosongPenting->isNotEmpty())
    <div class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <x-icon name="warning" class="mt-0.5"/>
        <div>
            <p class="font-medium">Beberapa pengaturan belum diisi:</p>
            <p class="mt-0.5">{{ $kosongPenting->implode(', ') }}.</p>
            <p class="mt-1 text-amber-700">Fitur yang memakainya (pembatalan, komisi, kelayakan bagi laba, cetak dokumen) akan meminta Anda melengkapinya dulu.</p>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('proyek.update') }}"
      x-data="{
          alokasi: {
              tanah: {{ (float) old('alokasi_tanah', $nilai['alokasi_tanah']) }},
              legal: {{ (float) old('alokasi_legal_infra', $nilai['alokasi_legal_infra']) }},
              marketing: {{ (float) old('alokasi_marketing', $nilai['alokasi_marketing']) }},
              cadangan: {{ (float) old('alokasi_cadangan', $nilai['alokasi_cadangan']) }},
              ops: {{ (float) old('alokasi_operasional', $nilai['alokasi_operasional']) }},
          },
          get totalAlokasi() { return Object.values(this.alokasi).reduce((a, b) => a + (Number(b) || 0), 0) },
      }">
    @csrf @method('PUT')

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[220px_minmax(0,1fr)]">
        {{-- Navigasi bagian (desktop) --}}
        <nav class="hidden lg:block">
            <div class="sticky top-24 space-y-1 text-sm">
                @foreach ($grup as $kode => $judul)
                    <a href="#grup-{{ $kode }}" class="block rounded-lg px-3 py-2 text-slate-600 hover:bg-white hover:text-forest-700">{{ $judul }}</a>
                @endforeach
            </div>
        </nav>

        <div class="space-y-6">
            @foreach ($grup as $kode => $judul)
                <x-card :title="$judul" id="grup-{{ $kode }}" class="scroll-mt-24">
                    @if ($kode === 'alokasi')
                        <x-slot:actions>
                            <span class="badge" :class="Math.abs(totalAlokasi - 100) < 0.001 ? '{{ \App\Support\Status::kelas('lunas') }}' : '{{ \App\Support\Status::kelas('batal') }}'">
                                Total: <span x-text="totalAlokasi.toLocaleString('id-ID')"></span>%
                            </span>
                        </x-slot:actions>
                    @endif

                    <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                        @foreach ($definisi as $kunci => [$g, $label, $tipe, , $ket])
                            @continue($g !== $kode)
                            @php
                                $model = [
                                    'alokasi_tanah' => 'alokasi.tanah', 'alokasi_legal_infra' => 'alokasi.legal',
                                    'alokasi_marketing' => 'alokasi.marketing', 'alokasi_cadangan' => 'alokasi.cadangan',
                                    'alokasi_operasional' => 'alokasi.ops',
                                ][$kunci] ?? null;
                                $val = is_array($nilai[$kunci]) ? implode(', ', $nilai[$kunci]) : $nilai[$kunci];
                                $kosong = $val === null || $val === '';
                            @endphp
                            <x-field :label="$label" :name="$kunci" :hint="$ket" :class="$tipe === 'daftar' ? 'sm:col-span-2' : ''">
                                @switch($tipe)
                                    @case('rupiah')
                                        <x-money :name="$kunci" :value="$val" :class="$kosong ? 'border-amber-300 bg-amber-50/40' : ''"/>
                                        @break
                                    @case('persen')
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0" max="100" name="{{ $kunci }}" id="{{ $kunci }}"
                                                   value="{{ old($kunci, $val) }}" @if ($model) x-model.number="{{ $model }}" @endif
                                                   @class(['form-input pr-9', 'is-invalid' => $errors->has($kunci), 'border-amber-300 bg-amber-50/40' => $kosong])>
                                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-slate-400">%</span>
                                        </div>
                                        @break
                                    @case('angka')
                                        <x-input :name="$kunci" type="number" min="0" step="1" :value="$val"/>
                                        @break
                                    @case('desimal')
                                        <x-input :name="$kunci" type="number" min="0" step="0.01" :value="$val"/>
                                        @break
                                    @default
                                        <x-input :name="$kunci" :value="$val" :class="$kosong ? 'border-amber-300 bg-amber-50/40' : ''"/>
                                @endswitch
                            </x-field>
                        @endforeach
                    </div>

                    @if ($kode === 'harga')
                        <div class="mt-5 overflow-hidden rounded-lg border border-slate-200">
                            <div class="table-wrap">
                                <table class="table">
                                    <thead><tr><th>Tahap</th><th>Unit Terjual</th><th class="text-right">Harga / m²</th><th>Status</th></tr></thead>
                                    <tbody>
                                        @foreach ($tahap as $t)
                                            <tr>
                                                <td class="font-medium">{{ $t['nama_tahap'] }}</td>
                                                <td>{{ $t['unit_mulai'] }} – {{ $t['unit_sampai'] }}</td>
                                                <td class="text-right tabular-nums">{{ rupiah($t['harga_per_m2']) }}</td>
                                                <td>
                                                    @if ($t['nomor'] === $tahapAktif)
                                                        <x-badge status="aktif"/>
                                                    @elseif ($t['nomor'] < $tahapAktif)
                                                        <x-badge status="selesai" label="Terlewati"/>
                                                    @else
                                                        <x-badge status="menunggu"/>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <p class="form-hint mt-2">Saat ini {{ $terjual }} kavling terjual (PPJB ditandatangani). Tabel di atas mengikuti nilai tersimpan; simpan untuk memperbarui.</p>
                    @endif
                </x-card>
            @endforeach

            <div class="sticky bottom-0 -mx-4 flex justify-end gap-2 border-t border-slate-200 bg-slate-50/95 px-4 py-3 backdrop-blur sm:mx-0 sm:rounded-xl sm:border sm:bg-white">
                <a href="{{ route('proyek.index') }}" class="btn btn-secondary">Batalkan Perubahan</a>
                <button type="submit" class="btn btn-primary"><x-icon name="check" class="h-4 w-4"/> Simpan Pengaturan</button>
            </div>
        </div>
    </div>
</form>
@endsection
