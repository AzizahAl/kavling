@extends('layouts.app')
@section('title', 'RAB & Realisasi')

@php
    $kosong = ['id' => null, 'kategori' => '', 'pos' => '', 'uraian' => '', 'anggaran' => '', 'catatan' => ''];
    $awal = old('_form') === 'rab' ? array_merge($kosong, request()->old(), ['id' => old('_id')]) : $kosong;
    $persen = $totalAnggaran > 0 ? $totalRealisasi / $totalAnggaran * 100 : 0;
@endphp

@section('content')
<div x-data="{
        f: @js($awal), kosong: @js($kosong),
        get action() { return this.f.id ? '{{ url('rab-realisasi') }}/' + this.f.id : '{{ route('rab.store') }}' },
        bukaForm(d) { this.f = { ...d }; this.$dispatch('open-modal', 'rab'); this.$nextTick(() => this.$dispatch('set-money', { name: 'anggaran', value: this.f.anggaran })) },
     }">

<x-page-header title="RAB & Realisasi" subtitle="Anggaran per item. Realisasi dihitung dari pengeluaran Kas Proyek yang ditautkan ke item RAB."
               :breadcrumbs="['Keuangan' => null, 'RAB & Realisasi' => null]">
    <x-slot:actions>
        <a href="{{ route('kas-proyek.index', ['jenis' => 'keluar']) }}" class="btn btn-secondary"><x-icon name="wallet" class="h-4 w-4"/> Catat Pengeluaran</a>
        <button type="button" class="btn btn-primary" x-on:click="bukaForm(kosong)"><x-icon name="plus" class="h-4 w-4"/> Tambah Item</button>
    </x-slot:actions>
</x-page-header>

<div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4 sm:gap-4">
    <x-stat-card label="Total Anggaran" :value="rupiah($totalAnggaran)" icon="clipboard" tone="dark"/>
    <x-stat-card label="Total Realisasi" :value="rupiah($totalRealisasi)" :hint="angka($persen, 1) . '% dari anggaran'">
        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full {{ $persen > 100 ? 'bg-red-500' : 'bg-forest-500' }}" style="width: {{ min(100, $persen) }}%"></div></div>
    </x-stat-card>
    <x-stat-card label="Sisa Anggaran" :value="rupiah($totalAnggaran - $totalRealisasi)" :tone="$totalAnggaran - $totalRealisasi < 0 ? 'danger' : 'default'"/>
    <x-stat-card label="Belum Dianggarkan" :value="($jumlahStatus['belum_dianggarkan'] ?? 0) . ' item'" :hint="($jumlahStatus['melebihi'] ?? 0) . ' item melebihi anggaran'" tone="gold"/>
</div>

<div class="card">
    <x-filter-bar placeholder="Cari kategori atau uraian…">
        <x-select name="kategori" :options="$kategoriList->mapWithKeys(fn ($k) => [$k => $k])" :value="request('kategori')" placeholder="Semua kategori" class="sm:w-48"/>
        <x-select name="status" :options="collect(['belum_dianggarkan', 'berjalan', 'sesuai', 'melebihi'])->mapWithKeys(fn ($s) => [$s => \App\Support\Status::label($s)])" :value="request('status')" placeholder="Semua status" class="sm:w-48"/>
    </x-filter-bar>

    @if ($rabs->isEmpty())
        <x-empty-state title="Belum ada item RAB"/>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Kategori / Uraian</th><th>Pos Alokasi</th><th class="text-right">Anggaran</th><th class="text-right">Realisasi</th><th class="text-right">Selisih</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @foreach ($rabs as $r)
                        <tr x-data="{ rinci: false }">
                            <td class="wrap min-w-[220px]">
                                <span class="text-xs font-medium tracking-wide text-slate-500 uppercase">{{ $r->kategori }}</span>
                                <div class="font-medium text-slate-900">{{ $r->uraian }}</div>
                                @if ($r->kasKeluar->isNotEmpty())
                                    <button type="button" class="mt-1 text-xs font-medium text-forest-700 hover:underline" x-on:click="rinci = !rinci" x-text="rinci ? 'Sembunyikan rincian' : '{{ $r->kasKeluar->count() }} pengeluaran'"></button>
                                    <ul x-show="rinci" x-collapse class="mt-1 space-y-0.5 text-xs text-slate-500">
                                        @foreach ($r->kasKeluar as $k)<li>{{ tanggal($k->tanggal) }} · {{ $k->kode }} · {{ rupiah($k->nominal) }}</li>@endforeach
                                    </ul>
                                @endif
                            </td>
                            <td>{{ \App\Models\AlokasiKas::POS[$r->pos] ?? '—' }}</td>
                            <td class="text-right tabular-nums">{{ $r->anggaran !== null ? rupiah($r->anggaran) : '—' }}</td>
                            <td class="text-right tabular-nums">{{ rupiah($r->realisasi_nilai) }}</td>
                            <td @class(['text-right tabular-nums', 'text-red-600' => ($r->selisih ?? 0) < 0])>{{ $r->selisih !== null ? rupiah($r->selisih) : '—' }}</td>
                            <td><x-badge :status="$r->status"/></td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <button type="button" class="btn-icon" title="Ubah" x-on:click="bukaForm(@js(['id' => $r->id, 'kategori' => $r->kategori, 'pos' => $r->pos, 'uraian' => $r->uraian, 'anggaran' => $r->anggaran !== null ? (float) $r->anggaran : '', 'catatan' => $r->catatan]))"><x-icon name="pencil" class="h-[18px] w-[18px]"/></button>
                                    <x-delete-button :action="route('rab.destroy', $r)" title="Hapus item RAB?" message="Item yang sudah punya realisasi tidak bisa dihapus."/>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot><tr><td colspan="2">Total</td><td class="text-right">{{ rupiah($rabs->sum('anggaran')) }}</td><td class="text-right">{{ rupiah($rabs->sum('realisasi_nilai')) }}</td><td colspan="3"></td></tr></tfoot>
            </table>
        </div>
    @endif
</div>

<x-modal name="rab" max-width="lg" :show="old('_form') === 'rab' && $errors->any()">
    <form method="POST" :action="action">
        @csrf
        <template x-if="f.id"><input type="hidden" name="_method" value="PUT"></template>
        <input type="hidden" name="_form" value="rab"><input type="hidden" name="_id" :value="f.id">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-semibold text-slate-900" x-text="f.id ? 'Ubah Item RAB' : 'Tambah Item RAB'"></h3>
            <button type="button" class="btn-icon -mr-2" x-on:click="$dispatch('close-modal', 'rab')"><x-icon name="x"/></button>
        </div>
        <div class="grid gap-4 p-5 sm:grid-cols-2">
            <x-field label="Kategori" name="kategori" required>
                <input type="text" name="kategori" id="kategori" x-model="f.kategori" list="kategori-rab" class="form-input">
                <datalist id="kategori-rab">@foreach ($kategoriList as $k)<option value="{{ $k }}">@endforeach</datalist>
            </x-field>
            <x-field label="Pos Alokasi" name="pos" required>
                <select name="pos" id="pos" x-model="f.pos" class="form-input">
                    <option value="">— Pilih pos —</option>
                    @foreach (\App\Models\AlokasiKas::POS as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                </select>
            </x-field>
            <x-field label="Uraian" name="uraian" required class="sm:col-span-2">
                <input type="text" name="uraian" id="uraian" x-model="f.uraian" class="form-input">
            </x-field>
            <x-field label="Anggaran" name="anggaran" hint="Kosongkan bila belum dianggarkan" class="sm:col-span-2"><x-money name="anggaran"/></x-field>
            <x-field label="Catatan" name="catatan" class="sm:col-span-2">
                <input type="text" name="catatan" id="catatan" x-model="f.catatan" class="form-input">
            </x-field>
        </div>
        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'rab')">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</x-modal>
</div>
@endsection
