@extends('layouts.app')

@section('content')
<div x-data="transaksiPage()" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Transaksi Penjualan</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola transaksi penjualan kavling, pembayaran konsumen, dan status pelunasan.</p>
        </div>
        <button @click="openModal()"
            class="flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors duration-150">
            <span class="text-lg leading-none">+</span> Tambah Transaksi
        </button>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach ([
            ['label' => 'Total Transaksi', 'value' => $stats['total']],
            ['label' => 'Reservasi', 'value' => $stats['reservasi']],
            ['label' => 'Booking', 'value' => $stats['booking']],
            ['label' => 'DP', 'value' => $stats['dp']],
        ] as $card)
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ $card['label'] }}</p>
                <p class="text-2xl font-semibold text-slate-800 mt-1">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Nilai Jual</p>
            <p class="text-2xl font-semibold text-slate-800 mt-1">Rp{{ number_format($stats['total_nilai_jual'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Bayar</p>
            <p class="text-2xl font-semibold text-emerald-600 mt-1">Rp{{ number_format($stats['total_bayar'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Sisa</p>
            <p class="text-2xl font-semibold text-red-500 mt-1">Rp{{ number_format($stats['total_sisa'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Transaksi Lunas</p>
            <p class="text-2xl font-semibold text-slate-800 mt-1">{{ $stats['lunas'] }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-xl border border-slate-200 p-5 mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="text-xs font-medium text-slate-500">Cari Transaksi</label>
            <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari ID transaksi..."
                class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-800 focus:border-slate-800">
        </div>
        <div>
            <label class="text-xs font-medium text-slate-500">Status</label>
            <select name="status" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                <option value="semua">Semua</option>
                <option value="reservasi" @selected(request('status')=='reservasi')>Reservasi</option>
                <option value="booking" @selected(request('status')=='booking')>Booking</option>
                <option value="dp" @selected(request('status')=='dp')>DP</option>
                <option value="lunas" @selected(request('status')=='lunas')>Lunas</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-medium text-slate-500">Jenis</label>
            <select name="jenis" class="mt-1 w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                <option value="semua">Semua</option>
                <option value="cash" @selected(request('jenis')=='cash')>Cash</option>
                <option value="angsuran" @selected(request('jenis')=='angsuran')>Angsuran</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-medium text-slate-500">Periode</label>
            <div class="flex gap-2 mt-1">
                <input type="date" name="periode" value="{{ request('periode') }}"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                <a href="{{ route('transaksi-penjualan.index') }}"
                    class="flex items-center gap-1 border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">
                    Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-500 uppercase border-b border-slate-200">
                    <th class="px-4 py-3">ID Transaksi</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">ID Konsumen</th>
                    <th class="px-4 py-3">Nama Konsumen</th>
                    <th class="px-4 py-3">Kavling</th>
                    <th class="px-4 py-3">Agen</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3 text-right">Nilai Jual</th>
                    <th class="px-4 py-3 text-right">Sisa</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transaksis as $t)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $t->kode_transaksi }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $t->tanggal->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $t->konsumen->kode_konsumen ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $t->konsumen->nama ?? '-' }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $t->kavling->kode_kavling ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $t->agen->nama_agen ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $badge = [
                                    'reservasi' => 'bg-amber-100 text-amber-700',
                                    'booking' => 'bg-orange-100 text-orange-700',
                                    'dp' => 'bg-blue-100 text-blue-700',
                                    'lunas' => 'bg-green-100 text-green-700',
                                ][$t->status];
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">{{ strtoupper($t->status) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($t->jenis_pembayaran === 'cash')
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">CASH</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-slate-200 text-slate-700">ANGSURAN</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-slate-800">Rp{{ number_format($t->nilai_jual, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right {{ $t->sisa_pembayaran > 0 ? 'text-red-500' : 'text-green-600' }}">
                            Rp{{ number_format($t->sisa_pembayaran, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-4 py-8 text-center text-slate-400">Belum ada transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $transaksis->links() }}</div>

    {{-- Modal popup --}}
    @include('transaksi-penjualan._modal-form')
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/transaksi-penjualan.js') }}"></script>
    <script>
        const KAVLINGS = @json($kavlings);
        const AGENS = @json($agens);
        const KODE_BARU = @json($kodeBaru);
        const URL_CARI_KONSUMEN = @json(route('transaksi-penjualan.cari-konsumen'));
        const URL_STORE = @json(route('transaksi-penjualan.store'));
    </script>
@endpush