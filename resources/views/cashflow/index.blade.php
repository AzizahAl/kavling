@extends('layouts.app')
@section('title', 'Alokasi Cashflow')

@php $warnaPos = ['tanah' => '#1a4028', 'legal_infra' => '#2e8b62', 'marketing' => '#7ec7a5', 'cadangan' => '#94a3b8', 'operasional' => '#475569']; @endphp

@section('content')
<x-page-header title="Alokasi Cashflow" subtitle="Setiap uang masuk dari konsumen dibagi otomatis ke 5 pos." :breadcrumbs="['Keuangan' => null, 'Alokasi Cashflow' => null]">
    <x-slot:actions>
        <x-button variant="secondary" icon="cog" :href="route('proyek.index') . '#grup-alokasi'">Atur Persentase</x-button>
    </x-slot:actions>
</x-page-header>

<div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
    @foreach ($pos as $p)
        @php $pakai = $p->alokasi > 0 ? min(100, $p->terpakai / $p->alokasi * 100) : 0; @endphp
        <div class="card p-4">
            <div class="flex items-center justify-between gap-2">
                <p class="flex items-center gap-2 text-xs font-medium text-slate-500"><span class="size-2 rounded-full" style="background: {{ $warnaPos[$p->pos] }}"></span>{{ $p->label }}</p>
                <span class="badge bg-slate-100 text-slate-600">{{ persen($p->persen, false) }}</span>
            </div>
            <p class="mt-1.5 text-lg font-semibold text-slate-900 tabular-nums">{{ rupiah($p->alokasi) }}</p>
            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full" style="width: {{ $pakai }}%; background: {{ $warnaPos[$p->pos] }}"></div></div>
            <div class="mt-2 flex flex-wrap justify-between gap-x-2 text-xs text-slate-500">
                <span>Terpakai {{ rupiah($p->terpakai) }}</span>
                <span @class(['font-medium', 'text-red-600' => $p->saldo < 0, 'text-slate-700' => $p->saldo >= 0])>Sisa {{ rupiah($p->saldo) }}</span>
            </div>
        </div>
    @endforeach
</div>

