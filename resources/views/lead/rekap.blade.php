@extends('layouts.app')
@section('title', 'Rekap Lead')

@php
    $total = ['lead' => $rekap->sum('lead'), 'prospek' => $rekap->sum('prospek'), 'closing' => $rekap->sum('closing')];
    $rasio = fn ($a, $b) => $b > 0 ? angka($a / $b * 100, 0) . '%' : '—';
@endphp

@section('content')
<x-page-header title="Rekap Lead, Prospek & Closing" :subtitle="$judul" :back="route('lead.index')"
               :breadcrumbs="['Lead Harian' => route('lead.index'), 'Rekap' => null]"/>

<div class="card mb-6">
    <form method="GET" class="flex flex-col gap-3 p-4 lg:flex-row lg:items-center" x-data x-on:change="$el.requestSubmit()">
        <div class="inline-flex rounded-lg bg-slate-100 p-1 text-sm">
            @foreach (['harian' => 'Harian', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan'] as $m => $l)
                <a href="{{ request()->fullUrlWithQuery(['mode' => $m]) }}" @class(['rounded-md px-4 py-1.5 font-medium', 'bg-white text-slate-900 shadow-xs' => $mode === $m, 'text-slate-500 hover:text-slate-700' => $mode !== $m])>{{ $l }}</a>
            @endforeach
        </div>
        <input type="hidden" name="mode" value="{{ $mode }}">
        <div class="flex items-center gap-2">
            <a href="{{ request()->fullUrlWithQuery(['tanggal' => $sebelum]) }}" class="btn-icon border border-slate-300 bg-white" title="Sebelumnya"><x-icon name="arrow-left" class="h-4 w-4"/></a>
            <input type="date" name="tanggal" value="{{ $acuan->toDateString() }}" class="form-input w-44">
            <a href="{{ request()->fullUrlWithQuery(['tanggal' => $sesudah]) }}" class="btn-icon border border-slate-300 bg-white" title="Berikutnya"><x-icon name="arrow-right" class="h-4 w-4"/></a>
        </div>
        <x-select name="agen" :options="$agens" :value="request('agen')" placeholder="Semua agen" class="lg:ml-auto lg:w-52"/>
    </form>
</div>

<div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-5 sm:gap-4">
    <x-stat-card label="Lead" :value="$total['lead']" icon="users"/>
    <x-stat-card label="Prospek" :value="$total['prospek']" icon="funnel"/>
    <x-stat-card label="Closing" :value="$total['closing']" icon="check-badge" tone="dark"/>
    <x-stat-card label="Lead → Prospek" :value="$rasio($total['prospek'], $total['lead'])" hint="pada periode ini"/>
    <x-stat-card label="Prospek → Closing" :value="$rasio($total['closing'], $total['prospek'])" hint="pada periode ini" tone="gold"/>
</div>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
    <x-card title="Per Agen" :padding="false" :class="$deret ? 'xl:col-span-3' : 'xl:col-span-5'">
        @if ($rekap->isEmpty())
            <x-empty-state title="Belum ada agen" message="Tambahkan agen di menu Agen & Marketing."/>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Agen</th><th class="text-right">Lead</th><th class="text-right">Prospek</th><th class="text-right">Closing</th><th class="text-right">Konversi</th></tr></thead>
                    <tbody>
                        @foreach ($rekap as $r)
                            <tr>
                                <td><a href="{{ route('agen.show', $r['agen']) }}" class="font-medium text-slate-900 hover:text-forest-700">{{ $r['agen']->nama_agen }}</a> <span class="text-xs text-slate-500">{{ $r['agen']->kode_agen }}</span></td>
                                <td class="text-right tabular-nums">{{ $r['lead'] }}</td>
                                <td class="text-right tabular-nums">{{ $r['prospek'] }}</td>
                                <td class="text-right font-semibold tabular-nums">{{ $r['closing'] }}</td>
                                <td class="text-right tabular-nums text-slate-500">{{ $rasio($r['closing'], $r['lead']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr><td>Total</td><td class="text-right">{{ $total['lead'] }}</td><td class="text-right">{{ $total['prospek'] }}</td><td class="text-right">{{ $total['closing'] }}</td><td class="text-right">{{ $rasio($total['closing'], $total['lead']) }}</td></tr></tfoot>
                </table>
            </div>
        @endif
        <p class="border-t border-slate-100 px-5 py-3 text-xs text-slate-500">Setiap tahap dihitung pada tanggal tahap itu terjadi. Satu orang bisa tercatat sebagai lead di satu hari dan prospek di hari lain.</p>
    </x-card>

    @if ($deret)
        <x-card title="Per Hari" class="xl:col-span-2">
            <div class="h-72"><canvas id="grafik-lead"></canvas></div>
        </x-card>
    @endif
</div>
@endsection

@if ($deret)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deret = @js($deret);
            const label = Object.keys(deret).map(d => new Date(d + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }));
            const seri = (i) => Object.values(deret).map(v => v[i]);
            new Chart(document.getElementById('grafik-lead'), {
                type: 'bar',
                data: { labels: label, datasets: [
                    { label: 'Lead', data: seri(0), backgroundColor: '#aed4bd', borderRadius: 3 },
                    { label: 'Prospek', data: seri(1), backgroundColor: '#e8b74d', borderRadius: 3 },
                    { label: 'Closing', data: seri(2), backgroundColor: '#1a4028', borderRadius: 3 },
                ] },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } } },
                    scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } } },
                },
            });
        });
    </script>
    @endpush
@endif
