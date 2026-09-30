@extends('layouts.app')
@section('title', $agen->nama_agen)

@section('content')
@php $admin = auth()->user()?->isAdmin(); @endphp
<div x-data="{ a: @js(['id' => $agen->id, 'nama_agen' => old('nama_agen', $agen->nama_agen), 'no_hp' => $agen->no_hp, 'email' => $agen->email, 'komisi_persen' => $agen->komisi_persen, 'aktif' => $agen->aktif, 'catatan' => $agen->catatan]), aksiAgen: '{{ route('agen.update', $agen) }}' }">

<x-page-header :title="$agen->nama_agen" :subtitle="$agen->kode_agen . ($agen->no_hp ? ' · ' . $agen->no_hp : '') . ($agen->aktif ? '' : ' · Nonaktif')"
               :back="$admin ? route('agen.index') : null" :breadcrumbs="$admin ? ['Agen' => route('agen.index'), $agen->kode_agen => null] : []">
    @if ($admin)
        <x-slot:actions>
            <x-button variant="secondary" icon="pencil" x-on:click="$dispatch('open-modal', 'agen')">Ubah</x-button>
            <x-button icon="banknotes" x-on:click="$dispatch('open-modal', 'bayar-komisi')" :disabled="($angka['sisa'] ?? 0) <= 0">Bayar Komisi</x-button>
        </x-slot:actions>
    @endif
</x-page-header>

<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <x-stat-card label="Lead / Prospek / Closing" :value="$angka['lead'] . ' / ' . $angka['prospek'] . ' / ' . $angka['closing']"
                 :hint="'Bulan ini ' . ($bulanan['lead'] ?? 0) . ' / ' . ($bulanan['prospek'] ?? 0) . ' / ' . ($bulanan['closing'] ?? 0)" icon="funnel"/>
    <x-stat-card label="Penjualan" :value="rupiah($angka['nilai_penjualan'])" :singkat="rupiah_singkat($angka['nilai_penjualan'])" :hint="$angka['transaksi'] . ' transaksi · ' . $angka['terjual'] . ' terjual'" icon="cart"/>
    <x-stat-card label="Komisi Hak" :value="$angka['komisi_hak'] !== null ? rupiah($angka['komisi_hak']) : 'Persen belum diatur'"
                 :singkat="$angka['komisi_hak'] !== null ? rupiah_singkat($angka['komisi_hak']) : '—'"
                 :hint="$angka['persen'] !== null ? persen($angka['persen'], false, 2) . ' × harga kavling terjual' : null" tone="utama" icon="check-badge"/>
    <x-stat-card label="Sisa Komisi" :value="$angka['sisa'] !== null ? rupiah($angka['sisa']) : '—'" :singkat="$angka['sisa'] !== null ? rupiah_singkat($angka['sisa']) : '—'" :hint="'Dibayar ' . rupiah($angka['dibayar'])" icon="wallet"/>
