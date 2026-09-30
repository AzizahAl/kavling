@extends('layouts.app')
@section('title', $agen->nama_agen)

@section('content')
@php $admin = auth()->user()?->isAdmin(); @endphp
<div x-data="{ a: @js(['id' => $agen->id, 'nama_agen' => old('nama_agen', $agen->nama_agen), 'no_hp' => $agen->no_hp, 'email' => $agen->email, 'komisi_persen' => $agen->komisi_persen, 'aktif' => $agen->aktif, 'catatan' => $agen->catatan]), aksiAgen: '{{ route('agen.update', $agen) }}' }">

<x-page-header :title="$agen->nama_agen" :subtitle="$agen->kode_agen . ($agen->no_hp ? ' · ' . $agen->no_hp : '') . ($agen->aktif ? '' : ' · Nonaktif')"
               :back="$admin ? route('agen.index') : null" :breadcrumbs="$admin ? ['Agen & Marketing' => route('agen.index'), $agen->kode_agen => null] : []">
    @if ($admin)
    <x-slot:actions>
        <button type="button" class="btn btn-secondary" x-on:click="$dispatch('open-modal', 'agen')"><x-icon name="pencil" class="h-4 w-4"/> Ubah</button>
        <button type="button" class="btn btn-primary" x-on:click="$dispatch('open-modal', 'bayar-komisi')" @disabled(($angka['sisa'] ?? 0) <= 0)><x-icon name="banknotes" class="h-4 w-4"/> Bayar Komisi</button>
    </x-slot:actions>
    @endif
</x-page-header>

<div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 sm:gap-4">
    <x-stat-card label="Lead / Prospek / Closing" :value="$angka['lead'] . ' / ' . $angka['prospek'] . ' / ' . $angka['closing']"
                 :hint="'Bulan ini: ' . ($bulanan['lead'] ?? 0) . ' / ' . ($bulanan['prospek'] ?? 0) . ' / ' . ($bulanan['closing'] ?? 0)" icon="funnel"/>
    <x-stat-card label="Nilai Penjualan" :value="rupiah_singkat($angka['nilai_penjualan'])" :hint="$angka['transaksi'] . ' transaksi aktif · ' . $angka['terjual'] . ' terjual'" icon="cart"/>
    <x-stat-card label="Komisi Menjadi Hak" :value="$angka['komisi_hak'] !== null ? rupiah($angka['komisi_hak']) : 'Persen belum diatur'"
                 :hint="$angka['persen'] !== null ? persen($angka['persen'], false, 2) . ' × harga jual kavling terjual' : null" tone="dark" icon="check-badge"/>
    <x-stat-card label="Sisa Komisi" :value="$angka['sisa'] !== null ? rupiah($angka['sisa']) : '—'" :hint="'Dibayar ' . rupiah($angka['dibayar'])" tone="gold" icon="wallet"/>
</div>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <x-card title="Komisi per Transaksi" subtitle="Komisi menjadi hak agen saat kavling Terjual (PPJB ditandatangani)." :padding="false">
            @if ($rincian->isEmpty())
                <x-empty-state title="Belum ada transaksi" message="Transaksi yang dicatat atas agen ini akan muncul di sini."/>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Transaksi</th><th>Konsumen</th><th class="text-right">Harga Jual</th><th>Status</th><th class="text-right">Komisi</th><th>Hak</th></tr></thead>
                        <tbody>
                            @foreach ($rincian as $r)
                                <tr>
                                    <td><a href="{{ route('transaksi-penjualan.show', $r->transaksi) }}" class="font-medium text-forest-700 hover:underline">{{ $r->transaksi->kode_transaksi }}</a>
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

        <x-card title="Pembayaran Komisi" subtitle="Setiap pembayaran tercatat sebagai kas keluar." :padding="false">
            @if ($agen->komisiPembayarans->isEmpty())
                <x-empty-state title="Belum ada pembayaran komisi"/>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Tanggal</th><th>Untuk Transaksi</th><th>Metode</th><th class="text-right">Nominal</th><th>Kas</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($agen->komisiPembayarans as $p)
                                <tr>
                                    <td>{{ tanggal($p->tanggal) }}</td>
                                    <td>{{ $p->transaksi ? $p->transaksi->kode_transaksi . ' · ' . $p->transaksi->kavling->kode_kavling : '—' }}@if ($p->catatan)<div class="text-xs text-slate-500">{{ $p->catatan }}</div>@endif</td>
                                    <td>{{ \App\Models\Pembayaran::METODE[$p->metode] }}</td>
                                    <td class="text-right font-medium tabular-nums">{{ rupiah($p->nominal) }}</td>
                                    <td class="text-xs text-slate-500">{{ $p->kas->kode ?? '—' }}</td>
                                    <td class="text-right">@if ($admin)<x-delete-button :action="route('agen.komisi.destroy', [$agen, $p])" title="Hapus pembayaran komisi?" message="Catatan kas keluarnya juga akan dihapus."/>@endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot><tr><td colspan="3">Total dibayar</td><td class="text-right">{{ rupiah($angka['dibayar']) }}</td><td colspan="2"></td></tr></tfoot>
                    </table>
                </div>
            @endif
        </x-card>
    </div>

    <x-card title="Lead Terbaru" :padding="false">
        <x-slot:actions><a href="{{ route('lead.index', ['agen' => $agen->id]) }}" class="text-sm font-medium text-forest-700 hover:underline">Semua</a></x-slot:actions>
        @forelse ($leads as $l)
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3 last:border-0">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-slate-900">{{ $l->nama }}</p>
                    <p class="text-xs text-slate-500">{{ $l->label_sumber }} · {{ tanggal($l->tanggal_lead) }}</p>
                </div>
                <x-badge :status="$l->tahap"/>
            </div>
        @empty
            <x-empty-state title="Belum ada lead"/>
        @endforelse
    </x-card>
</div>

@if ($admin)
@include('agen._modal')

<x-modal name="bayar-komisi" title="Bayar Komisi {{ $agen->nama_agen }}" max-width="md" :show="$errors->has('nominal') || $errors->has('tanggal')">
    <form method="POST" action="{{ route('agen.komisi.store', $agen) }}">
        @csrf
        <div class="space-y-4 p-5">
            <div class="rounded-lg bg-gold-50 px-4 py-3 text-sm text-gold-800">Sisa komisi yang bisa dibayar: <span class="font-bold text-slate-900">{{ rupiah(max(0, $angka['sisa'] ?? 0)) }}</span></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Tanggal" name="tanggal" required><x-input type="date" name="tanggal" :value="now()->toDateString()" :max="now()->toDateString()"/></x-field>
                <x-field label="Metode" name="metode" required><x-select name="metode" :options="\App\Models\Pembayaran::METODE"/></x-field>
            </div>
            <x-field label="Nominal" name="nominal" required><x-money name="nominal" :value="max(0, $angka['sisa'] ?? 0)"/></x-field>
            <x-field label="Untuk Transaksi" name="transaksi_id">
                <x-select name="transaksi_id" :options="$rincian->where('terjual', true)->mapWithKeys(fn ($r) => [$r->transaksi->id => $r->transaksi->kode_transaksi . ' · ' . $r->transaksi->kavling->kode_kavling])" placeholder="— Umum —"/>
            </x-field>
            <x-field label="Catatan" name="catatan"><x-input name="catatan"/></x-field>
        </div>
        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'bayar-komisi')">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
        </div>
    </form>
</x-modal>
@endif
</div>
@endsection
