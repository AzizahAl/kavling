@extends('layouts.app')
@section('title', 'Kewajiban Tanah')

@php
    $total = $ringkas['total'];
    $bukaBayar = old('_form') === 'bayar-tanah' && $errors->any();
    $bukaTotal = old('_form') === 'total-tanah' && $errors->any();
@endphp

@section('content')
<x-page-header title="Kewajiban Tanah" :subtitle="'Pembayaran ke pemilik lahan' . ($pemilik ? ' (' . $pemilik . ')' : '') . '. Setiap pembayaran tercatat sebagai kas keluar.'"
               :breadcrumbs="['Keuangan' => null, 'Kewajiban Tanah' => null]">
    <x-slot:actions>
        <x-button variant="secondary" icon="pencil" x-data x-on:click="$dispatch('open-modal', 'total-tanah')">{{ $total === null ? 'Tetapkan Total' : 'Ubah Total' }}</x-button>
        <x-button icon="plus" x-data x-on:click="$dispatch('open-modal', 'bayar-tanah')">Catat Pembayaran</x-button>
    </x-slot:actions>
</x-page-header>

@if ($total === null)
    <div class="mb-5 flex gap-3 rounded-kartu border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        <x-icon name="warning" class="size-5 text-amber-500"/>
        <p>Total kesepakatan dengan pemilik lahan belum ditetapkan. Pembayaran tetap bisa dicatat, tetapi tanah belum dianggap lunas sampai total ditetapkan dan sisanya nol.</p>
    </div>
@endif

<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <x-stat-card label="Total Kesepakatan" :value="$total === null ? 'Belum ditetapkan' : rupiah($total)" :singkat="$total === null ? '—' : rupiah_singkat($total)" icon="landmark"/>
    <x-stat-card label="Sudah Dibayar" :value="rupiah($ringkas['terbayar'])" :singkat="rupiah_singkat($ringkas['terbayar'])" :hint="$daftar->count() . ' pembayaran'" tone="utama" icon="check-circle">
        @if ($total)
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand-500" style="width: {{ min(100, $ringkas['persen']) }}%"></div></div>
        @endif
    </x-stat-card>
    <x-stat-card label="Sisa Kewajiban" :value="$total === null ? '—' : rupiah($ringkas['sisa'])" :singkat="$total === null ? '—' : rupiah_singkat($ringkas['sisa'])"
                 :hint="$total ? angka($ringkas['persen'], 1) . '% terbayar' : null" :tone="$total && $ringkas['sisa'] > 0 ? 'bahaya' : 'default'" icon="wallet"/>
    <x-stat-card label="Status Tanah" :value="$ringkas['lunas'] ? 'Lunas' : 'Belum lunas'" :tone="$ringkas['lunas'] ? 'utama' : 'default'" icon="scale"/>
</div>

<div class="mb-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
    <x-card title="Kewajiban Terpenuhi">
        <ul class="space-y-3 text-sm">
            @foreach ([['Tanah lunas', $laba['tanah']['terpenuhi'], $laba['tanah']['catatan'] ?? ($ringkas['lunas'] ? 'Pembayaran = total kesepakatan.' : 'Sisa ' . rupiah($ringkas['sisa']) . '.')],
                       ['Legal & infrastruktur tercukupi', $laba['legal']['terpenuhi'], $laba['legal']['catatan'] ?? rupiah($laba['legal']['alokasi']) . ' / ' . rupiah($laba['legal']['target'])]] as [$nama, $ok, $ket])
                <li class="flex items-start justify-between gap-3">
                    <div><p class="font-medium text-slate-800">{{ $nama }}</p><p class="text-xs text-slate-500">{{ $ket }}</p></div>
                    <x-badge :status="$ok ? 'selesai' : 'belum'" :label="$ok ? 'Terpenuhi' : 'Belum'"/>
                </li>
            @endforeach
        </ul>
    </x-card>
    <x-card title="Laba Bersih Tersedia">
        <p @class(['text-2xl font-semibold tabular-nums', 'text-brand-700' => $laba['layak'], 'text-slate-400' => ! $laba['layak']])>{{ rupiah($laba['laba_tersedia']) }}</p>
        <p class="mt-1 text-sm text-slate-500">
            {{ $laba['layak'] ? 'Semua kewajiban terpenuhi; laba boleh dibagi.' : 'Laba baru tersedia setelah tanah lunas dan legal/infrastruktur tercukupi.' }}
            Laba saat ini {{ rupiah($laba['laba']) }}.
        </p>
        <a href="{{ route('cashflow.index') }}" class="tautan mt-3 inline-block text-sm">Lihat Cashflow</a>
    </x-card>
