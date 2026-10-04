@extends('layouts.app')
@section('title', 'Formulir & Marketing Toolkit')

@php
    $judulGrup = [
        'transaksi' => ['Formulir Transaksi (kosong)', 'Untuk diisi tangan. Versi terisi otomatis ada di halaman tiap transaksi → Lainnya.'],
        'toolkit'   => ['Marketing Toolkit', 'Bahan presentasi dan form kerja sales.'],
    ];
@endphp

@section('content')
<x-page-header title="Formulir & Marketing Toolkit" subtitle="Format sesuai dokumen resmi proyek. Buka untuk melihat, cetak, atau unduh PDF."
               :breadcrumbs="['Marketing' => null, 'Formulir' => null]"/>

<div class="space-y-5">
    @foreach ($judulGrup as $kunci => [$judul, $ket])
        @continue(! isset($grup[$kunci]))
        <x-card :title="$judul" :subtitle="$ket" :padding="false">
            <ul class="divide-y divide-slate-100">
                @foreach ($grup[$kunci] as $jenis => [$nama, $kertas, $halaman, , $deskripsi])
                    <li class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:gap-4 sm:px-5">
                        <div class="flex min-w-0 flex-1 items-start gap-3">
                            <span class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700"><x-icon name="document" class="size-5"/></span>
                            <div class="min-w-0">
                                <p class="font-medium text-slate-900">{{ $nama }}</p>
                                <p class="text-xs text-slate-500">{{ $deskripsi }} · {{ $kertas === 'a4' ? 'A4' : 'Letter' }}</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 gap-2 pl-12 sm:pl-0">
                            <x-button variant="secondary" size="sm" :href="route('formulir.lihat', $jenis)">Buka</x-button>
                            <x-button variant="ghost" size="sm" icon="download" :href="route('formulir.unduh', $jenis)">PDF</x-button>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-card>
    @endforeach
</div>
@endsection