<div class="mb-5 grid grid-cols-1 gap-5 xl:grid-cols-3">
    <x-card title="Kelayakan Bagi Laba" class="xl:col-span-2">
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ([['Tanah Lunas', $laba['tanah'], 'Pengeluaran kas kategori Tanah = anggaran RAB Tanah'], ['Legal & Infrastruktur', $laba['legal'], 'Alokasi ≥ anggaran RAB ' . implode(', ', $laba['legal']['kategori'])]] as [$judul, $s, $ket])
                @php $pr = ($s['target'] ?? 0) > 0 ? min(100, $s['alokasi'] / $s['target'] * 100) : 0; @endphp
                <div @class(['rounded-kontrol border p-4', 'border-green-200 bg-green-50/50' => $s['terpenuhi'], 'border-slate-200' => ! $s['terpenuhi']])>
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-medium text-slate-900">{{ $judul }}</p>
                        <x-badge :status="$s['terpenuhi'] ? 'selesai' : 'belum'" :label="$s['terpenuhi'] ? 'Terpenuhi' : 'Belum'"/>
                    </div>
                    <p class="mt-0.5 text-xs text-slate-500">{{ $ket }}@if ($judul === 'Tanah Lunas') · <a href="{{ route('rab.index', ['periode' => 'semua', 'kategori' => 'Tanah']) }}" class="tautan">RAB Tanah</a>@endif</p>
                    @if ($s['catatan'])
                        <p class="mt-3 flex gap-1.5 text-xs text-amber-700"><x-icon name="warning" class="size-4"/>{{ $s['catatan'] }}</p>
                    @else
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand-500" style="width: {{ $pr }}%"></div></div>
                        <p class="mt-1.5 text-xs text-slate-600 tabular-nums">{{ rupiah($s['alokasi']) }} / {{ rupiah($s['target']) }} ({{ angka($pr, 1) }}%)</p>
                    @endif
                </div>
            @endforeach
        </div>
        <dl class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-100 pt-5 text-sm sm:grid-cols-4">
            <div><dt class="text-slate-500">Uang masuk konsumen</dt><dd class="mt-0.5 text-lg font-semibold tabular-nums">{{ rupiah($laba['uang_masuk']) }}</dd></div>
            <div><dt class="text-slate-500">Pengeluaran</dt><dd class="mt-0.5 text-lg font-semibold tabular-nums">{{ rupiah($laba['pengeluaran']) }}</dd></div>
            <div><dt class="text-slate-500">Laba</dt><dd @class(['mt-0.5 text-lg font-semibold tabular-nums', 'text-red-600' => $laba['laba'] < 0, 'text-brand-700' => $laba['laba'] >= 0])>{{ rupiah($laba['laba']) }}</dd></div>
            <div><dt class="text-slate-500">Laba bersih tersedia</dt><dd class="mt-0.5 text-lg font-semibold text-brand-700 tabular-nums">{{ rupiah($laba['laba_tersedia']) }}</dd></div>
        </dl>
    </x-card>

    <div @class(['rounded-kartu border p-5 shadow-kartu', 'border-brand-700 bg-brand-700 text-white' => $laba['layak'], 'border-slate-200 bg-white' => ! $laba['layak']])>
        <p @class(['text-xs font-medium', 'text-brand-100' => $laba['layak'], 'text-slate-500' => ! $laba['layak']])>Pembagian Laba</p>
        <p class="mt-1.5 text-xl font-semibold">{{ $laba['layak'] ? 'Layak dibagi' : 'Belum layak' }}</p>
        <p @class(['mt-1 text-sm', 'text-brand-100' => $laba['layak'], 'text-slate-500' => ! $laba['layak']])>{{ $laba['layak'] ? 'Kedua syarat terpenuhi.' : 'Menunggu tanah lunas & legal/infrastruktur tercukupi.' }}</p>
        <div class="mt-5 space-y-2.5">
            @foreach ([['Pengelola', $laba['persen_pengelola'], $laba['bagian_pengelola']], ['Pemilik Lahan', $laba['persen_pemilik'], $laba['bagian_pemilik']]] as [$nama, $p, $nilai])
                <div @class(['flex items-center justify-between gap-3 rounded-kontrol px-4 py-3', 'bg-white/10' => $laba['layak'], 'bg-slate-50' => ! $laba['layak']])>
                    <span>{{ $nama }} <span class="opacity-60">{{ persen($p, false) }}</span></span>
                    <span class="font-semibold tabular-nums">{{ rupiah($nilai) }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card" x-data="{ q: '', status: '' }">
    <div class="flex flex-col gap-2.5 border-b border-slate-100 p-3 sm:flex-row sm:flex-wrap sm:items-center sm:gap-3 sm:p-4">
        <h2 class="card-title sm:mr-2">Per Transaksi</h2>
        <div class="relative min-w-0 flex-1 sm:max-w-xs">
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"/>
            <input type="search" x-model="q" placeholder="Cari transaksi, kavling, konsumen…" class="form-input pl-9" aria-label="Cari">
        </div>
        <x-select name="f_status" :options="collect(\App\Models\TransaksiPenjualan::STATUS)->mapWithKeys(fn ($s) => [$s => \App\Support\Status::label($s)])" placeholder="Semua status" x-model="status" class="sm:w-44"/>
        <button type="button" class="btn btn-ghost btn-sm text-slate-500 sm:ml-auto" x-show="q || status" x-cloak x-on:click="q = ''; status = ''"><x-icon name="filter-x" class="size-4"/> Hapus filter</button>
    </div>
    @if ($perTransaksi->isEmpty())
        <x-empty-state title="Belum ada uang masuk" message="Alokasi muncul setelah pembayaran konsumen dicatat."/>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Transaksi</th><th class="text-right">Uang Masuk</th>@foreach (\App\Models\AlokasiKas::POS as $l)<th class="text-right">{{ $l }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach ($perTransaksi as $t)
                        <tr x-show="(! q || {{ Js::from(mb_strtolower($t->kode_transaksi . ' ' . $t->kode_kavling . ' ' . $t->nama_lengkap)) }}.includes(q.toLowerCase())) && (! status || status === '{{ $t->status }}')">
                            <td><a href="{{ route('transaksi-penjualan.show', $t->id) }}" class="tautan">{{ $t->kode_transaksi }}</a>
                                <div class="text-xs text-slate-500">{{ $t->kode_kavling }} · {{ $t->nama_lengkap }}@if ($t->status === 'batal') · <span class="text-red-600">batal</span>@endif</div></td>
                            <td class="text-right font-medium tabular-nums">{{ rupiah($t->total) }}</td>
                            @foreach (array_keys(\App\Models\AlokasiKas::POS) as $p)<td class="text-right text-slate-600 tabular-nums">{{ rupiah($t->{$p}) }}</td>@endforeach
                        </tr>
                    @endforeach
                </tbody>
                <tfoot><tr><td>Total</td><td class="text-right tabular-nums">{{ rupiah($perTransaksi->sum('total')) }}</td>@foreach (array_keys(\App\Models\AlokasiKas::POS) as $p)<td class="text-right tabular-nums">{{ rupiah($perTransaksi->sum($p)) }}</td>@endforeach</tr></tfoot>
            </table>
        </div>
    @endif
</div>
@endsection