</div>

<div class="card">
    <x-filter-bar placeholder="Cari keterangan…">
        <x-select name="metode" :options="\App\Models\Pembayaran::METODE" :value="request('metode')" placeholder="Semua metode"/>
        <x-select name="tahun" :options="$tahun->mapWithKeys(fn ($t) => [$t => $t])->all()" :value="request('tahun')" placeholder="Semua tahun"/>
    </x-filter-bar>

    @if ($daftar->isEmpty())
        <x-empty-state icon="landmark" title="Belum ada pembayaran ke pemilik lahan" :message="request()->query() ? 'Ubah atau hapus filter.' : null">
            <x-button icon="plus" x-data x-on:click="$dispatch('open-modal', 'bayar-tanah')">Catat Pembayaran</x-button>
        </x-empty-state>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Tanggal</th><th>Keterangan</th><th>Metode</th><th class="text-right">Nominal</th><th>Kas</th><th class="w-px"></th></tr></thead>
                <tbody>
                    @foreach ($daftar as $p)
                        <tr>
                            <td>{{ tanggal($p->tanggal) }}</td>
                            <td class="wrap">{{ $p->keterangan }}<div class="text-xs text-slate-500">{{ $p->pembuat->name ?? '—' }}</div></td>
                            <td>{{ \App\Models\Pembayaran::METODE[$p->metode] ?? $p->metode }}</td>
                            <td class="text-right font-medium tabular-nums">{{ rupiah($p->nominal) }}</td>
                            <td class="text-xs text-slate-500">{{ $p->kas->kode ?? '—' }}</td>
                            <td><x-delete-button :action="route('kewajiban-tanah.destroy', $p)" title="Hapus pembayaran tanah?" message="Catatan kas keluarnya ikut terhapus."/></td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot><tr><td colspan="3">Total</td><td class="text-right tabular-nums">{{ rupiah($daftar->sum('nominal')) }}</td><td colspan="2"></td></tr></tfoot>
            </table>
        </div>
    @endif
</div>

<x-modal name="total-tanah" title="Total Kesepakatan" subtitle="Total yang harus dibayar ke pemilik lahan." max-width="md" :show="$bukaTotal">
    <form method="POST" action="{{ route('kewajiban-tanah.total') }}" class="flex min-h-0 flex-1 flex-col">
        @csrf @method('PUT')
        <input type="hidden" name="_form" value="total-tanah">
        <x-modal-body>
            <x-field label="Total kesepakatan" name="total" hint="Kosongkan bila belum disepakati.">
                <x-money name="total" :value="$total"/>
            </x-field>
        </x-modal-body>
        <x-modal-footer><x-button type="submit" icon="check">Simpan</x-button></x-modal-footer>
    </form>
</x-modal>

<x-modal name="bayar-tanah" title="Catat Pembayaran Tanah" :subtitle="$total !== null ? 'Sisa ' . rupiah($ringkas['sisa']) : null" max-width="md" :show="$bukaBayar">
    <form method="POST" action="{{ route('kewajiban-tanah.store') }}" class="flex min-h-0 flex-1 flex-col">
        @csrf
        <input type="hidden" name="_form" value="bayar-tanah">
        <x-modal-body>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Tanggal" name="tanggal" required><x-input type="date" name="tanggal" :value="now()->toDateString()" :max="now()->toDateString()"/></x-field>
                <x-field label="Metode" name="metode" required><x-select name="metode" :options="\App\Models\Pembayaran::METODE" :value="old('metode', 'transfer')"/></x-field>
            </div>
            <x-field label="Nominal" name="nominal" required><x-money name="nominal"/></x-field>
            <x-field label="Keterangan" name="keterangan" required><x-input name="keterangan" placeholder="Mis. Pembayaran tahap 1"/></x-field>
        </x-modal-body>
        <x-modal-footer><x-button type="submit" icon="check">Simpan Pembayaran</x-button></x-modal-footer>
    </form>
</x-modal>
@endsection
