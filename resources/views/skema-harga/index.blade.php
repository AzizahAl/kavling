@extends('layouts.app')
@section('title', 'Skema Harga')

@section('content')
<x-page-header title="Skema Harga" subtitle="Harga naik tiap kelipatan kavling terjual (PPJB)." :breadcrumbs="['Data Master' => null, 'Skema Harga' => null]">
    <x-slot:actions>
        <x-button variant="secondary" icon="cog" :href="route('proyek.index') . '#grup-harga'">Ubah di Pengaturan</x-button>
    </x-slot:actions>
</x-page-header>

<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <x-stat-card label="Harga Aktif/m²" :value="rupiah($hargaAktif)" :hint="'Tahap ' . $aktif" tone="utama" icon="tag"/>
    <x-stat-card label="Terjual" :value="$terjual . ' / ' . $totalKavling" icon="check-badge"/>
    <x-stat-card label="Menuju Kenaikan" :value="$menujuNaik !== null ? $menujuNaik . ' unit' : 'Tahap akhir'" icon="trending-up"/>
    <x-stat-card label="Harga Tertinggi/m²" :value="rupiah(collect($tahap)->max('harga_per_m2'))" icon="chart"/>
</div>

<div class="card" x-data="{ status: '' }">
    {{-- Filter sederhana di sisi browser (data tahap sedikit & tidak berhalaman) --}}
    <div class="flex flex-col gap-2.5 border-b border-slate-100 p-3 sm:flex-row sm:items-center sm:p-4">
        <h2 class="card-title flex-1">Tahap Harga</h2>
        <x-select name="filter_status" :options="['aktif' => 'Aktif', 'terlewati' => 'Terlewati', 'menunggu' => 'Menunggu']" placeholder="Semua status" x-model="status" class="sm:w-48"/>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Tahap</th><th>Unit Terjual</th><th class="text-right">Harga/m²</th><th class="text-right">Prima 98 m²</th><th class="text-right">Standard 70 m²</th><th class="text-right">Transaksi</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($tahap as $t)
                    @php $st = $t['nomor'] === $aktif ? 'aktif' : ($t['nomor'] < $aktif ? 'terlewati' : 'menunggu'); @endphp
                    <tr x-show="!status || status === '{{ $st }}'" @class(['bg-brand-50/50' => $st === 'aktif'])>
                        <td class="font-medium text-slate-900">{{ $t['nama_tahap'] }}</td>
                        <td>{{ $t['unit_mulai'] }}–{{ $t['unit_sampai'] }} unit</td>
                        <td class="text-right font-medium tabular-nums">{{ rupiah($t['harga_per_m2']) }}</td>
                        <td class="text-right text-slate-500 tabular-nums">{{ rupiah($t['harga_per_m2'] * 98) }}</td>
                        <td class="text-right text-slate-500 tabular-nums">{{ rupiah($t['harga_per_m2'] * 70) }}</td>
                        <td class="text-right tabular-nums">{{ $perTahap[$t['harga_per_m2']] ?? 0 }}</td>
                        <td>
                            @if ($st === 'aktif')<x-badge status="aktif"/>
                            @elseif ($st === 'terlewati')<x-badge status="menunggu" label="Terlewati"/>
                            @else<x-badge status="menunggu"/>@endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
