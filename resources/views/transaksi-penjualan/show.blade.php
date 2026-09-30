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

<x-page-header :title="'Transaksi ' . $t->kode_transaksi"
               :subtitle="$t->konsumen->nama_lengkap . ' · Kavling ' . $t->kavling->kode_kavling . ' · ' . tanggal($t->tanggal, 'j F Y')"
               :back="$admin ? route('transaksi-penjualan.index') : route('agen.show', $t->agen_id)"
               :breadcrumbs="$admin ? ['Transaksi Penjualan' => route('transaksi-penjualan.index'), $t->kode_transaksi => null] : []">
    <x-slot:actions>
        <x-badge :status="$t->status" class="px-3 py-1 text-sm"/>
        @if ($admin && ! $t->isBatal())
            <a href="{{ route('transaksi-penjualan.edit', $t) }}" class="btn btn-secondary"><x-icon name="pencil" class="h-4 w-4"/> Ubah</a>
            <button type="button" class="btn btn-secondary text-red-600 hover:bg-red-50" x-on:click="$dispatch('open-modal', 'batal')"><x-icon name="ban" class="h-4 w-4"/> Batalkan</button>
        @endif
        @if ($bisaBayar)
            <button type="button" class="btn btn-primary" x-on:click="baru()"><x-icon name="plus" class="h-4 w-4"/> Catat Pembayaran</button>
        @endif
    </x-slot:actions>
</x-page-header>

@if ($t->isBatal())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        <p class="font-semibold">Transaksi dibatalkan pada {{ tanggal($t->tanggal_batal, 'j F Y') }}</p>
        <p class="mt-1">Alasan: {{ $t->alasan_batal }}</p>
        @foreach ($t->kasRefunds as $r)
            <p class="mt-1">Refund {{ rupiah($r->nominal) }} tercatat di kas ({{ $r->kode }}). {{ $r->catatan }}</p>
        @endforeach
        @if ($t->kasRefunds->isEmpty())<p class="mt-1">Tidak ada refund.</p>@endif
    </div>
@endif

<div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 sm:gap-4">
    <x-stat-card label="Harga Jual (terkunci)" :value="rupiah($t->nilai_jual)" :hint="angka($t->luas) . ' m² × ' . rupiah($t->harga_per_m2)" tone="dark" icon="lock"/>
    <x-stat-card label="Pokok Terbayar" :value="rupiah($t->pokokTerbayar())" :hint="angka($t->persenLunas(), 1) . '% dari harga'" icon="check-circle">
        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-forest-500" style="width: {{ $t->persenLunas() }}%"></div></div>
    </x-stat-card>
    <x-stat-card label="Sisa Pembayaran" :value="rupiah($t->sisa())" :tone="$angsuran['jumlah_telat'] ? 'danger' : 'default'"
                 :hint="$angsuran['jumlah_telat'] ? $angsuran['jumlah_telat'] . ' cicilan terlambat · ' . rupiah($angsuran['tunggakan']) : null" icon="wallet"/>
    <x-stat-card label="Total Uang Masuk" :value="rupiah($t->totalMasuk())" hint="Termasuk reservasi & booking" tone="gold" icon="banknotes"/>
