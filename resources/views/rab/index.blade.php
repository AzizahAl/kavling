@extends('layouts.app')
@section('title', 'RAB & Realisasi')

@php
    $kosong = ['id' => null, 'kategori' => '', 'pos' => '', 'uraian' => '', 'anggaran' => '', 'catatan' => ''];
    $awal = old('_form') === 'rab' ? array_merge($kosong, request()->old(), ['id' => old('_id')]) : $kosong;
    // Popup "Catat Realisasi" = pengeluaran Kas Proyek yang ditautkan ke item RAB (route kas-proyek.store)
    $realisasiAwal = old('_form') === 'realisasi'
        ? ['rab_id' => old('rab_id'), 'kategori' => old('kategori'), 'uraian' => old('uraian'), 'item' => old('_item'), 'tanggal' => old('tanggal'), 'sumber' => old('sumber'), 'catatan' => old('catatan'), 'sisa' => old('_sisa')]
        : ['rab_id' => null, 'kategori' => '', 'uraian' => '', 'item' => '', 'tanggal' => now()->toDateString(), 'sumber' => '', 'catatan' => '', 'sisa' => null];
    $persen = $totalAnggaran > 0 ? $totalRealisasi / $totalAnggaran * 100 : 0;
    $dataRab = fn ($r) => ['id' => $r->id, 'kategori' => $r->kategori, 'pos' => $r->pos, 'uraian' => $r->uraian, 'anggaran' => $r->anggaran !== null ? (float) $r->anggaran : '', 'catatan' => $r->catatan];
    $dataRealisasi = fn ($r) => ['rab_id' => $r->id, 'kategori' => $r->kategori, 'uraian' => $r->uraian, 'item' => $r->kategori . ' · ' . $r->uraian, 'tanggal' => now()->toDateString(), 'sumber' => '', 'catatan' => '', 'sisa' => $r->selisih];
    $artiStatus = [
        'belum_dianggarkan' => 'Anggaran belum diisi',
        'berjalan'  => 'Realisasi masih di bawah anggaran',
        'sesuai'    => 'Realisasi sama dengan anggaran',
        'melebihi'  => 'Realisasi melebihi anggaran',
    ];
@endphp

@section('content')
<div x-data="{
        f: @js($awal), kosong: @js($kosong), r: @js($realisasiAwal),
        get action() { return this.f.id ? '{{ url('rab-realisasi') }}/' + this.f.id : '{{ route('rab.store') }}' },
        bukaForm(d) { this.f = { ...d }; this.$dispatch('open-modal', 'rab'); this.$nextTick(() => this.$dispatch('set-money', { name: 'anggaran', value: this.f.anggaran })) },
        bukaRealisasi(d) { this.r = { ...d }; this.$dispatch('open-modal', 'realisasi'); this.$nextTick(() => this.$dispatch('set-money', { name: 'nominal', value: '' })) },
     }">

<x-page-header title="RAB & Realisasi" subtitle="Status dihitung otomatis dari anggaran dan realisasi." :breadcrumbs="['Keuangan' => null, 'RAB & Realisasi' => null]">
    <x-slot:actions>
        <x-button variant="secondary" icon="wallet" :href="route('kas-proyek.index', ['jenis' => 'keluar'])">Pengeluaran</x-button>
        <x-button icon="plus" x-on:click="bukaForm(kosong)">Tambah Item</x-button>
    </x-slot:actions>
</x-page-header>

<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <x-stat-card label="Anggaran" :value="rupiah($totalAnggaran)" :singkat="rupiah_singkat($totalAnggaran)" icon="clipboard" tone="utama"/>
    <x-stat-card label="Realisasi" :value="rupiah($totalRealisasi)" :singkat="rupiah_singkat($totalRealisasi)" :hint="angka($persen, 1) . '% dari anggaran'">
        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full {{ $persen > 100 ? 'bg-red-500' : 'bg-brand-500' }}" style="width: {{ min(100, $persen) }}%"></div></div>
    </x-stat-card>
    <x-stat-card label="Sisa Anggaran" :value="rupiah($totalAnggaran - $totalRealisasi)" :singkat="rupiah_singkat($totalAnggaran - $totalRealisasi)" :tone="$totalAnggaran - $totalRealisasi < 0 ? 'bahaya' : 'default'"/>
    <x-stat-card label="Belum Dianggarkan" :value="($jumlahStatus['belum_dianggarkan'] ?? 0) . ' item'" :hint="($jumlahStatus['melebihi'] ?? 0) . ' melebihi anggaran'"/>
