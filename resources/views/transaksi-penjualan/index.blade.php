@extends('layouts.app')
@section('title', 'Transaksi Penjualan')

@section('content')
<x-page-header title="Transaksi Penjualan" subtitle="Total bayar & sisa dihitung otomatis dari pembayaran."
               :breadcrumbs="['Penjualan' => null, 'Transaksi Penjualan' => null]">
    <x-slot:actions>
        <a href="{{ route('transaksi-penjualan.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4"/> Transaksi Baru</a>
    </x-slot:actions>
</x-page-header>

<div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 sm:gap-4">
    <x-stat-card label="Transaksi Aktif" :value="$stats['aktif']" :hint="($stats['per_status']['lunas'] ?? 0) . ' lunas · ' . ($stats['per_status']['batal'] ?? 0) . ' batal'" tone="dark" icon="cart"/>
    <x-stat-card label="Nilai Penjualan" :value="rupiah_singkat($stats['nilai_jual'])" :hint="rupiah($stats['nilai_jual'])" icon="tag"/>
    <x-stat-card label="Pokok Terbayar" :value="rupiah_singkat($stats['terbayar'])" :hint="rupiah($stats['terbayar'])" icon="check-circle"/>
    <x-stat-card label="Piutang" :value="rupiah_singkat($stats['piutang'])" :hint="rupiah($stats['piutang'])" tone="gold" icon="wallet"/>
</div>

<div class="mb-4 flex gap-2 overflow-x-auto pb-1">
    @php $semua = collect(\App\Models\TransaksiPenjualan::STATUS); @endphp
    <a href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}" @class(['btn btn-sm', 'btn-primary' => ! request('status'), 'btn-secondary' => request('status')])>Semua</a>
    @foreach ($semua as $s)
        <a href="{{ request()->fullUrlWithQuery(['status' => $s, 'page' => null]) }}" @class(['btn btn-sm', 'btn-primary' => request('status') === $s, 'btn-secondary' => request('status') !== $s])>
            {{ \App\Support\Status::label($s) }} <span class="opacity-60">{{ $stats['per_status'][$s] ?? 0 }}</span>
        </a>
    @endforeach
</div>

<div class="card">
    <x-filter-bar placeholder="Cari ID, konsumen, atau kavling…">
        @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
        <x-select name="jenis" :options="['cash' => 'Cash', 'angsuran' => 'Angsuran']" :value="request('jenis')" placeholder="Semua metode" class="sm:w-40"/>
        <x-select name="agen" :options="$agens" :value="request('agen')" placeholder="Semua agen" class="sm:w-44"/>
        <input type="month" name="bulan" value="{{ request('bulan') }}" class="form-input col-span-2 sm:w-44" title="Bulan transaksi">
    </x-filter-bar>

    @if ($transaksis->isEmpty())
        <x-empty-state title="Belum ada transaksi" message="{{ request()->query() ? 'Tidak ada transaksi yang cocok dengan filter.' : 'Mulai dengan membuat transaksi penjualan pertama.' }}">
            <a href="{{ route('transaksi-penjualan.create') }}" class="btn btn-primary"><x-icon name="plus" class="h-4 w-4"/> Transaksi Baru</a>
        </x-empty-state>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>ID / Tanggal</th><th>Konsumen</th><th>Kavling</th><th>Metode</th><th class="text-right">Harga Jual</th><th class="text-right">Terbayar</th><th class="text-right">Sisa</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach ($transaksis as $t)
                        <tr>
                            <td>
                                <a href="{{ route('transaksi-penjualan.show', $t) }}" class="font-semibold text-forest-700 hover:underline">{{ $t->kode_transaksi }}</a>
                                <div class="text-xs text-slate-500">{{ tanggal($t->tanggal) }}</div>
                            </td>
                            <td>
                                <span class="font-medium text-slate-900">{{ $t->konsumen->nama_lengkap }}</span>
                                <div class="text-xs text-slate-500">{{ $t->agen ? 'Agen ' . $t->agen->nama_agen : 'Tanpa agen' }}</div>
                            </td>
                            <td class="font-medium">{{ $t->kavling->kode_kavling }}</td>
                            <td class="whitespace-nowrap">{{ $t->isAngsuran() ? "Angsuran {$t->tenor} bln" : 'Cash' }}</td>
                            <td class="text-right tabular-nums">{{ rupiah($t->nilai_jual) }}</td>
                            <td class="text-right tabular-nums">
                                {{ rupiah($t->pokokTerbayar()) }}
                                @unless ($t->isBatal())
                                    <div class="mt-1 ml-auto h-1 w-20 overflow-hidden rounded-full bg-slate-100"><div class="h-full bg-forest-500" style="width: {{ $t->persenLunas() }}%"></div></div>
                                @endunless
                            </td>
                            <td class="text-right tabular-nums">{{ $t->isBatal() ? '—' : rupiah($t->sisa()) }}</td>
                            <td><x-badge :status="$t->status"/></td>
                            <td class="text-right"><a href="{{ route('transaksi-penjualan.show', $t) }}" class="btn-icon" title="Detail"><x-icon name="chevron-right"/></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($transaksis->hasPages())<div class="border-t border-slate-100 px-4 py-3">{{ $transaksis->links() }}</div>@endif
    @endif
</div>
@endsection