</div>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="min-w-0 space-y-5 xl:col-span-2">
        <x-card title="Komisi per Transaksi" subtitle="Menjadi hak saat PPJB ditandatangani." :padding="false">
            @if ($rincian->isEmpty())
                <x-empty-state title="Belum ada transaksi"/>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Transaksi</th><th>Konsumen</th><th class="text-right">Harga Jual</th><th>Status</th><th class="text-right">Komisi</th><th>Hak</th></tr></thead>
                        <tbody>
                            @foreach ($rincian as $r)
                                <tr>
                                    <td><a href="{{ route('transaksi-penjualan.show', $r->transaksi) }}" class="tautan">{{ $r->transaksi->kode_transaksi }}</a>
                                        <div class="text-xs text-slate-500">{{ $r->transaksi->kavling->kode_kavling }} · {{ tanggal($r->transaksi->tanggal) }}</div></td>
                                    <td>{{ $r->transaksi->konsumen->nama_lengkap }}</td>
                                    <td class="text-right tabular-nums">{{ rupiah($r->transaksi->nilai_jual) }}</td>
                                    <td><x-badge :status="$r->transaksi->status"/></td>
                                    <td class="text-right tabular-nums">{{ $r->komisi !== null ? rupiah($r->komisi) : '—' }}</td>
                                    <td>@if ($r->terjual)<x-badge status="selesai" label="Hak agen"/>@else<x-badge status="menunggu" label="Menunggu PPJB"/>@endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>

        <x-card title="Pembayaran Komisi" subtitle="Tercatat sebagai kas keluar." :padding="false">
            @if ($agen->komisiPembayarans->isEmpty())
                <x-empty-state title="Belum ada pembayaran"/>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Tanggal</th><th>Transaksi</th><th>Metode</th><th class="text-right">Nominal</th><th>Kas</th><th class="w-px"></th></tr></thead>
                        <tbody>
                            @foreach ($agen->komisiPembayarans as $p)
                                <tr>
                                    <td>{{ tanggal($p->tanggal) }}</td>
                                    <td>{{ $p->transaksi ? $p->transaksi->kode_transaksi . ' · ' . $p->transaksi->kavling->kode_kavling : 'Umum' }}@if ($p->catatan)<div class="text-xs text-slate-500">{{ $p->catatan }}</div>@endif</td>
                                    <td>{{ \App\Models\Pembayaran::METODE[$p->metode] }}</td>
                                    <td class="text-right font-medium tabular-nums">{{ rupiah($p->nominal) }}</td>
                                    <td class="text-xs text-slate-500">{{ $p->kas->kode ?? '—' }}</td>
                                    <td>@if ($admin)<x-delete-button :action="route('agen.komisi.destroy', [$agen, $p])" title="Hapus pembayaran komisi?" message="Catatan kas keluarnya ikut terhapus."/>@endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot><tr><td colspan="3">Total</td><td class="text-right tabular-nums">{{ rupiah($angka['dibayar']) }}</td><td colspan="2"></td></tr></tfoot>
                    </table>
                </div>
            @endif
        </x-card>
    </div>

    <x-card title="Lead Terbaru" :padding="false">
        <x-slot:actions><a href="{{ route('lead.index', ['agen' => $agen->id]) }}" class="tautan text-sm">Semua</a></x-slot:actions>
        <ul class="divide-y divide-slate-100">
            @forelse ($leads as $l)
                <li class="flex items-center justify-between gap-3 px-5 py-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-slate-900">{{ $l->nama }}</p>
                        <p class="text-xs text-slate-500">{{ $l->label_sumber }} · {{ tanggal($l->tanggal_lead) }}</p>
                    </div>
                    <x-badge :status="$l->tahap"/>
                </li>
            @empty
                <li><x-empty-state title="Belum ada lead"/></li>
            @endforelse
        </ul>
    </x-card>
</div>

@if ($admin)
    @include('agen._modal')

    <x-modal name="bayar-komisi" title="Bayar Komisi" :subtitle="$agen->nama_agen . ' · sisa ' . rupiah(max(0, $angka['sisa'] ?? 0))" max-width="md" :show="$errors->has('nominal') || $errors->has('tanggal') || $errors->has('transaksi_id')">
        <form method="POST" action="{{ route('agen.komisi.store', $agen) }}" class="flex min-h-0 flex-1 flex-col">
            @csrf
            <x-modal-body>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field label="Tanggal" name="tanggal" required><x-input type="date" name="tanggal" :value="now()->toDateString()" :max="now()->toDateString()"/></x-field>
                    <x-field label="Metode" name="metode" required><x-select name="metode" :options="\App\Models\Pembayaran::METODE"/></x-field>
                </div>
                <x-field label="Nominal" name="nominal" required><x-money name="nominal" :value="max(0, $angka['sisa'] ?? 0)"/></x-field>
                <x-field label="Untuk Transaksi" name="transaksi_id">
                    <x-select name="transaksi_id" :options="$rincian->where('terjual', true)->mapWithKeys(fn ($r) => [$r->transaksi->id => $r->transaksi->kode_transaksi . ' · ' . $r->transaksi->kavling->kode_kavling])" placeholder="Umum"/>
                </x-field>
                <x-field label="Catatan" name="catatan"><x-input name="catatan"/></x-field>
            </x-modal-body>
            <x-modal-footer><x-button type="submit">Simpan</x-button></x-modal-footer>
        </form>
    </x-modal>
@endif
</div>
@endsection
