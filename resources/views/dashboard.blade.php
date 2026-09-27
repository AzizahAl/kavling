@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    {{-- HEADER HALAMAN --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
        <p class="text-slate-500 mt-1">Monitoring keseluruhan proyek, kavling, penjualan, agen, konsumen, dan keuangan Tectona Residen.</p>
    </div>

    {{-- ================= ROW 1 : STAT CARDS KAVLING ================= --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-4">
        @php
            $statKavling = [
                ['label' => 'TOTAL KAVLING', 'value' => $stats['total_kavling'], 'icon' => '▦', 'color' => 'text-slate-900'],
                ['label' => 'TERSEDIA', 'value' => $stats['tersedia'], 'icon' => '✅', 'color' => 'text-green-600'],
                ['label' => 'RESERVASI', 'value' => $stats['reservasi'], 'icon' => '📋', 'color' => 'text-slate-900'],
                ['label' => 'BOOKING', 'value' => $stats['booking'], 'icon' => '🔖', 'color' => 'text-slate-900'],
                ['label' => 'DP', 'value' => $stats['dp'], 'icon' => '💳', 'color' => 'text-slate-900'],
                ['label' => 'TERJUAL', 'value' => $stats['terjual'], 'icon' => '✔️', 'color' => 'text-slate-900'],
            ];
        @endphp

        @foreach ($statKavling as $item)
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
                <div class="flex items-center justify-between text-[11px] font-semibold tracking-wide text-slate-400">
                    <span>{{ $item['label'] }}</span>
                    <span>{{ $item['icon'] }}</span>
                </div>
                <p class="text-2xl font-bold mt-2 {{ $item['color'] }}">{{ $item['value'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- ================= ROW 2 : HARGA / PENJUALAN / PEMBAYARAN ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <p class="text-[11px] font-semibold tracking-wide text-slate-400">HARGA AKTIF/M²</p>
            <p class="text-2xl font-bold mt-2">Rp {{ number_format($stats['harga_aktif'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <p class="text-[11px] font-semibold tracking-wide text-slate-400">TOTAL PENJUALAN</p>
            <p class="text-2xl font-bold mt-2">Rp {{ number_format($stats['total_penjualan'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <p class="text-[11px] font-semibold tracking-wide text-slate-400">PEMBAYARAN MASUK</p>
            <p class="text-2xl font-bold mt-2">Rp {{ number_format($stats['pembayaran_masuk'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- ================= ROW 3 : PIUTANG / SALDO KAS / REALISASI RAB ================= --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <p class="text-[11px] font-semibold tracking-wide text-slate-400">PIUTANG KONSUMEN</p>
            <p class="text-2xl font-bold mt-2">Rp {{ number_format($stats['piutang'], 0, ',', '.') }}</p>
        </div>

        <div class="bg-red-50 rounded-xl border border-red-100 p-4 relative overflow-hidden">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>
            <p class="text-[11px] font-semibold tracking-wide text-red-500 flex items-center gap-1">⚠ SALDO KAS PROYEK</p>
            <p class="text-2xl font-bold mt-2 text-red-600">
                {{ $stats['saldo_kas'] < 0 ? '−' : '' }}Rp {{ number_format(abs($stats['saldo_kas']), 0, ',', '.') }}
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <p class="text-[11px] font-semibold tracking-wide text-slate-400">REALISASI RAB</p>
            <p class="text-2xl font-bold mt-2">Rp {{ number_format($stats['realisasi_rab'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- ================= ROW 4 : STATUS KAVLING (DONUT) + BASELINE PROYEK ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        {{-- Status Kavling Keseluruhan --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="font-semibold text-slate-800 mb-4">Status Kavling Keseluruhan</h2>
            <div class="flex flex-col md:flex-row items-center gap-8">
                <div class="relative w-48 h-48 shrink-0">
                    <canvas id="chartStatusKavling"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-3xl font-bold">{{ $stats['tersedia'] }}</span>
                        <span class="text-xs text-slate-400">Tersedia</span>
                    </div>
                </div>

                <div class="flex-1 w-full space-y-3">
                    @foreach ($statusKavling as $s)
                        <div class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $s['color'] }}"></span>
                                {{ $s['label'] }}
                            </span>
                            <span class="text-slate-500">{{ $s['value'] }} ({{ $s['percent'] }}%)</span>
                        </div>
                    @endforeach

                    <div class="pt-2">
                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-green-600" style="width: {{ $stats['tersedia_percent'] }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-slate-400 mt-1">
                            <span>0</span>
                            <span>{{ $stats['total_kavling'] }} Kavling</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Baseline Proyek --}}
        <div class="bg-forest-900 text-white rounded-xl p-6">
            <h2 class="font-semibold mb-4 flex items-center gap-2">📖 Baseline Proyek</h2>
            <div class="space-y-3 text-sm">
                @foreach ($baseline as $row)
                    <div class="flex items-center justify-between border-b border-white/10 pb-3">
                        <span class="text-slate-300">{{ $row['label'] }}</span>
                        @if(!empty($row['badge']))
                            <span class="bg-green-600/80 text-xs font-semibold px-2 py-1 rounded-full">{{ $row['value'] }}</span>
                        @else
                            <span class="font-semibold">{{ $row['value'] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ================= ROW 5 : TRANSAKSI TERBARU + RINGKASAN KEUANGAN ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        {{-- Transaksi Terbaru --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="font-semibold text-slate-800 mb-4">Transaksi Terbaru</h2>
            <div class="divide-y divide-slate-100">
                @foreach ($transaksiTerbaru as $trx)
                    <div class="flex items-center justify-between py-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ $trx['kode'] }}</p>
                            <p class="text-xs text-slate-400">{{ $trx['nama'] }}</p>
                        </div>
                        <div class="text-sm text-slate-600">Kav. {{ $trx['kavling'] }}</div>
                        <div class="text-sm font-semibold">Rp {{ number_format($trx['nominal'], 0, ',', '.') }}</div>
                        <span @class([
                            'text-xs font-semibold px-3 py-1 rounded-full',
                            'bg-green-700 text-white' => $trx['status'] === 'DP 10%',
                            'bg-slate-700 text-white' => $trx['status'] === 'Booking',
                            'bg-amber-100 text-amber-700' => $trx['status'] === 'Reservasi',
                        ])>{{ $trx['status'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Ringkasan Keuangan Proyek --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="font-semibold text-slate-800 mb-4">Ringkasan Keuangan Proyek</h2>
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-xs font-semibold text-green-700">TOTAL PEMASUKAN</p>
                    <p class="text-xl font-bold text-green-800 mt-1">Rp {{ number_format($stats['total_pemasukan'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-red-50 rounded-lg p-4">
                    <p class="text-xs font-semibold text-red-600">TOTAL PENGELUARAN</p>
                    <p class="text-xl font-bold text-red-700 mt-1">Rp {{ number_format($stats['total_pengeluaran'], 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="flex items-center justify-between text-sm mb-2">
                <span class="text-slate-500">Realisasi RAB Lahan</span>
                <span class="font-semibold">{{ $stats['realisasi_rab_percent'] }}%</span>
            </div>
            <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden mb-1">
                <div class="h-full bg-red-500" style="width: {{ $stats['realisasi_rab_percent'] }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-slate-400 mb-6">
                <span>Realisasi: Rp {{ number_format($stats['realisasi_rab'], 0, ',', '.') }}</span>
                <span>Target RAB: Rp {{ number_format($stats['target_rab'], 0, ',', '.') }}</span>
            </div>

            <div class="h-40">
                <canvas id="chartInOut"></canvas>
            </div>
        </div>
    </div>

    {{-- ================= ROW 6 : PERFORMA PENJUALAN & AGEN ================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-slate-800">Performa Penjualan &amp; Agen</h2>
            <a href="{{ route('agen.index') }}" class="text-sm font-semibold text-green-700 hover:underline">Lihat Detail →</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="h-72">
                <canvas id="chartPenjualan"></canvas>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[11px] text-slate-400 tracking-wide">
                            <th class="pb-2">ID AGEN</th>
                            <th class="pb-2">NAMA AGEN</th>
                            <th class="pb-2 text-right">NILAI CLOSING</th>
                            <th class="pb-2 text-right">FUNNEL (L/P/C)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($agenTop as $agen)
                            <tr>
                                <td class="py-3 text-slate-500">{{ $agen['id'] }}</td>
                                <td class="py-3 font-medium">
                                    {{ $agen['nama'] }}
                                    @if($agen['top'] ?? false) <span class="text-amber-400">★</span> @endif
                                </td>
                                <td class="py-3 text-right font-semibold">{{ $agen['nilai_closing'] }}</td>
                                <td class="py-3 text-right text-slate-500">{{ $agen['funnel'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= ROW 7 : HARGA PER M² + SKEMA ALOKASI KAS + PROFIT SHARING ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Harga per m2 --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h2 class="font-semibold text-slate-800 mb-4">Harga per m² (Tahap)</h2>
            <div class="h-72">
                <canvas id="chartHarga"></canvas>
            </div>
        </div>

        <div class="space-y-4">
            {{-- Skema Alokasi Kas --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h2 class="font-semibold text-slate-800 mb-4">Skema Alokasi Kas</h2>
                <div class="flex items-center gap-4">
                    <div class="w-28 h-28 shrink-0">
                        <canvas id="chartAlokasiKas"></canvas>
                    </div>
                    <div class="text-xs space-y-1.5">
                        @foreach ($alokasiKas as $a)
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full" style="background: {{ $a['color'] }}"></span>
                                {{ $a['label'] }} ({{ $a['percent'] }}%)
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Skema Profit Sharing --}}
            <div class="bg-indigo-50 rounded-xl border border-indigo-100 p-6">
                <h2 class="font-semibold text-slate-800 mb-4">Skema Profit Sharing</h2>
                <div class="flex justify-between text-sm font-semibold mb-2">
                    <span>Pengelola ({{ $profitSharing['pengelola'] }}%)</span>
                    <span>Paman ({{ $profitSharing['mitra'] }}%)</span>
                </div>
                <div class="w-full h-2 rounded-full bg-slate-200 overflow-hidden flex">
                    <div class="h-full bg-forest-900" style="width: {{ $profitSharing['pengelola'] }}%"></div>
                    <div class="h-full bg-green-300" style="width: {{ $profitSharing['mitra'] }}%"></div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    const statusKavlingData = @json($statusKavling);
    const inOutData         = @json($inOutChart);
    const penjualanData     = @json($penjualanChart);
    const hargaData         = @json($hargaChart);
    const alokasiKasData    = @json($alokasiKas);

    new Chart(document.getElementById('chartStatusKavling'), {
        type: 'doughnut',
        data: {
            labels: statusKavlingData.map(s => s.label),
            datasets: [{
                data: statusKavlingData.map(s => s.value),
                backgroundColor: statusKavlingData.map(s => s.color),
                borderWidth: 0,
            }]
        },
        options: {
            cutout: '75%',
            plugins: { legend: { display: false }, tooltip: { enabled: true } }
        }
    });

    new Chart(document.getElementById('chartInOut'), {
        type: 'bar',
        data: {
            labels: ['In', 'Out'],
            datasets: [{
                data: [inOutData.in, inOutData.out],
                backgroundColor: ['#0f2418', '#dc2626'],
                borderRadius: 6,
                barThickness: 60,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { display: false }, x: { grid: { display: false } } }
        }
    });

    new Chart(document.getElementById('chartPenjualan'), {
        type: 'bar',
        data: {
            labels: penjualanData.labels,
            datasets: [{
                data: penjualanData.values,
                backgroundColor: '#0f2418',
                borderRadius: 6,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    new Chart(document.getElementById('chartHarga'), {
        type: 'line',
        data: {
            labels: hargaData.labels,
            datasets: [{
                data: hargaData.values,
                borderColor: '#166534',
                backgroundColor: 'rgba(22,101,52,0.1)',
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#166534',
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { ticks: { callback: v => v.toLocaleString('id-ID') } } }
        }
    });

    new Chart(document.getElementById('chartAlokasiKas'), {
        type: 'doughnut',
        data: {
            labels: alokasiKasData.map(a => a.label),
            datasets: [{
                data: alokasiKasData.map(a => a.percent),
                backgroundColor: alokasiKasData.map(a => a.color),
                borderWidth: 0,
            }]
        },
        options: {
            cutout: '65%',
            plugins: { legend: { display: false } }
        }
    });
</script>
@endpush