</div>

{{-- Cara status berubah --}}
<div class="mb-5 rounded-kartu border border-slate-200 bg-white p-4 shadow-kartu" x-data="{ info: true }">
    <div class="flex items-start gap-3">
        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600"><x-icon name="info" class="size-4"/></span>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-slate-900">Cara mengubah status</p>
            <ol class="mt-1 space-y-0.5 text-sm text-slate-600">
                <li><span class="font-medium text-slate-800">1.</span> Isi <span class="font-medium">anggaran</span> lewat tombol <x-icon name="pencil" class="inline size-3.5 align-[-2px]"/> Ubah.</li>
                <li><span class="font-medium text-slate-800">2.</span> Catat pengeluaran lewat tombol <span class="inline-flex items-center gap-1 font-medium text-brand-700"><x-icon name="banknotes" class="size-3.5"/>Catat Realisasi</span>. Status berubah otomatis.</li>
            </ol>
            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-slate-500">
                @foreach ($artiStatus as $s => $arti)
                    <span class="inline-flex items-center gap-1.5"><x-badge :status="$s"/>{{ $arti }}</span>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="card">
    <x-filter-bar placeholder="Cari kategori atau uraian…">
        <x-select name="kategori" :options="$kategoriList->mapWithKeys(fn ($k) => [$k => $k])" :value="request('kategori')" placeholder="Semua kategori"/>
        <x-select name="status" :options="collect(array_keys($artiStatus))->mapWithKeys(fn ($s) => [$s => \App\Support\Status::label($s)])" :value="request('status')" placeholder="Semua status"/>
    </x-filter-bar>

    @if ($rabs->isEmpty())
        <x-empty-state title="Tidak ada item RAB" :message="request()->query() ? 'Ubah atau hapus filter.' : null"/>
    @else
        <div class="table-wrap max-md:hidden">
            <table class="table">
                <thead><tr><th>Uraian</th><th class="text-right">Anggaran</th><th class="text-right">Realisasi</th><th class="text-right">Selisih</th><th>Status</th><th class="w-px"></th></tr></thead>
                <tbody>
                    @foreach ($rabs as $r)
                        <tr x-data="{ rinci: false }">
                            <td class="wrap min-w-[220px]">
                                <span class="text-xs font-medium text-slate-500">{{ $r->kategori }} · {{ \App\Models\AlokasiKas::POS[$r->pos] ?? '—' }}</span>
                                <div class="font-medium text-slate-900">{{ $r->uraian }}</div>
                                @include('rab._rincian')
                            </td>
                            <td class="text-right tabular-nums">{{ $r->anggaran !== null ? rupiah($r->anggaran) : '—' }}</td>
                            <td class="text-right tabular-nums">{{ rupiah($r->realisasi_nilai) }}</td>
                            <td @class(['text-right tabular-nums', 'text-red-600' => ($r->selisih ?? 0) < 0])>{{ $r->selisih !== null ? rupiah($r->selisih) : '—' }}</td>
                            <td><span x-data x-tip="{{ Js::from($artiStatus[$r->status]) }}" tabindex="0"><x-badge :status="$r->status"/></span></td>
                            <td>@include('rab._aksi')</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot><tr><td>Total</td><td class="text-right tabular-nums">{{ rupiah($rabs->sum('anggaran')) }}</td><td class="text-right tabular-nums">{{ rupiah($rabs->sum('realisasi_nilai')) }}</td><td colspan="3"></td></tr></tfoot>
            </table>
        </div>

        <ul class="divide-y divide-slate-100 md:hidden">
            @foreach ($rabs as $r)
                <li class="px-4 py-3.5" x-data="{ rinci: false }">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500">{{ $r->kategori }}</p>
                            <p class="font-medium text-slate-900">{{ $r->uraian }}</p>
                        </div>
                        <x-badge :status="$r->status"/>
                    </div>
                    <dl class="mt-2 grid grid-cols-2 gap-2 text-xs">
                        <div><dt class="text-slate-500">Anggaran</dt><dd class="font-medium tabular-nums">{{ $r->anggaran !== null ? rupiah($r->anggaran) : '—' }}</dd></div>
                        <div><dt class="text-slate-500">Realisasi</dt><dd class="font-medium tabular-nums">{{ rupiah($r->realisasi_nilai) }}</dd></div>
                    </dl>
                    @include('rab._rincian')
                    <div class="mt-2">@include('rab._aksi')</div>
                </li>
            @endforeach
        </ul>
    @endif
</div>

{{-- Popup tambah / ubah item --}}
<x-modal name="rab" judul-js="f.id ? 'Ubah Item RAB' : 'Tambah Item RAB'" :show="old('_form') === 'rab' && $errors->any()">
    <form method="POST" :action="action" class="flex min-h-0 flex-1 flex-col">
        @csrf
        <template x-if="f.id"><input type="hidden" name="_method" value="PUT"></template>
        <input type="hidden" name="_form" value="rab"><input type="hidden" name="_id" :value="f.id">
        <x-modal-body>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Kategori" name="kategori" required>
                    <x-input name="kategori" x-model="f.kategori" list="kategori-rab"/>
                    <datalist id="kategori-rab">@foreach ($kategoriList as $k)<option value="{{ $k }}">@endforeach</datalist>
                </x-field>
                <x-field label="Pos Alokasi" name="pos" required>
                    <x-select name="pos" :options="\App\Models\AlokasiKas::POS" placeholder="Pilih pos" x-model="f.pos"/>
                </x-field>
            </div>
            <x-field label="Uraian" name="uraian" required><x-input name="uraian" x-model="f.uraian"/></x-field>
            <x-field label="Anggaran" name="anggaran" hint="Kosong = Belum Dianggarkan"><x-money name="anggaran"/></x-field>
            <x-field label="Catatan" name="catatan"><x-input name="catatan" x-model="f.catatan"/></x-field>
        </x-modal-body>
        <x-modal-footer><x-button type="submit">Simpan</x-button></x-modal-footer>
    </form>
</x-modal>

{{-- Popup catat realisasi (pengeluaran kas yang ditautkan ke item RAB) --}}
<x-modal name="realisasi" title="Catat Realisasi" :show="old('_form') === 'realisasi' && $errors->any()">
    <form method="POST" action="{{ route('kas-proyek.store') }}" class="flex min-h-0 flex-1 flex-col">
        @csrf
        <input type="hidden" name="_form" value="realisasi">
        <input type="hidden" name="jenis" value="keluar">
        <input type="hidden" name="rab_id" :value="r.rab_id">
        <input type="hidden" name="kategori" :value="r.kategori">
        <input type="hidden" name="_item" :value="r.item">
        <input type="hidden" name="_sisa" :value="r.sisa">
        <x-modal-body>
            <div class="rounded-kontrol bg-slate-50 px-3 py-2.5 text-sm">
                <p class="text-xs text-slate-500">Item RAB</p>
                <p class="font-medium text-slate-900" x-text="r.item"></p>
                <p class="mt-0.5 text-xs text-slate-500" x-show="r.sisa !== null && r.sisa !== ''">Sisa anggaran <span class="font-medium text-slate-700" x-text="rupiah(r.sisa)"></span></p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Tanggal" name="tanggal" required><x-input type="date" name="tanggal" x-model="r.tanggal" :max="now()->toDateString()"/></x-field>
                <x-field label="Nominal" name="nominal" required><x-money name="nominal"/></x-field>
            </div>
            <x-field label="Uraian" name="uraian" required><x-input name="uraian" id="uraian_realisasi" x-model="r.uraian"/></x-field>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Penerima" name="sumber"><x-input name="sumber" x-model="r.sumber"/></x-field>
                <x-field label="Catatan" name="catatan"><x-input name="catatan" id="catatan_realisasi" x-model="r.catatan"/></x-field>
            </div>
            <p class="flex gap-2 text-xs text-slate-500"><x-icon name="info" class="size-4"/>Tercatat sebagai pengeluaran di Kas Proyek.</p>
        </x-modal-body>
        <x-modal-footer><x-button type="submit" icon="check">Simpan Realisasi</x-button></x-modal-footer>
    </form>
</x-modal>
</div>
@endsection
