@extends('layouts.app')
@section('title', 'Alokasi Cashflow')

@php $warnaPos = ['tanah' => '#1a4028', 'legal_infra' => '#2f7a55', 'marketing' => '#e8b74d', 'cadangan' => '#94a3b8', 'operasional' => '#475569']; @endphp

@section('content')
<x-page-header title="Alokasi Cashflow" subtitle="Setiap uang masuk dari konsumen dialokasikan otomatis ke pos-pos proyek."
               :breadcrumbs="['Keuangan' => null, 'Alokasi Cashflow' => null]">
    <x-slot:actions>
        <a href="{{ route('proyek.index') }}#grup-alokasi" class="btn btn-secondary"><x-icon name="cog" class="h-4 w-4"/> Atur Persentase</a>
    </x-slot:actions>
</x-page-header>

{{-- Pos alokasi --}}
<div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5 sm:gap-4">
    @foreach ($pos as $p)
        @php $pakai = $p->alokasi > 0 ? min(100, $p->terpakai / $p->alokasi * 100) : 0; @endphp
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between gap-2">
                <p class="text-xs font-medium tracking-wide text-slate-500 uppercase">{{ $p->label }}</p>
                <span class="badge bg-slate-100 text-slate-600">{{ persen($p->persen, false) }}</span>
            </div>
            <p class="mt-1.5 text-xl font-bold tabular-nums text-slate-900">{{ rupiah($p->alokasi) }}</p>
            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full" style="width: {{ $pakai }}%; background: {{ $warnaPos[$p->pos] }}"></div></div>
            <div class="mt-2 flex justify-between text-xs text-slate-500">
                <span>Terpakai {{ rupiah($p->terpakai) }}</span>
                <span @class(['font-semibold', 'text-red-600' => $p->saldo < 0, 'text-slate-700' => $p->saldo >= 0])>Sisa {{ rupiah($p->saldo) }}</span>
            </div>
        </div>
    @endforeach
</div>

