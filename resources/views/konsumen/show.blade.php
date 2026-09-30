@extends('layouts.app')

@section('content')
<div class="p-6">

    {{-- Breadcrumb + tombol kembali --}}
    <div class="flex items-start justify-between mb-1">
        <div>
            <p class="text-sm text-gray-500 mb-1">
                Penjualan &gt; <a href="{{ route('konsumen.index') }}" class="hover:underline">Data Konsumen</a> &gt;
                <span class="text-gray-800 font-medium">Detail Konsumen</span>
            </p>
            <h1 class="text-2xl font-bold text-gray-900">Detail Konsumen</h1>
        </div>
        <a href="{{ route('konsumen.index') }}"
           class="border rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Kembali ke Data Konsumen
        </a>
    </div>
    <p class="text-gray-500 mb-6">Kelola dan pantau informasi lengkap, riwayat pembayaran, serta status dokumen konsumen.</p>

    @php
        $rp = fn($n) => 'Rp' . number_format($n, 0, ',', '.');

        // Luas tanah tanpa desimal berlebih: 98.00 -> 98 | 98.50 -> 98,5
        $luasRaw   = $konsumen->kavling->luas ?? null;
        $luasTampil = $luasRaw !== null && $luasRaw !== ''
            ? rtrim(rtrim(number_format((float) $luasRaw, 2, ',', '.'), '0'), ',')
            : '-';

        $stages    = $summary['stages'];
        $hargaNett = $summary['harga_jual_nett'];

        $stageBar = [
            'selesai' => 'bg-green-500',
            'proses'  => 'bg-amber-400',
            'belum'   => 'bg-gray-200',
        ];
        $stageBadge = [
            'selesai' => 'bg-green-100 text-green-700',
            'proses'  => 'bg-yellow-100 text-yellow-700',
            'belum'   => 'bg-gray-100 text-gray-500',
        ];
        $stageLabel = ['selesai' => 'Selesai', 'proses' => 'Proses', 'belum' => 'Belum'];

        // Status per komponen diambil dari perhitungan bertahap di controller
        $stageByKey = collect($stages)->keyBy('key');
        $statusKomponen = fn($key) => $stageByKey[$key]['status'] ?? 'belum';
    @endphp

    {{-- Informasi Konsumen + Kavling --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-1">
                <h2 class="text-lg font-bold text-gray-900">{{ $konsumen->nama_lengkap }}</h2>
                <span class="bg-gray-100 text-gray-600 text-xs font-medium px-3 py-1 rounded-full">ID: {{ $konsumen->id_konsumen }}</span>
            </div>
            @if($konsumen->label)
                <p class="text-sm text-gray-500 mb-4">{{ $konsumen->label }}</p>
            @else
                <div class="mb-4"></div>
            @endif

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">NIK</p>
                    <p class="text-gray-800">{{ $konsumen->nik }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Telepon</p>
                    <p class="text-gray-800">{{ $konsumen->no_hp }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-xs text-gray-400 uppercase mb-1">Alamat</p>
                    <p class="text-gray-800">{{ $konsumen->alamat }}</p>
                </div>
            </div>

            <div class="border-t mt-4 pt-4 text-sm text-gray-600 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Agen Marketing: <span class="font-medium text-gray-800">{{ $konsumen->agen->nama_agen ?? '-' }}</span>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Informasi Kavling</h2>
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Kode Kavling</p>
                    <p class="text-gray-800 font-semibold">{{ $konsumen->kavling->kode_kavling ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Tipe</p>
                    <p class="text-gray-800 font-semibold">{{ $konsumen->kavling->tipe ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Skema Bayar</p>
                    <p class="text-gray-800 font-semibold">{{ ['cash_lunas' => 'Cash / Lunas', 'angsuran' => 'Angsuran'][$konsumen->skema_bayar] ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Luas Tanah</p>
                    <p class="text-gray-800 font-semibold">{{ $luasTampil }}@if($luasTampil !== '-') m&sup2;@endif</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Harga/m&sup2;</p>
                    <p class="text-gray-800 font-semibold">{{ $rp($konsumen->kavling->harga_per_m2 ?? 0) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Tanggal Transaksi</p>
                    <p class="text-gray-800 font-semibold">{{ $konsumen->tanggal_transaksi->format('d M Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan Harga --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4 min-h-[96px] flex flex-col justify-between">
            <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide whitespace-nowrap">Harga Jual Nett</p>
            <p class="text-2xl font-bold text-gray-900 leading-none mt-3">{{ $rp($summary['harga_jual_nett']) }}</p>
        </div>
        <div class="bg-green-50 rounded-xl border border-green-100 shadow-sm px-5 py-4 min-h-[96px] flex flex-col justify-between">
            <p class="text-xs text-green-700 uppercase font-semibold tracking-wide whitespace-nowrap">Total Terbayar</p>
            <p class="text-2xl font-bold text-green-700 leading-none mt-3">{{ $rp($summary['total_terbayar']) }}</p>
        </div>
        <div class="bg-red-50 rounded-xl border border-red-100 shadow-sm px-5 py-4 min-h-[96px] flex flex-col justify-between">
            <p class="text-xs text-red-600 uppercase font-semibold tracking-wide whitespace-nowrap">Sisa Tagihan</p>
            <p class="text-2xl font-bold text-red-600 leading-none mt-3">{{ $rp($summary['sisa_tagihan']) }}</p>
        </div>
    </div>

    {{-- Progress Pembayaran per Tahap --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Progress Pembayaran</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">
                    {{ $rp($summary['total_terbayar']) }}
                    <span class="text-sm font-normal text-gray-500">dari {{ $rp($hargaNett) }} (Harga Jual Nett)</span>
                </p>
            </div>
            <p class="text-2xl font-bold text-amber-500">{{ $summary['progress_percent'] }}%</p>
        </div>

        {{-- Bar bertahap: lebar tiap segmen sebanding dengan targetnya terhadap Harga Jual Nett --}}
        <div class="flex gap-1 mt-4 h-3">
            @foreach($stages as $s)
                <div class="h-full rounded-full bg-gray-100 overflow-hidden"
                     style="width: {{ $hargaNett > 0 ? ($s['target'] / $hargaNett) * 100 : 0 }}%; min-width: 6px;"
                     title="{{ $s['nama'] }}: {{ $rp($s['terbayar']) }} / {{ $rp($s['target']) }}">
                    <div class="h-full {{ $stageBar[$s['status']] }}" style="width: {{ $s['persen'] }}%"></div>
                </div>
            @endforeach
        </div>

        {{-- Detail tiap tahap --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mt-5">
            @foreach($stages as $i => $s)
                <div class="rounded-lg border px-4 py-3 {{ $s['aktif'] ? 'border-amber-300 bg-amber-50/40' : 'border-gray-200' }}">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-semibold text-gray-900">{{ $i + 1 }}. {{ $s['nama'] }}</p>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $stageBadge[$s['status']] }}">
                            {{ $stageLabel[$s['status']] }}
                        </span>
                    </div>

                    <p class="mt-2 text-lg font-bold text-gray-900">{{ $rp($s['terbayar']) }}</p>
                    <p class="text-xs text-gray-500">dari target {{ $rp($s['target']) }}</p>

                    <div class="mt-2 h-1.5 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full {{ $stageBar[$s['status']] }}" style="width: {{ $s['persen'] }}%"></div>
                    </div>

                    <p class="mt-2 text-xs {{ $s['aktif'] ? 'text-amber-600 font-medium' : 'text-gray-400' }}">
                        @if($s['status'] === 'selesai')
                            Lunas
                        @elseif($s['aktif'])
                            Tahap berjalan · sisa {{ $rp($s['sisa']) }}
                        @else
                            Menunggu tahap sebelumnya
                        @endif
                    </p>
                </div>
            @endforeach
        </div>

        <p class="mt-4 text-xs text-gray-400">
            Total semua tahap = {{ $rp(collect($stages)->sum('target')) }} (sama dengan Harga Jual Nett).
        </p>
    </div>

    {{-- Rincian Komponen + Riwayat Pembayaran --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-6 py-5 border-b">
                <h2 class="text-lg font-bold text-gray-900">Rincian Komponen</h2>
            </div>
            <table class="w-full text-sm">
                <thead class="text-gray-400 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">Komponen</th>
                        <th class="px-6 py-3 text-right">Nominal Target</th>
                        <th class="px-6 py-3 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr>
                        <td class="px-6 py-4 text-gray-700">Reservasi (NUP)</td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">{{ $rp($summary['target_reservasi']) }}</td>
                        <td class="px-6 py-4 text-right">
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $stageBadge[$statusKomponen('reservasi')] }}">{{ $stageLabel[$statusKomponen('reservasi')] }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 text-gray-700">Booking Fee</td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">{{ $rp($summary['target_booking']) }}</td>
                        <td class="px-6 py-4 text-right">
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $stageBadge[$statusKomponen('booking')] }}">{{ $stageLabel[$statusKomponen('booking')] }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 text-gray-700">Down Payment (DP)</td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">{{ $rp($summary['target_dp']) }}</td>
                        <td class="px-6 py-4 text-right">
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $stageBadge[$statusKomponen('dp')] }}">{{ $stageLabel[$statusKomponen('dp')] }}</span>
                        </td>
                    </tr>
                    @if($stageByKey->has('pelunasan'))
                        <tr>
                            <td class="px-6 py-4 text-gray-700">
                                @if($konsumen->jumlah_angsuran)
                                    Angsuran ({{ $konsumen->jumlah_angsuran }}x)
                                @else
                                    Pelunasan
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                {{ $rp($stageByKey['pelunasan']['target']) }}
                                @if($konsumen->jumlah_angsuran)
                                    <p class="text-xs text-gray-400">{{ $rp($summary['angsuran_per_bulan']) }} / bln</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $stageBadge[$statusKomponen('pelunasan')] }}">{{ $stageLabel[$statusKomponen('pelunasan')] }}</span>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-6 py-5 border-b">
                <h2 class="text-lg font-bold text-gray-900">Riwayat Pembayaran</h2>
            </div>
            <table class="w-full text-sm">
                <thead class="text-gray-400 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">Tanggal</th>
                        <th class="px-6 py-3 text-left">Keterangan</th>
                        <th class="px-6 py-3 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($konsumen->riwayatPembayarans as $riwayat)
                        <tr>
                            <td class="px-6 py-4 text-gray-500 whitespace-nowrap">{{ $riwayat->tanggal->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $riwayat->keterangan }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <span class="font-medium whitespace-nowrap">{{ $rp($riwayat->nominal) }}</span>
                                    <a href="{{ route('konsumen.kwitansi', [$konsumen, $riwayat]) }}" title="Unduh Kwitansi (Word)" class="text-gray-400 hover:text-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-400">Belum ada riwayat pembayaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Status Dokumen Legal & Administrasi --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-bold text-gray-900">Status Dokumen Legal & Administrasi</h2>
        <p class="text-sm text-gray-500 mb-8">Pantau pergerakan berkas administrasi kavling.</p>

        @php
            $steps = [
                [
                    'label'    => 'Reservasi',
                    'status'   => $konsumen->status_reservasi,
                    'tanggal'  => $konsumen->status_reservasi !== 'belum' ? $konsumen->tanggal_transaksi->format('d M Y') : null,
                ],
                [
                    'label'    => 'Booking / SPK',
                    'status'   => $konsumen->status_booking,
                    'tanggal'  => null,
                ],
                [
                    'label'    => 'PPJB',
                    'status'   => $konsumen->status_ppjb,
                    'syarat'   => 'Syarat: DP Lunas',
                ],
                [
                    'label'    => 'AJB / Legal',
                    'status'   => $konsumen->status_ajb,
                    'syarat'   => 'Syarat: Lunas Total',
                ],
            ];

            $docLabel = fn($s) => match($s) {
                'selesai' => 'Selesai',
                'proses'  => 'Menunggu Berkas',
                default   => 'Terkunci',
            };
            $docBadge = fn($s) => match($s) {
                'selesai' => 'bg-green-100 text-green-700',
                'proses'  => 'bg-yellow-100 text-yellow-700',
                default   => 'bg-gray-100 text-gray-500',
            };
            $iconBg = fn($s) => match($s) {
                'selesai' => 'bg-green-500 text-white',
                'proses'  => 'bg-amber-400 text-white',
                default   => 'bg-gray-200 text-gray-400',
            };
        @endphp

        <div class="flex items-start">
            @foreach($steps as $i => $step)
                <div class="flex-1 flex flex-col items-center text-center relative">
                    @if($i > 0)
                        <div class="absolute top-5 -left-1/2 w-full h-0.5 bg-gray-200 -z-10"></div>
                    @endif

                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $iconBg($step['status']) }}">
                        @if($step['status'] === 'selesai')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @elseif($step['status'] === 'proses')
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 12h6"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        @endif
                    </div>

                    <p class="font-semibold text-gray-800 mt-3">{{ $step['label'] }}</p>
                    <span class="px-3 py-1 rounded-full text-xs font-medium mt-2 {{ $docBadge($step['status']) }}">
                        {{ $docLabel($step['status']) }}
                    </span>

                    <p class="text-xs text-gray-400 mt-2">
                        {{ $step['tanggal'] ?? $step['syarat'] ?? '-' }}
                    </p>

                    @if($step['label'] === 'Reservasi' && $step['status'] === 'selesai')
                        <a href="{{ $konsumen->riwayatPembayarans->first() ? route('konsumen.kwitansi', [$konsumen, $konsumen->riwayatPembayarans->first()]) : '#' }}"
                           class="mt-3 border rounded-lg px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Kwitansi
                        </a>
                    @endif

                    @if($step['label'] === 'Booking / SPK' && $step['status'] === 'proses')
                        <a href="{{ route('konsumen.buat-spk', $konsumen) }}"
                           class="mt-3 bg-gray-900 text-white rounded-lg px-3 py-1.5 text-xs font-medium hover:bg-gray-800">
                            Buat SPK
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection