@extends('layouts.app')
@section('title', 'Skema Harga')

@php
    $kosong = ['id' => null, 'nama_tahap' => 'Tahap ' . ($tahap->count() + 1), 'unit_mulai' => $tahap->isEmpty() ? 0 : $tahap->max('unit_sampai') + 1, 'unit_sampai' => '', 'harga_per_m2' => ''];
    $awal = old('_form') === 'skema'
        ? ['id' => old('_id'), 'nama_tahap' => old('nama_tahap'), 'unit_mulai' => old('unit_mulai'), 'unit_sampai' => old('unit_sampai'), 'harga_per_m2' => old('harga_per_m2')]
        : $kosong;
@endphp

@section('content')
<div x-data="{
        f: @js($awal), kosong: @js($kosong), status: '',
        get action() { return this.f.id ? '{{ url('skema-harga') }}/' + this.f.id : '{{ route('skema-harga.store') }}' },
        buka(d) { this.f = { ...d }; this.$dispatch('open-modal', 'skema'); this.$nextTick(() => this.$dispatch('set-money', { name: 'harga_per_m2', value: this.f.harga_per_m2 })) },
     }">

<x-page-header title="Skema Harga" subtitle="Tahap aktif mengikuti jumlah kavling yang sudah bertransaksi." :breadcrumbs="['Data Master' => null, 'Skema Harga' => null]">
    <x-slot:actions>
        <x-button icon="plus" x-on:click="buka(kosong)">Tambah Skema</x-button>
    </x-slot:actions>
</x-page-header>

<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <x-stat-card label="Harga Aktif/m²" :value="rupiah($hargaAktif)" :hint="$tahap[$aktif - 1]->nama_tahap ?? 'Belum ada tahap'" tone="utama" icon="tag"/>
    <x-stat-card label="Bertransaksi" :value="$bertransaksi . ' / ' . $totalKavling" icon="check-badge"/>
    <x-stat-card label="Menuju Kenaikan" :value="$menujuNaik !== null ? $menujuNaik . ' unit' : 'Tahap akhir'" icon="trending-up"/>
    <x-stat-card label="Harga Tertinggi/m²" :value="rupiah($tahap->max('harga_per_m2') ?? 0)" icon="chart"/>
</div>

<div class="card">
    {{-- Filter sederhana di sisi browser (data tahap sedikit & tidak berhalaman) --}}
    <div class="flex flex-col gap-2.5 border-b border-slate-100 p-3 sm:flex-row sm:items-center sm:p-4">
        <h2 class="card-title flex-1">Tahap Harga</h2>
        <x-select name="filter_status" :options="['aktif' => 'Aktif', 'terlewati' => 'Terlewati', 'menunggu' => 'Menunggu']" placeholder="Semua status" x-model="status" class="sm:w-48"/>
    </div>
    @if ($tahap->isEmpty())
        <x-empty-state title="Belum ada tahap harga" message="Tambah skema agar kavling memiliki harga."/>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Tahap</th><th>Unit Bertransaksi</th><th class="text-right">Harga/m²</th><th class="text-right">Transaksi</th><th>Status</th><th class="w-px"></th></tr></thead>
                <tbody>
                    @foreach ($tahap as $i => $t)
                        @php
                            $nomor = $i + 1;
                            $st = $nomor === $aktif ? 'aktif' : ($nomor < $aktif ? 'terlewati' : 'menunggu');
                            $dataForm = ['id' => $t->id, 'nama_tahap' => $t->nama_tahap, 'unit_mulai' => $t->unit_mulai, 'unit_sampai' => $t->unit_sampai, 'harga_per_m2' => (int) $t->harga_per_m2];
                        @endphp
                        <tr x-show="!status || status === '{{ $st }}'" @class(['bg-brand-50/50' => $st === 'aktif'])>
                            <td class="font-medium text-slate-900">{{ $t->nama_tahap }}</td>
                            <td>{{ $t->unit_mulai }}–{{ $t->unit_sampai }} unit</td>
                            <td class="text-right font-medium tabular-nums">{{ rupiah($t->harga_per_m2) }}</td>
                            <td class="text-right tabular-nums">{{ $perTahap[$t->id] ?? 0 }}</td>
                            <td>
                                @if ($st === 'aktif')<x-badge status="aktif"/>
                                @elseif ($st === 'terlewati')<x-badge status="menunggu" label="Terlewati"/>
                                @else<x-badge status="menunggu"/>@endif
                            </td>
                            <td>
                                <div class="flex justify-end gap-0.5">
                                    <x-icon-button icon="pencil" label="Ubah" x-on:click="buka({{ Js::from($dataForm) }})"/>
                                    <x-delete-button :action="route('skema-harga.destroy', $t)" :title="'Hapus ' . $t->nama_tahap . '?'" message="Tahap yang sudah dipakai transaksi tidak bisa dihapus."/>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Popup tambah / ubah --}}
<x-modal name="skema" judul-js="f.id ? 'Ubah Skema' : 'Tambah Skema'" :show="old('_form') === 'skema' && $errors->any()">
    <form method="POST" :action="action" class="flex min-h-0 flex-1 flex-col">
        @csrf
        <template x-if="f.id"><input type="hidden" name="_method" value="PUT"></template>
        <input type="hidden" name="_form" value="skema">
        <input type="hidden" name="_id" :value="f.id">

        <x-modal-body>
            <x-field label="Tahap" name="nama_tahap" required>
                <x-input name="nama_tahap" x-model="f.nama_tahap" placeholder="Tahap 1"/>
            </x-field>
            <div class="grid grid-cols-2 gap-4">
                <x-field label="Unit Mulai" name="unit_mulai" required>
                    <x-input name="unit_mulai" inputmode="numeric" x-model="f.unit_mulai" class="tabular-nums"/>
                </x-field>
                <x-field label="Unit Sampai" name="unit_sampai" required>
                    <x-input name="unit_sampai" inputmode="numeric" x-model="f.unit_sampai" class="tabular-nums"/>
                </x-field>
            </div>
            <x-field label="Harga/m²" name="harga_per_m2" required>
                <x-money name="harga_per_m2"/>
            </x-field>
        </x-modal-body>
        <x-modal-footer>
            <button type="submit" class="btn btn-primary" x-text="f.id ? 'Simpan' : 'Tambah'"></button>
        </x-modal-footer>
    </form>
</x-modal>
</div>
@endsection