<div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
    {{-- Kelayakan bagi laba --}}
    <x-card title="Status Kelayakan Bagi Laba" class="xl:col-span-2">
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ([['Tanah Lunas', $laba['tanah'], 'Alokasi tanah ≥ total kewajiban ke pemilik lahan'], ['Legal & Infrastruktur Tercukupi', $laba['legal'], 'Alokasi legal+infra ≥ anggaran RAB ' . implode(', ', $laba['legal']['kategori'])]] as [$judul, $s, $ket])
                @php $pr = ($s['target'] ?? 0) > 0 ? min(100, $s['alokasi'] / $s['target'] * 100) : 0; @endphp
                <div class="rounded-xl border p-4 {{ $s['terpenuhi'] ? 'border-forest-200 bg-forest-50/60' : 'border-slate-200' }}">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-semibold text-slate-900">{{ $judul }}</p>
                        <x-badge :status="$s['terpenuhi'] ? 'selesai' : 'belum'" :label="$s['terpenuhi'] ? 'Terpenuhi' : 'Belum'"/>
                    </div>
                    <p class="mt-0.5 text-xs text-slate-500">{{ $ket }}</p>
                    @if ($s['catatan'])
                        <p class="mt-3 flex gap-1.5 text-xs text-amber-700"><x-icon name="warning" class="h-4 w-4"/> {{ $s['catatan'] }}</p>
                    @else
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-forest-500" style="width: {{ $pr }}%"></div></div>
                        <p class="mt-1.5 text-xs tabular-nums text-slate-600">{{ rupiah($s['alokasi']) }} / {{ rupiah($s['target']) }} ({{ angka($pr, 1) }}%)</p>
                    @endif
                </div>
            @endforeach
        </div>

        <dl class="mt-5 grid gap-3 border-t border-slate-100 pt-5 text-sm sm:grid-cols-3">
            <div><dt class="text-slate-500">Uang masuk dari konsumen</dt><dd class="mt-0.5 text-lg font-semibold tabular-nums">{{ rupiah($laba['uang_masuk']) }}</dd></div>
            <div><dt class="text-slate-500">Pengeluaran terealisasi</dt><dd class="mt-0.5 text-lg font-semibold tabular-nums">{{ rupiah($laba['pengeluaran']) }}</dd></div>
            <div><dt class="text-slate-500">Laba</dt><dd @class(['mt-0.5 text-lg font-bold tabular-nums', 'text-red-600' => $laba['laba'] < 0, 'text-forest-700' => $laba['laba'] >= 0])>{{ rupiah($laba['laba']) }}</dd></div>
        </dl>
    </x-card>

    <div @class(['rounded-xl border p-5 shadow-xs', 'border-forest-800 bg-forest-800 text-white' => $laba['layak'], 'border-slate-200 bg-white' => ! $laba['layak']])>
        <p @class(['text-xs font-medium tracking-wide uppercase', 'text-forest-200' => $laba['layak'], 'text-slate-500' => ! $laba['layak']])>Pembagian Laba</p>
        <p class="mt-1.5 text-xl font-bold">{{ $laba['layak'] ? 'Layak dibagi' : 'Belum layak dibagi' }}</p>
        <p @class(['mt-1 text-sm', 'text-forest-100' => $laba['layak'], 'text-slate-500' => ! $laba['layak']])>
            {{ $laba['layak'] ? 'Kedua syarat terpenuhi dan laba positif.' : 'Laba baru boleh dibagi setelah tanah lunas dan legal & infrastruktur tercukupi.' }}
        </p>
        <div class="mt-5 space-y-3">
            @foreach ([['Pengelola', $laba['persen_pengelola'], $laba['bagian_pengelola']], ['Pemilik Lahan', $laba['persen_pemilik'], $laba['bagian_pemilik']]] as [$nama, $p, $nilai])
                <div class="flex items-center justify-between gap-3 rounded-lg {{ $laba['layak'] ? 'bg-white/10' : 'bg-slate-50' }} px-4 py-3">
                    <span>{{ $nama }} <span class="opacity-60">{{ persen($p, false) }}</span></span>
                    <span class="font-semibold tabular-nums">{{ rupiah($nilai) }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<x-card title="Alokasi per Transaksi" subtitle="Jumlah alokasi dari seluruh pembayaran (dikurangi refund) tiap transaksi." :padding="false">
    @if ($perTransaksi->isEmpty())
        <x-empty-state title="Belum ada uang masuk" message="Alokasi muncul otomatis setelah pembayaran konsumen dicatat."/>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Transaksi</th><th class="text-right">Uang Masuk</th>@foreach (\App\Models\AlokasiKas::POS as $l)<th class="text-right">{{ $l }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach ($perTransaksi as $t)
                        <tr>
                            <td><a href="{{ route('transaksi-penjualan.show', $t->id) }}" class="font-medium text-forest-700 hover:underline">{{ $t->kode_transaksi }}</a>
                                <div class="text-xs text-slate-500">{{ $t->kode_kavling }} · {{ $t->nama_lengkap }} @if ($t->status === 'batal')· <span class="text-red-600">batal</span>@endif</div></td>
                            <td class="text-right font-medium tabular-nums">{{ rupiah($t->total) }}</td>
                            @foreach (array_keys(\App\Models\AlokasiKas::POS) as $p)<td class="text-right tabular-nums text-slate-600">{{ rupiah($t->{$p}) }}</td>@endforeach
                        </tr>
                    @endforeach
                </tbody>
                <tfoot><tr><td>Total</td><td class="text-right">{{ rupiah($perTransaksi->sum('total')) }}</td>@foreach (array_keys(\App\Models\AlokasiKas::POS) as $p)<td class="text-right">{{ rupiah($perTransaksi->sum($p)) }}</td>@endforeach</tr></tfoot>
            </table>
        </div>
    @endif
</x-card>
@endsection
