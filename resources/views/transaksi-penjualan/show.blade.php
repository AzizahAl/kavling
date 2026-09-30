@extends('layouts.app')
@section('title', 'Transaksi ' . $t->kode_transaksi)

@php
    $jadwal = $angsuran['jadwal'];
    $saran = [
        'reservasi' => max(0, (float) $t->biaya_reservasi - $t->terbayarJenis('reservasi')),
        'booking'   => max(0, (float) $t->biaya_booking - $t->terbayarJenis('booking')),
        'dp'        => max(0, min((float) $t->nominal_dp - $t->terbayarJenis('dp'), $t->sisa())),
        'angsuran'  => $angsuran['berikutnya']->sisa ?? 0,
        'pelunasan' => $t->sisa(),
    ];
    $jenisAwal = collect(['reservasi', 'booking', 'dp'])->first(fn ($j) => $saran[$j] > 0) ?? ($t->isAngsuran() ? 'angsuran' : 'pelunasan');
    $admin = auth()->user()?->isAdmin();
    $bisaBayar = $admin && ! $t->isBatal() && $t->status !== 'lunas';
    $formBayarAwal = old('_form') === 'bayar'
        ? ['id' => old('_id'), 'tanggal' => old('tanggal'), 'jenis' => old('jenis'), 'nominal' => old('nominal'), 'metode' => old('metode'), 'no_bukti' => old('no_bukti'), 'catatan' => old('catatan')]
        : ['id' => null, 'tanggal' => now()->toDateString(), 'jenis' => $jenisAwal, 'nominal' => $saran[$jenisAwal], 'metode' => 'transfer', 'no_bukti' => '', 'catatan' => ''];
    $opsiJenis = collect(\App\Models\Pembayaran::JENIS)->reject(fn ($l, $v) => $v === 'angsuran' && ! $t->isAngsuran());
    $dataBayar = fn ($p) => ['id' => $p->id, 'tanggal' => $p->tanggal->toDateString(), 'jenis' => $p->jenis, 'nominal' => (float) $p->nominal, 'metode' => $p->metode, 'no_bukti' => $p->no_bukti, 'catatan' => $p->catatan];
@endphp

@section('content')
<div x-data="{
        f: @js($formBayarAwal),
        saran: @js($saran),
        get action() { return this.f.id ? '{{ url('transaksi-penjualan/' . $t->id . '/pembayaran') }}/' + this.f.id : '{{ route('pembayaran.store', $t) }}' },
        baru() { this.f = { ...@js($formBayarAwal), id: null, jenis: @js($jenisAwal), nominal: this.saran[@js($jenisAwal)] }; this.sinkronUang(); this.$dispatch('open-modal', 'bayar') },
        ubah(p) { this.f = { ...p }; this.sinkronUang(); this.$dispatch('open-modal', 'bayar') },
        pilihJenis() { if (!this.f.id) { this.f.nominal = this.saran[this.f.jenis] || ''; this.sinkronUang() } },
        sinkronUang() { this.$nextTick(() => this.$dispatch('set-money', { name: 'nominal', value: this.f.nominal })) },
     }">

<x-page-header :title="$t->kode_transaksi"
               :subtitle="$t->konsumen->nama_lengkap . ' · Kavling ' . $t->kavling->kode_kavling . ' · ' . tanggal($t->tanggal)"
               :back="$admin ? route('transaksi-penjualan.index') : route('agen.show', $t->agen_id)"
               :breadcrumbs="$admin ? ['Transaksi' => route('transaksi-penjualan.index'), $t->kode_transaksi => null] : []">
    <x-slot:actions>
        <x-badge :status="$t->status" class="!h-7 !grow-0 px-3 text-[13px]"/>
        @if ($admin && ! $t->isBatal())
            <x-menu align="right" width="w-52">
                <x-slot:trigger><x-button variant="secondary" icon="more" aria-haspopup="menu">Lainnya</x-button></x-slot:trigger>
                <x-menu-item :href="route('transaksi-penjualan.edit', $t)" icon="pencil">Ubah transaksi</x-menu-item>
                <x-menu-item :href="route('dokumen.lihat', [$t, 'spk'])" icon="document">SPK</x-menu-item>
                <x-menu-item :href="route('dokumen.lihat', [$t, 'ppjb'])" icon="document">PPJB</x-menu-item>
                <div class="my-1 border-t border-slate-100"></div>
                <x-menu-item icon="ban" danger x-on:click="$dispatch('open-modal', 'batal')">Batalkan transaksi</x-menu-item>
            </x-menu>
        @endif
        @if ($bisaBayar)
            <x-button icon="plus" x-on:click="baru()">Catat Pembayaran</x-button>
        @endif
    </x-slot:actions>
