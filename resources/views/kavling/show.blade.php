@extends('layouts.app')

@section('content')
<div class="p-6 max-w-2xl">
    <p class="text-sm text-gray-500 mb-1">Data Master &gt; Master Kavling &gt; <span class="text-gray-800 font-medium">Detail Kavling</span></p>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Detail Kavling {{ $kavling->kode_kavling }}</h1>

    <div class="bg-white border rounded-xl p-6 divide-y text-sm">
        <div class="py-3 flex justify-between"><span class="text-gray-500">Kode Kavling</span><span class="font-medium">{{ $kavling->kode_kavling }}</span></div>
        <div class="py-3 flex justify-between"><span class="text-gray-500">Blok</span><span class="font-medium">{{ $kavling->blok }}</span></div>
        <div class="py-3 flex justify-between"><span class="text-gray-500">No</span><span class="font-medium">{{ $kavling->no }}</span></div>
        <div class="py-3 flex justify-between"><span class="text-gray-500">Tipe</span><span class="font-medium">{{ $kavling->tipe }}</span></div>
        <div class="py-3 flex justify-between"><span class="text-gray-500">Ukuran</span><span class="font-medium">{{ $kavling->ukuran }}</span></div>
        <div class="py-3 flex justify-between"><span class="text-gray-500">Luas</span><span class="font-medium">{{ $kavling->luas }} m²</span></div>
        <div class="py-3 flex justify-between"><span class="text-gray-500">Harga/m²</span><span class="font-medium">Rp{{ number_format($kavling->harga_per_m2, 0, ',', '.') }}</span></div>
        <div class="py-3 flex justify-between"><span class="text-gray-500">Harga Jual</span><span class="font-medium">Rp{{ number_format($kavling->harga_jual, 0, ',', '.') }}</span></div>
        <div class="py-3 flex justify-between"><span class="text-gray-500">Status</span><span class="font-medium">{{ ucfirst($kavling->status) }}</span></div>
    </div>

    <a href="{{ route('kavling.index') }}" class="inline-block mt-6 text-sm text-gray-700 border px-4 py-2 rounded-lg hover:bg-gray-50">
        ← Kembali ke Master Kavling
    </a>
</div>
@endsection