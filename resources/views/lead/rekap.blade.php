@extends('layouts.app')
@section('title', 'Rekap Lead')

@php
    $total = ['lead' => $rekap->sum('lead'), 'prospek' => $rekap->sum('prospek'), 'closing' => $rekap->sum('closing')];
    $rasio = fn ($a, $b) => $b > 0 ? angka($a / $b * 100, 0) . '%' : '—';
    $tabs = collect(['harian' => 'Harian', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan'])
        ->map(fn ($l, $m) => [$l, request()->fullUrlWithQuery(['mode' => $m]), $mode === $m, null])->values()->all();
@endphp

@section('content')
<x-page-header title="Rekap Lead" :subtitle="$judul" :back="route('lead.index')" :breadcrumbs="['Lead Harian' => route('lead.index'), 'Rekap' => null]"/>

<x-tabs :items="$tabs"/>

<div class="card mb-5">
    <form method="GET" class="flex flex-col gap-2.5 p-3 sm:flex-row sm:flex-wrap sm:items-center sm:gap-3 sm:p-4" x-data x-on:change="$el.requestSubmit()">
        <input type="hidden" name="mode" value="{{ $mode }}">
        <div class="flex items-center gap-1.5">
            <x-icon-button icon="chevron-left" label="Periode sebelumnya" :href="request()->fullUrlWithQuery(['tanggal' => $sebelum])" class="border border-slate-300 bg-white"/>
            <x-input type="date" name="tanggal" :value="$acuan->toDateString()" class="w-full sm:w-44" aria-label="Tanggal acuan"/>
            <x-icon-button icon="chevron-right" label="Periode berikutnya" :href="request()->fullUrlWithQuery(['tanggal' => $sesudah])" class="border border-slate-300 bg-white"/>
        </div>
        @unless (auth()->user()?->isAgen())
            <x-select name="agen" :options="$agens" :value="request('agen')" placeholder="Semua agen" class="sm:w-52"/>
        @endunless
        @if (request()->hasAny(['tanggal', 'agen']))
            <a href="{{ route('lead.rekap', ['mode' => $mode]) }}" class="btn btn-ghost btn-sm text-slate-500 sm:ml-auto"><x-icon name="filter-x" class="size-4"/> Hapus filter</a>
        @endif
    </form>
</div>

<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-5">
    <x-stat-card label="Lead" :value="$total['lead']" icon="users"/>
    <x-stat-card label="Prospek" :value="$total['prospek']" icon="funnel"/>
    <x-stat-card label="Closing" :value="$total['closing']" icon="check-badge" tone="utama"/>
    <x-stat-card label="Lead → Prospek" :value="$rasio($total['prospek'], $total['lead'])"/>
    <x-stat-card label="Prospek → Closing" :value="$rasio($total['closing'], $total['prospek'])"/>
</div>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-5">
    <x-card title="Per Agen" subtitle="Tiap tahap dihitung pada tanggal terjadinya." :padding="false" :class="$deret ? 'xl:col-span-3' : 'xl:col-span-5'">
        @if ($rekap->isEmpty())
            <x-empty-state title="Belum ada agen"/>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Agen</th><th class="text-right">Lead</th><th class="text-right">Prospek</th><th class="text-right">Closing</th><th class="text-right">Konversi</th></tr></thead>
                    <tbody>
                        @foreach ($rekap as $r)
                            <tr>
                                <td><a href="{{ route('agen.show', $r['agen']) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ $r['agen']->nama_agen }}</a> <span class="text-xs text-slate-500">{{ $r['agen']->kode_agen }}</span></td>
                                <td class="text-right tabular-nums">{{ $r['lead'] }}</td>
                                <td class="text-right tabular-nums">{{ $r['prospek'] }}</td>
                                <td class="text-right font-semibold tabular-nums">{{ $r['closing'] }}</td>
                                <td class="text-right text-slate-500 tabular-nums">{{ $rasio($r['closing'], $r['lead']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr><td>Total</td><td class="text-right">{{ $total['lead'] }}</td><td class="text-right">{{ $total['prospek'] }}</td><td class="text-right">{{ $total['closing'] }}</td><td class="text-right">{{ $rasio($total['closing'], $total['lead']) }}</td></tr></tfoot>
                </table>
            </div>
        @endif
    </x-card>

    @if ($deret)
        <x-card title="Per Hari" class="xl:col-span-2">
            <div class="h-72"><canvas id="grafik-lead" aria-label="Grafik lead per hari" role="img"></canvas></div>
        </x-card>
    @endif
</div>
@endsection

@if ($deret)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deret = @js($deret), w = window.warnaGrafik;
            const label = Object.keys(deret).map(d => new Date(d + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }));
            const seri = (i) => Object.values(deret).map(v => v[i]);
            new Chart(document.getElementById('grafik-lead'), {
                type: 'bar',
                data: { labels: label, datasets: [
                    { label: 'Lead', data: seri(0), backgroundColor: w.netralMuda, borderRadius: 4 },
                    { label: 'Prospek', data: seri(1), backgroundColor: w.utamaMuda, borderRadius: 4 },
                    { label: 'Closing', data: seri(2), backgroundColor: w.utama, borderRadius: 4 },
                ] },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 } } },
                },
            });
        });
    </script>
    @endpush
@endif