</x-page-header>

@if ($t->isBatal())
    <div class="mb-5 flex gap-3 rounded-kartu border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        <x-icon name="x-circle" class="size-5 text-red-500"/>
        <div class="min-w-0 space-y-1">
            <p class="font-semibold">Dibatalkan {{ tanggal($t->tanggal_batal, 'j F Y') }}</p>
            <p>Alasan: {{ $t->alasan_batal }}</p>
            @forelse ($t->kasRefunds as $r)
                <p>Refund {{ rupiah($r->nominal) }} ({{ $r->kode }}). {{ $r->catatan }}</p>
            @empty
                <p>Tidak ada refund.</p>
            @endforelse
        </div>
    </div>
@endif

<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <x-stat-card label="Harga Jual" :value="rupiah($t->nilai_jual)" :singkat="rupiah_singkat($t->nilai_jual)" :hint="angka($t->luas) . ' m² × ' . rupiah($t->harga_per_m2)" tone="utama" icon="lock"/>
    <x-stat-card label="Terbayar" :value="rupiah($t->pokokTerbayar())" :singkat="rupiah_singkat($t->pokokTerbayar())" :hint="angka($t->persenLunas(), 1) . '% dari harga'" icon="check-circle">
        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand-500" style="width: {{ $t->persenLunas() }}%"></div></div>
    </x-stat-card>
    <x-stat-card label="Sisa" :value="rupiah($t->sisa())" :singkat="rupiah_singkat($t->sisa())" :tone="$angsuran['jumlah_telat'] ? 'bahaya' : 'default'"
                 :hint="$angsuran['jumlah_telat'] ? $angsuran['jumlah_telat'] . ' cicilan telat · ' . rupiah($angsuran['tunggakan']) : null" icon="wallet"/>
    <x-stat-card label="Uang Masuk" :value="rupiah($t->totalMasuk())" :singkat="rupiah_singkat($t->totalMasuk())" hint="Termasuk reservasi & booking" icon="banknotes"/>
