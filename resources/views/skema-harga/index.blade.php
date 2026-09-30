@extends('layouts.app')
@section('title', 'Skema Harga')

@section('content')
<x-page-header title="Skema Harga" subtitle="Harga per m² naik bertahap mengikuti jumlah kavling terjual (PPJB ditandatangani)."
               :breadcrumbs="['Data Master' => null, 'Skema Harga' => null]">
    <x-slot:actions>
        <a href="{{ route('proyek.index') }}#grup-harga" class="btn btn-secondary"><x-icon name="cog" class="h-4 w-4"/> Ubah di Pengaturan</a>
    </x-slot:actions>
</x-page-header>

<div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 sm:gap-4">
    <x-stat-card label="Harga Aktif / m²" :value="rupiah($hargaAktif)" :hint="'Tahap ' . $aktif" tone="dark" icon="tag"/>
    <x-stat-card label="Kavling Terjual" :value="$terjual . ' / ' . $totalKavling" icon="check-badge"/>
    <x-stat-card label="Menuju Kenaikan" :value="$menujuNaik !== null ? $menujuNaik . ' unit lagi' : 'Tahap terakhir'" icon="chart"/>
    <x-stat-card label="Harga Tertinggi / m²" :value="rupiah(collect($tahap)->max('harga_per_m2'))" tone="gold"/>
</div>

<x-card title="Tahap Harga" subtitle="Rumus: harga awal + (unit terjual ÷ unit per kenaikan, dibulatkan ke bawah) × kenaikan" :padding="false">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Tahap</th><th>Rentang Unit Terjual</th><th class="text-right">Harga / m²</th><th class="text-right">Prima 98 m²</th><th class="text-right">Standard 70 m²</th><th class="text-right">Transaksi Terkunci</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($tahap as $t)
                    <tr @class(['bg-forest-50/60' => $t['nomor'] === $aktif])>
                        <td class="font-semibold">{{ $t['nama_tahap'] }}</td>
                        <td>{{ $t['unit_mulai'] }} – {{ $t['unit_sampai'] }} unit</td>
                        <td class="text-right font-medium tabular-nums">{{ rupiah($t['harga_per_m2']) }}</td>
                        <td class="text-right tabular-nums text-slate-500">{{ rupiah($t['harga_per_m2'] * 98) }}</td>
                        <td class="text-right tabular-nums text-slate-500">{{ rupiah($t['harga_per_m2'] * 70) }}</td>
                        <td class="text-right tabular-nums">{{ $perTahap[$t['harga_per_m2']] ?? 0 }}</td>
                        <td>
                            @if ($t['nomor'] === $aktif)<x-badge status="aktif"/>
                            @elseif ($t['nomor'] < $aktif)<x-badge status="selesai" label="Terlewati"/>
                            @else<x-badge status="menunggu"/>@endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-card>
@endsection
