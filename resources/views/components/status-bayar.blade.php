@props(['t'])
{{-- Badge STATUS PEMBAYARAN transaksi (berbeda dari status kavling & status dokumen). --}}
@php
    $warna = ['menunggu' => 'abu', 'reservasi' => 'biru', 'booking' => 'kuning', 'dp' => 'ungu', 'angsuran' => 'indigo', 'lunas' => 'sukses', 'batal' => 'merah'][$t->status] ?? 'abu';
    $ikon = ['menunggu' => 'clock', 'lunas' => 'check', 'batal' => 'x'][$t->status] ?? null;
@endphp
<x-badge :status="$t->status" :label="$t->label_status" :warna="$warna" :ikon="$ikon" {{ $attributes }}/>