</div>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="min-w-0 space-y-5 xl:col-span-2">
        <x-card title="Pembayaran" subtitle="Otomatis tercatat sebagai kas masuk." :padding="false">
            @if ($t->pembayarans->isEmpty())
                <x-empty-state title="Belum ada pembayaran">
                    @if ($bisaBayar)<x-button icon="plus" x-on:click="baru()">Catat Pembayaran</x-button>@endif
                </x-empty-state>
            @else
                <div class="table-wrap max-md:hidden">
                    <table class="table">
                        <thead><tr><th>No. Kwitansi</th><th>Tanggal</th><th>Jenis</th><th>Metode</th><th class="text-right">Nominal</th><th>Kas</th><th class="w-px"></th></tr></thead>
                        <tbody>
                            @foreach ($t->pembayarans as $p)
                                <tr>
                                    <td class="font-medium text-slate-900">{{ $p->kode }}</td>
                                    <td>{{ tanggal($p->tanggal) }}</td>
                                    <td>{{ $p->label_jenis }} @if (in_array($p->jenis, ['reservasi', 'booking']))<span class="text-xs text-slate-400">di luar harga</span>@endif</td>
                                    <td>{{ \App\Models\Pembayaran::METODE[$p->metode] }}@if ($p->no_bukti)<div class="text-xs text-slate-500">{{ $p->no_bukti }}</div>@endif</td>
                                    <td class="text-right font-medium tabular-nums">{{ rupiah($p->nominal) }}</td>
                                    <td class="text-xs text-slate-500">{{ $p->kas->kode ?? '—' }}</td>
                                    <td>@include('transaksi-penjualan._aksi-bayar')</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot><tr><td colspan="4">Total</td><td class="text-right tabular-nums">{{ rupiah($t->totalMasuk()) }}</td><td colspan="2"></td></tr></tfoot>
                    </table>
                </div>
                <ul class="divide-y divide-slate-100 md:hidden">
                    @foreach ($t->pembayarans as $p)
                        <li class="flex items-center gap-3 px-4 py-3">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-slate-900">{{ $p->label_jenis }} <span class="font-semibold tabular-nums">{{ rupiah($p->nominal) }}</span></p>
                                <p class="text-xs text-slate-500">{{ $p->kode }} · {{ tanggal($p->tanggal) }} · {{ \App\Models\Pembayaran::METODE[$p->metode] }}</p>
                            </div>
                            @include('transaksi-penjualan._aksi-bayar')
                        </li>
                    @endforeach
                    <li class="flex justify-between px-4 py-3 text-sm font-semibold"><span>Total</span><span class="tabular-nums">{{ rupiah($t->totalMasuk()) }}</span></li>
                </ul>
            @endif
        </x-card>

        @if ($t->isAngsuran() && $jadwal->isNotEmpty())
            <x-card title="Jadwal Angsuran" :subtitle="$angsuran['cicilan_lunas'] . ' dari ' . $jadwal->count() . ' lunas · ' . rupiah($cicilan) . '/bulan'" :padding="false">
                <div class="table-wrap max-h-[480px] overflow-y-auto">
                    <table class="table">
                        <thead class="sticky top-0 z-[1]"><tr><th>Ke</th><th>Jatuh Tempo</th><th class="text-right">Cicilan</th><th class="text-right">Terbayar</th><th class="text-right">Sisa</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach ($jadwal as $j)
                                <tr @class(['bg-red-50/60' => $j->status === 'terlambat'])>
                                    <td class="font-medium">{{ $j->ke }}</td>
                                    <td>{{ tanggal($j->jatuh_tempo) }}</td>
                                    <td class="text-right tabular-nums">{{ rupiah($j->nominal) }}</td>
                                    <td class="text-right tabular-nums">{{ $j->terbayar ? rupiah($j->terbayar) : '—' }}</td>
                                    <td class="text-right tabular-nums">{{ $j->sisa ? rupiah($j->sisa) : '—' }}</td>
                                    <td><x-badge :status="$j->status" :label="$j->hari_telat ? 'Telat ' . $j->hari_telat . ' hari' : null"/></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif
    </div>

    <div class="min-w-0 space-y-5">
        <x-card title="Rincian">
            <dl class="space-y-3 text-sm">
                @foreach ([
                    ['Konsumen', ($admin ? '<a href="' . route('konsumen.show', $t->konsumen) . '" class="tautan">' . e($t->konsumen->nama_lengkap) . '</a>' : e($t->konsumen->nama_lengkap)) . '<span class="block text-xs font-normal text-slate-500">' . e($t->konsumen->id_konsumen . ' · ' . $t->konsumen->no_hp) . '</span>'],
                    ['Kavling', ($admin ? '<a href="' . route('kavling.show', $t->kavling) . '" class="tautan">' . e($t->kavling->kode_kavling) . '</a>' : e($t->kavling->kode_kavling)) . ' · ' . e($t->kavling->tipe) . ' · ' . angka($t->luas) . ' m²'],
                    ['Tahap Harga', e(($t->tahap->nama_tahap ?? '—') . ' · ' . rupiah($t->harga_per_m2) . '/m²')],
                    ['Agen', e($t->agen->nama_agen ?? '—')],
                    ['Metode', $t->isAngsuran() ? 'Angsuran ' . $t->tenor . ' bulan' : 'Cash'],
                    ['DP', rupiah($t->nominal_dp) . ($t->nilai_jual > 0 ? ' (' . angka($t->nominal_dp / $t->nilai_jual * 100, 1) . '%)' : '')],
                    ['Di luar harga', 'Reservasi ' . rupiah($t->biaya_reservasi) . ' · Booking ' . rupiah($t->biaya_booking)],
                    ['Dicatat oleh', e($t->pembuat->name ?? '—')],
                    ['Catatan', e($t->catatan ?: '—')],
                ] as [$l, $v])
                    <div class="flex flex-col gap-0.5 sm:flex-row sm:justify-between sm:gap-4">
                        <dt class="shrink-0 text-slate-500">{{ $l }}</dt>
                        <dd class="font-medium text-slate-800 sm:text-right">{!! $v !!}</dd>
                    </div>
                @endforeach
            </dl>
        </x-card>

        <x-card title="Tahapan Pembayaran">
            @php
                $tahapan = [
                    ['Reservasi', $t->terbayarJenis('reservasi'), (float) $t->biaya_reservasi],
                    ['Booking Fee', $t->terbayarJenis('booking'), (float) $t->biaya_booking],
                    ['DP', $t->terbayarJenis('dp'), (float) $t->nominal_dp],
                    [$t->isAngsuran() ? 'Angsuran & Pelunasan' : 'Pelunasan', $t->terbayarJenis('angsuran') + $t->terbayarJenis('pelunasan'), $t->pokokDiangsur()],
                ];
            @endphp
            <ol class="space-y-4">
                @foreach ($tahapan as [$nama, $bayar, $target])
                    @php $st = $target <= 0 ? null : ($bayar >= $target ? 'selesai' : ($bayar > 0 ? 'proses' : 'belum')); @endphp
                    <li>
                        <div class="flex items-center justify-between gap-2 text-sm">
                            <span class="font-medium text-slate-800">{{ $nama }}</span>
                            @if ($st)<x-badge :status="$st"/>@else<span class="text-xs text-slate-400">Tidak dipakai</span>@endif
                        </div>
                        @if ($target > 0)
                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand-500" style="width: {{ min(100, $bayar / $target * 100) }}%"></div></div>
                            <p class="mt-1 text-xs text-slate-500 tabular-nums">{{ rupiah($bayar) }} / {{ rupiah($target) }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        </x-card>

        <x-card title="Checklist Legal">
            @if ($admin && ! $t->isBatal())
                <x-slot:actions><a href="{{ route('legal.index') }}" class="tautan text-sm">Ubah</a></x-slot:actions>
            @endif
            <ul class="space-y-2.5 text-sm">
                @foreach (\App\Models\ChecklistLegal::ITEM as $kunci => $label)
                    <li class="flex items-center justify-between gap-2">
                        <span class="text-slate-700">{{ $label }}</span>
                        <span class="flex items-center gap-2">
                            @if ($t->checklist?->{$kunci . '_tanggal'})<span class="text-xs text-slate-500">{{ tanggal($t->checklist->{$kunci . '_tanggal'}) }}</span>@endif
                            <x-badge :status="$t->checklist?->{$kunci . '_status'} ?? 'belum'"/>
                        </span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-3 text-xs text-slate-500">PPJB selesai = kavling Terjual.</p>
            @if ($t->lead)
                <p class="mt-2 text-xs text-slate-500">Closing dari lead <a href="{{ route('lead.index', ['cari' => $t->lead->kode]) }}" class="tautan">{{ $t->lead->kode }}</a></p>
            @endif
        </x-card>
    </div>
</div>

@if ($admin)
    {{-- Popup catat / ubah pembayaran --}}
    <x-modal name="bayar" judul-js="f.id ? 'Ubah Pembayaran' : 'Catat Pembayaran'" :show="old('_form') === 'bayar' && $errors->any()">
        <form method="POST" :action="action" class="flex min-h-0 flex-1 flex-col">
            @csrf
            <template x-if="f.id"><input type="hidden" name="_method" value="PUT"></template>
            <input type="hidden" name="_form" value="bayar">
            <input type="hidden" name="_id" :value="f.id">
            <x-modal-body>
                <dl class="grid grid-cols-2 gap-3 rounded-kontrol bg-slate-50 p-3 text-xs">
                    <div><dt class="text-slate-500">Sisa harga</dt><dd class="font-semibold text-slate-800 tabular-nums">{{ rupiah($t->sisa()) }}</dd></div>
                    @if ($angsuran['berikutnya'])
                        <div><dt class="text-slate-500">Cicilan ke-{{ $angsuran['berikutnya']->ke }}</dt><dd class="font-semibold text-slate-800 tabular-nums">{{ rupiah($angsuran['berikutnya']->sisa) }} · {{ tanggal($angsuran['berikutnya']->jatuh_tempo) }}</dd></div>
                    @endif
                </dl>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field label="Jenis" name="jenis" required>
                        <x-select name="jenis" :options="$opsiJenis" x-model="f.jenis" x-on:change="pilihJenis()"/>
                    </x-field>
                    <x-field label="Tanggal Bayar" name="tanggal" required>
                        <x-input type="date" name="tanggal" x-model="f.tanggal" :max="now()->toDateString()"/>
                    </x-field>
                </div>
                <x-field label="Nominal" name="nominal" required><x-money name="nominal"/></x-field>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field label="Metode" name="metode" required><x-select name="metode" :options="\App\Models\Pembayaran::METODE" x-model="f.metode"/></x-field>
                    <x-field label="No. Bukti" name="no_bukti"><x-input name="no_bukti" x-model="f.no_bukti"/></x-field>
                </div>
                <x-field label="Catatan" name="catatan"><x-input name="catatan" x-model="f.catatan"/></x-field>
            </x-modal-body>
            <x-modal-footer><button type="submit" class="btn btn-primary" x-text="f.id ? 'Simpan' : 'Simpan Pembayaran'"></button></x-modal-footer>
        </form>
    </x-modal>

    {{-- Popup pembatalan --}}
    @if ($refund)
        <x-modal name="batal" title="Batalkan Transaksi" :subtitle="$t->kode_transaksi . ' · ' . $t->konsumen->nama_lengkap" :show="$errors->has('alasan') || $errors->has('tanggal_batal')">
            <form method="POST" action="{{ route('transaksi-penjualan.batal', $t) }}" class="flex min-h-0 flex-1 flex-col"
                  data-confirm="Kavling kembali tersedia dan refund dicatat sebagai kas keluar. Tidak bisa diurungkan."
                  data-confirm-title="Batalkan transaksi ini?" data-confirm-ok="Ya, batalkan">
                @csrf
                <x-modal-body>
                    <div class="overflow-hidden rounded-kontrol border border-slate-200">
                        <div class="table-wrap">
                            <table class="table">
                                <thead><tr><th>Pembayaran</th><th class="text-right">Dibayar</th><th class="text-right">Refund</th></tr></thead>
                                <tbody>
                                    @forelse ($refund['baris'] as $b)
                                        <tr>
                                            <td class="wrap">{{ $b['label'] }}<span class="block text-xs text-slate-500">{{ $b['aturan'] }}</span></td>
                                            <td class="text-right tabular-nums">{{ rupiah($b['dibayar']) }}</td>
                                            <td class="text-right font-medium tabular-nums">{{ $b['refund'] === null ? '?' : rupiah($b['refund']) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-slate-500">Belum ada pembayaran.</td></tr>
                                    @endforelse
                                </tbody>
                                @if ($refund['baris'])
                                    <tfoot><tr><td>Total</td><td class="text-right tabular-nums">{{ rupiah($refund['total_bayar']) }}</td><td class="text-right tabular-nums">{{ $refund['total_refund'] === null ? '?' : rupiah($refund['total_refund']) }}</td></tr></tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                    @unless ($refund['lengkap'])
                        <div class="flex gap-2 rounded-kontrol bg-amber-50 px-3 py-2 text-sm text-amber-900">
                            <x-icon name="warning" class="size-5 text-amber-500"/>
                            <p>Aturan refund DP/angsuran belum diisi. <a href="{{ route('proyek.index') }}#grup-penjualan" class="font-medium underline">Buka Pengaturan</a></p>
                        </div>
                    @endunless
                    <x-field label="Tanggal Batal" name="tanggal_batal" required>
                        <x-input type="date" name="tanggal_batal" :value="now()->toDateString()" :max="now()->toDateString()"/>
                    </x-field>
                    <x-field label="Alasan" name="alasan" required><x-textarea name="alasan" rows="2"/></x-field>
                </x-modal-body>
                <x-modal-footer batal="Kembali">
                    <x-button type="submit" variant="danger" icon="ban" :disabled="! $refund['lengkap']">Batalkan Transaksi</x-button>
                </x-modal-footer>
            </form>
        </x-modal>
    @endif
@endif
</div>
@endsection