</div>

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        {{-- Riwayat pembayaran --}}
        <x-card title="Riwayat Pembayaran" subtitle="Setiap pembayaran otomatis tercatat sebagai kas masuk." :padding="false">
            @if ($t->pembayarans->isEmpty())
                <x-empty-state title="Belum ada pembayaran" message="Catat reservasi, booking, DP, atau angsuran pertama.">
                    @if ($bisaBayar)<button type="button" class="btn btn-primary" x-on:click="baru()"><x-icon name="plus" class="h-4 w-4"/> Catat Pembayaran</button>@endif
                </x-empty-state>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>No. Kwitansi</th><th>Tanggal</th><th>Jenis</th><th>Metode</th><th class="text-right">Nominal</th><th>Kas</th><th class="text-right">Aksi</th></tr></thead>
                        <tbody>
                            @foreach ($t->pembayarans as $p)
                                <tr>
                                    <td class="font-medium">{{ $p->kode }}</td>
                                    <td class="whitespace-nowrap">{{ tanggal($p->tanggal) }}</td>
                                    <td>{{ $p->label_jenis }} @if (in_array($p->jenis, ['reservasi', 'booking']))<span class="text-xs text-slate-400">(di luar harga)</span>@endif</td>
                                    <td>{{ \App\Models\Pembayaran::METODE[$p->metode] }}@if ($p->no_bukti)<span class="block text-xs text-slate-500">{{ $p->no_bukti }}</span>@endif</td>
                                    <td class="text-right font-medium tabular-nums">{{ rupiah($p->nominal) }}</td>
                                    <td class="text-xs text-slate-500">{{ $p->kas->kode ?? '—' }}</td>
                                    <td>
                                        <div class="flex justify-end gap-1">
                                            @if ($admin)
                                            <a href="{{ route('pembayaran.kwitansi', $p) }}" class="btn-icon" title="Kwitansi"><x-icon name="receipt" class="h-[18px] w-[18px]"/></a>
                                            @unless ($t->isBatal())
                                                <button type="button" class="btn-icon" title="Ubah"
                                                        x-on:click="ubah(@js(['id' => $p->id, 'tanggal' => $p->tanggal->toDateString(), 'jenis' => $p->jenis, 'nominal' => (float) $p->nominal, 'metode' => $p->metode, 'no_bukti' => $p->no_bukti, 'catatan' => $p->catatan]))"><x-icon name="pencil" class="h-[18px] w-[18px]"/></button>
                                                <x-delete-button :action="route('pembayaran.destroy', [$t, $p])" title="Hapus pembayaran {{ $p->kode }}?"
                                                                 message="Catatan kas masuk untuk pembayaran ini juga akan dihapus, dan status transaksi dihitung ulang."/>
                                            @endunless
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot><tr><td colspan="4">Total</td><td class="text-right tabular-nums">{{ rupiah($t->totalMasuk()) }}</td><td colspan="2"></td></tr></tfoot>
                    </table>
                </div>
            @endif
        </x-card>

        {{-- Jadwal angsuran --}}
        @if ($t->isAngsuran() && $jadwal->isNotEmpty())
            <x-card title="Jadwal Angsuran" :subtitle="$angsuran['cicilan_lunas'] . ' dari ' . $jadwal->count() . ' cicilan lunas · ' . rupiah($cicilan) . '/bulan'" :padding="false">
                <div class="table-wrap max-h-[480px] overflow-y-auto">
                    <table class="table">
                        <thead class="sticky top-0"><tr><th>Ke</th><th>Jatuh Tempo</th><th class="text-right">Cicilan</th><th class="text-right">Terbayar</th><th class="text-right">Sisa</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach ($jadwal as $j)
                                <tr @class(['bg-red-50/50' => $j->status === 'terlambat'])>
                                    <td class="font-medium">{{ $j->ke }}</td>
                                    <td class="whitespace-nowrap">{{ tanggal($j->jatuh_tempo) }}</td>
                                    <td class="text-right tabular-nums">{{ rupiah($j->nominal) }}</td>
                                    <td class="text-right tabular-nums">{{ $j->terbayar ? rupiah($j->terbayar) : '—' }}</td>
                                    <td class="text-right tabular-nums">{{ $j->sisa ? rupiah($j->sisa) : '—' }}</td>
                                    <td>
                                        <x-badge :status="$j->status"/>
                                        @if ($j->hari_telat)<span class="ml-1 text-xs font-medium text-red-600">{{ $j->hari_telat }} hari</span>@endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif
    </div>

    <div class="space-y-6">
        <x-card title="Rincian Transaksi">
            <dl class="space-y-3 text-sm">
                @foreach ([
                    ['Konsumen', ($admin ? '<a href="' . route('konsumen.show', $t->konsumen) . '" class="text-forest-700 hover:underline">' . e($t->konsumen->nama_lengkap) . '</a>' : e($t->konsumen->nama_lengkap)) . '<span class="block text-xs text-slate-500">' . e($t->konsumen->id_konsumen . ' · ' . $t->konsumen->no_hp) . '</span>'],
                    ['Kavling', ($admin ? '<a href="' . route('kavling.show', $t->kavling) . '" class="text-forest-700 hover:underline">' . e($t->kavling->kode_kavling) . '</a>' : e($t->kavling->kode_kavling)) . ' · ' . e($t->kavling->tipe) . ' · ' . angka($t->luas) . ' m²'],
                    ['Tahap Harga', e(($t->tahap->nama_tahap ?? '—') . ' · ' . rupiah($t->harga_per_m2) . '/m²')],
                    ['Agen', e($t->agen->nama_agen ?? '—')],
                    ['Metode', $t->isAngsuran() ? 'Angsuran ' . $t->tenor . ' bulan' : 'Cash'],
                    ['DP Disepakati', rupiah($t->nominal_dp) . ($t->nilai_jual > 0 ? ' (' . angka($t->nominal_dp / $t->nilai_jual * 100, 1) . '%)' : '')],
                    ['Biaya di Luar Harga', 'Reservasi ' . rupiah($t->biaya_reservasi) . ' · Booking ' . rupiah($t->biaya_booking)],
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
                    ['Down Payment', $t->terbayarJenis('dp'), (float) $t->nominal_dp],
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
                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-forest-500" style="width: {{ min(100, $bayar / $target * 100) }}%"></div></div>
                            <p class="mt-1 text-xs text-slate-500 tabular-nums">{{ rupiah($bayar) }} / {{ rupiah($target) }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        </x-card>

        <x-card title="Checklist Legal">
            <ul class="space-y-2 text-sm">
                @foreach (\App\Models\ChecklistLegal::ITEM as $kunci => $label)
                    <li class="flex items-center justify-between">
                        <span class="text-slate-700">{{ $label }}</span>
                        <span class="flex items-center gap-2">
                            @if ($t->checklist?->{$kunci . '_tanggal'})<span class="text-xs text-slate-500">{{ tanggal($t->checklist->{$kunci . '_tanggal'}) }}</span>@endif
                            <x-badge :status="$t->checklist?->{$kunci . '_status'} ?? 'belum'"/>
                        </span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-3 text-xs text-slate-500">Kavling berstatus Terjual setelah PPJB ditandai selesai (ditandatangani).</p>
            @if ($admin && ! $t->isBatal())
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="{{ route('dokumen.lihat', [$t, 'spk']) }}" class="btn btn-sm btn-secondary"><x-icon name="document" class="h-4 w-4"/> SPK</a>
                    <a href="{{ route('dokumen.lihat', [$t, 'ppjb']) }}" class="btn btn-sm btn-secondary"><x-icon name="document" class="h-4 w-4"/> PPJB</a>
                    <a href="{{ route('legal.index') }}" class="btn btn-sm btn-ghost col-span-2">Ubah checklist legal</a>
                </div>
            @endif
            @if ($t->lead)
                <p class="mt-3 text-xs text-slate-500">Closing dari lead <a href="{{ route('lead.index', ['cari' => $t->lead->kode]) }}" class="font-medium text-forest-700 hover:underline">{{ $t->lead->kode }}</a>.</p>
            @endif
        </x-card>
    </div>
</div>

@if ($admin)
{{-- Modal catat / ubah pembayaran --}}
<x-modal name="bayar" max-width="lg" :show="old('_form') === 'bayar' && $errors->any()">
    <form method="POST" :action="action">
        @csrf
        <template x-if="f.id"><input type="hidden" name="_method" value="PUT"></template>
        <input type="hidden" name="_form" value="bayar">
        <input type="hidden" name="_id" :value="f.id">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-semibold text-slate-900" x-text="f.id ? 'Ubah Pembayaran' : 'Catat Pembayaran'"></h3>
            <button type="button" class="btn-icon -mr-2" x-on:click="$dispatch('close-modal', 'bayar')"><x-icon name="x"/></button>
        </div>
        <div class="space-y-4 p-5">
            <div class="grid grid-cols-2 gap-3 rounded-lg bg-slate-50 p-3 text-xs">
                <div><span class="text-slate-500">Sisa harga</span><p class="font-semibold text-slate-800">{{ rupiah($t->sisa()) }}</p></div>
                @if ($angsuran['berikutnya'])
                    <div><span class="text-slate-500">Cicilan berikutnya (ke-{{ $angsuran['berikutnya']->ke }})</span><p class="font-semibold text-slate-800">{{ rupiah($angsuran['berikutnya']->sisa) }} · {{ tanggal($angsuran['berikutnya']->jatuh_tempo) }}</p></div>
                @endif
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Jenis" name="jenis" required>
                    <select name="jenis" id="jenis" x-model="f.jenis" x-on:change="pilihJenis()" class="form-input">
                        @foreach (\App\Models\Pembayaran::JENIS as $v => $l)
                            @continue($v === 'angsuran' && ! $t->isAngsuran())
                            <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </x-field>
                <x-field label="Tanggal Bayar" name="tanggal" required>
                    <input type="date" name="tanggal" id="tanggal" x-model="f.tanggal" max="{{ now()->toDateString() }}" class="form-input">
                </x-field>
            </div>
            <x-field label="Nominal" name="nominal" required>
                <x-money name="nominal"/>
            </x-field>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Metode" name="metode" required>
                    <select name="metode" id="metode" x-model="f.metode" class="form-input">
                        @foreach (\App\Models\Pembayaran::METODE as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                    </select>
                </x-field>
                <x-field label="No. Bukti / Referensi" name="no_bukti">
                    <input type="text" name="no_bukti" id="no_bukti" x-model="f.no_bukti" class="form-input">
                </x-field>
            </div>
            <x-field label="Catatan" name="catatan">
                <input type="text" name="catatan" id="catatan" x-model="f.catatan" class="form-input">
            </x-field>
        </div>
        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'bayar')">Batal</button>
            <button type="submit" class="btn btn-primary" x-text="f.id ? 'Simpan Perubahan' : 'Simpan Pembayaran'"></button>
        </div>
    </form>
</x-modal>

{{-- Modal pembatalan --}}
@if ($refund)
    <x-modal name="batal" title="Batalkan Transaksi {{ $t->kode_transaksi }}" max-width="lg" :show="$errors->has('alasan') || $errors->has('tanggal_batal')">
        <form method="POST" action="{{ route('transaksi-penjualan.batal', $t) }}"
              data-confirm="Transaksi akan dibatalkan, kavling kembali tersedia, dan refund dicatat sebagai kas keluar. Tindakan ini tidak bisa diurungkan."
              data-confirm-title="Yakin batalkan transaksi?" data-confirm-ok="Ya, batalkan">
            @csrf
            <div class="space-y-4 p-5">
                <div class="overflow-hidden rounded-lg border border-slate-200">
                    <table class="table">
                        <thead><tr><th>Pembayaran</th><th class="text-right">Dibayar</th><th class="text-right">Refund</th></tr></thead>
                        <tbody>
                            @forelse ($refund['baris'] as $b)
                                <tr>
                                    <td>{{ $b['label'] }}<span class="block text-xs text-slate-500">{{ $b['aturan'] }}</span></td>
                                    <td class="text-right tabular-nums">{{ rupiah($b['dibayar']) }}</td>
                                    <td class="text-right font-medium tabular-nums">{{ $b['refund'] === null ? '?' : rupiah($b['refund']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-slate-500">Belum ada pembayaran, tidak ada refund.</td></tr>
                            @endforelse
                        </tbody>
                        @if ($refund['baris'])
                            <tfoot><tr><td>Total</td><td class="text-right tabular-nums">{{ rupiah($refund['total_bayar']) }}</td><td class="text-right tabular-nums">{{ $refund['total_refund'] === null ? '?' : rupiah($refund['total_refund']) }}</td></tr></tfoot>
                        @endif
                    </table>
                </div>
                @unless ($refund['lengkap'])
                    <div class="flex gap-2 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">
                        <x-icon name="warning" class="h-5 w-5"/>
                        <p>Aturan refund DP/angsuran belum diisi. <a href="{{ route('proyek.index') }}#grup-penjualan" class="font-medium underline">Lengkapi di Pengaturan Proyek</a> sebelum membatalkan.</p>
                    </div>
                @endunless
                <x-field label="Tanggal Pembatalan" name="tanggal_batal" required>
                    <x-input type="date" name="tanggal_batal" :value="now()->toDateString()" :max="now()->toDateString()"/>
                </x-field>
                <x-field label="Alasan Pembatalan" name="alasan" required>
                    <x-textarea name="alasan" rows="2"/>
                </x-field>
            </div>
            <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'batal')">Kembali</button>
                <button type="submit" class="btn btn-danger" @disabled(! $refund['lengkap'])><x-icon name="ban" class="h-4 w-4"/> Batalkan Transaksi</button>
            </div>
        </form>
    </x-modal>
@endif
@endif
</div>
@endsection
