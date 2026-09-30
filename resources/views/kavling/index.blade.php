@extends('layouts.app')
@section('title', 'Master Kavling')

@php
    $kosong = ['id' => null, 'blok' => '', 'nomor' => '', 'tipe' => 'Prima', 'ukuran' => '', 'luas' => '', 'catatan' => '', 'terkunci' => false];
    $awal = old('_form') === 'kavling'
        ? ['id' => old('_id'), 'blok' => old('blok'), 'nomor' => old('nomor'), 'tipe' => old('tipe'), 'ukuran' => old('ukuran'), 'luas' => old('luas'), 'catatan' => old('catatan'), 'terkunci' => (bool) old('_terkunci')]
        : $kosong;
@endphp

@section('content')
<div x-data="{
        form: @js($awal),
        kosong: @js($kosong),
        harga: {{ $hargaAktif }},
        get action() { return this.form.id ? '{{ url('master-kavling') }}/' + this.form.id : '{{ route('kavling.store') }}' },
        get estimasi() { return (Number(this.form.luas) || 0) * this.harga },
        tambah() { this.form = { ...this.kosong }; $dispatch('open-modal', 'kavling') },
        ubah(k) { this.form = { ...k }; $dispatch('open-modal', 'kavling') },
     }">

    <x-page-header title="Master Kavling" subtitle="Data kavling, status, dan harga jual. Status berubah otomatis mengikuti transaksi."
                   :breadcrumbs="['Data Master' => null, 'Master Kavling' => null]">
        <x-slot:actions>
            <button type="button" class="btn btn-primary" x-on:click="tambah()"><x-icon name="plus" class="h-4 w-4"/> Tambah Kavling</button>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6 sm:gap-4">
        <x-stat-card label="Total Kavling" :value="$total" tone="dark" icon="grid"/>
        @foreach (\App\Models\Kavling::STATUS as $s)
            <a href="{{ route('kavling.index', ['status' => $s]) }}" class="block transition hover:-translate-y-0.5">
                <x-stat-card :label="$s === 'dp' ? 'DP / Angsuran' : \App\Support\Status::label($s)" :value="$jumlah[$s] ?? 0"
                             :class="request('status') === $s ? 'ring-2 ring-forest-500' : ''"/>
            </a>
        @endforeach
    </div>

    <div class="mb-6 flex flex-col gap-3 rounded-xl border border-gold-200 bg-gold-50 px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gold-100 text-gold-700"><x-icon name="tag"/></div>
            <p class="text-gold-800">Harga aktif <span class="font-semibold">{{ $tahapAktif?->nama_tahap }}</span>:
                <span class="font-bold text-slate-900">{{ rupiah($hargaAktif) }}/m²</span>. Kavling yang sudah bertransaksi memakai harga terkunci.</p>
        </div>
        <a href="{{ route('skema-harga.index') }}" class="btn btn-sm btn-secondary">Lihat skema harga</a>
    </div>

    <div class="card">
        <x-filter-bar placeholder="Cari kode atau tipe…">
            <x-select name="blok" :options="$bloks->mapWithKeys(fn ($b) => [$b => 'Blok ' . $b])" :value="request('blok')" placeholder="Semua blok" class="sm:w-36"/>
            <x-select name="status" :options="collect(\App\Models\Kavling::STATUS)->mapWithKeys(fn ($s) => [$s => $s === 'dp' ? 'DP / Angsuran' : \App\Support\Status::label($s)])" :value="request('status')" placeholder="Semua status" class="sm:w-44"/>
        </x-filter-bar>

        @if ($kavlings->isEmpty())
            <x-empty-state title="Tidak ada kavling" message="Tidak ada kavling yang cocok dengan filter."/>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode</th><th>Tipe</th><th>Ukuran</th><th class="text-right">Luas</th>
                            <th class="text-right">Harga / m²</th><th class="text-right">Harga Jual</th>
                            <th>Status</th><th>Pembeli</th><th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kavlings as $k)
                            @php
                                $t = $k->transaksiAktif;
                                $hargaM2 = $t ? $t->harga_per_m2 : $k->harga_per_m2;
                                $hargaJual = $t ? $t->nilai_jual : $k->harga_jual;
                                $dataForm = ['id' => $k->id, 'blok' => $k->blok, 'nomor' => (int) substr($k->no, strlen($k->blok)), 'tipe' => $k->tipe, 'ukuran' => $k->ukuran, 'luas' => $k->luas ? (float) $k->luas : '', 'catatan' => $k->catatan, 'terkunci' => (bool) $t];
                            @endphp
                            <tr>
                                <td><a href="{{ route('kavling.show', $k) }}" class="font-semibold text-forest-700 hover:underline">{{ $k->kode_kavling }}</a></td>
                                <td>{{ $k->tipe }}</td>
                                <td class="whitespace-nowrap text-slate-500">{{ $k->ukuran ?: '—' }}</td>
                                <td class="text-right tabular-nums">{{ $k->luas ? angka($k->luas, 0) . ' m²' : '—' }}</td>
                                <td class="text-right tabular-nums">
                                    {{ $hargaM2 ? rupiah($hargaM2) : '—' }}
                                    @if ($t)<x-icon name="lock" class="ml-0.5 inline h-3.5 w-3.5 text-slate-400" title="Harga terkunci di transaksi"/>@endif
                                </td>
                                <td class="text-right font-medium tabular-nums">{{ $hargaJual ? rupiah($hargaJual) : 'Menunggu luas' }}</td>
                                <td><x-badge :status="$k->status" :label="$k->label_status"/></td>
                                <td>
                                    @if ($t)
                                        <a href="{{ route('transaksi-penjualan.show', $t) }}" class="hover:text-forest-700">
                                            <span class="block font-medium">{{ $t->konsumen->nama_lengkap }}</span>
                                            <span class="text-xs text-slate-500">{{ $t->kode_transaksi }}</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('kavling.show', $k) }}" class="btn-icon" title="Detail"><x-icon name="eye" class="h-[18px] w-[18px]"/></a>
                                        <button type="button" class="btn-icon" title="Ubah" x-on:click="ubah(@js($dataForm))"><x-icon name="pencil" class="h-[18px] w-[18px]"/></button>
                                        <x-delete-button :action="route('kavling.destroy', $k)" title="Hapus kavling {{ $k->kode_kavling }}?"
                                                         message="Kavling yang pernah bertransaksi tidak bisa dihapus."/>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Modal tambah / ubah --}}
    <x-modal name="kavling" max-width="lg" :show="old('_form') === 'kavling' && $errors->any()">
        <form method="POST" :action="action">
            @csrf
            <template x-if="form.id"><input type="hidden" name="_method" value="PUT"></template>
            <input type="hidden" name="_form" value="kavling">
            <input type="hidden" name="_id" :value="form.id">
            <input type="hidden" name="_terkunci" :value="form.terkunci ? 1 : ''">

            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h3 class="text-base font-semibold text-slate-900" x-text="form.id ? 'Ubah Kavling' : 'Tambah Kavling'"></h3>
                <button type="button" class="btn-icon -mr-2" x-on:click="$dispatch('close-modal', 'kavling')"><x-icon name="x"/></button>
            </div>

            <div class="space-y-4 p-5">
                <template x-if="form.terkunci">
                    <div class="flex gap-2 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
                        <x-icon name="lock" class="h-4 w-4"/> Kavling sedang bertransaksi: blok, nomor, dan luas dikunci.
                    </div>
                </template>
                <div class="grid grid-cols-2 gap-4">
                    <x-field label="Blok" name="blok" required>
                        <input type="text" name="blok" id="blok" x-model="form.blok" maxlength="3" :readonly="form.terkunci" class="form-input uppercase" placeholder="A">
                    </x-field>
                    <x-field label="Nomor" name="nomor" required>
                        <input type="number" name="nomor" id="nomor" x-model="form.nomor" min="1" max="99" :readonly="form.terkunci" class="form-input" placeholder="1">
                    </x-field>
                </div>
                <p class="-mt-2 text-xs text-slate-500">Kode otomatis:
                    <span class="font-semibold text-slate-700" x-text="'{{ \App\Services\Pengaturan::get('prefix_kavling', 'TR') }}-' + (form.blok || '?').toUpperCase() + String(form.nomor || 0).padStart(2, '0')"></span></p>

                <x-field label="Tipe" name="tipe" required>
                    <select name="tipe" id="tipe" x-model="form.tipe" class="form-input">
                        @foreach (\App\Models\Kavling::TIPE as $tipe)<option>{{ $tipe }}</option>@endforeach
                    </select>
                </x-field>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field label="Ukuran" name="ukuran" hint="Contoh: 7 x 14">
                        <input type="text" name="ukuran" id="ukuran" x-model="form.ukuran" class="form-input">
                    </x-field>
                    <x-field label="Luas (m²)" name="luas" hint="Kosongkan bila belum final">
                        <input type="number" step="0.01" min="1" name="luas" id="luas" x-model="form.luas" :readonly="form.terkunci" class="form-input">
                    </x-field>
                </div>
                <div class="rounded-lg bg-slate-50 px-4 py-3 text-sm" x-show="!form.terkunci">
                    <div class="flex justify-between"><span class="text-slate-500">Harga/m² (tahap aktif)</span><span class="font-medium" x-text="rupiah(harga)"></span></div>
                    <div class="mt-1 flex justify-between"><span class="text-slate-500">Estimasi harga jual</span><span class="font-semibold text-slate-900" x-text="estimasi ? rupiah(estimasi) : '—'"></span></div>
                </div>
                <x-field label="Catatan" name="catatan">
                    <textarea name="catatan" id="catatan" rows="2" x-model="form.catatan" class="form-input"></textarea>
                </x-field>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'kavling')">Batal</button>
                <button type="submit" class="btn btn-primary" x-text="form.id ? 'Simpan Perubahan' : 'Tambah Kavling'"></button>
            </div>
        </form>
    </x-modal>
</div>
@endsection
