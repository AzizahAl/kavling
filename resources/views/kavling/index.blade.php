@extends('layouts.app')
@section('title', 'Kavling')

@php
    $kosong = ['id' => null, 'blok' => '', 'nomor' => '', 'tipe' => 'Prima', 'ukuran' => '', 'luas' => '', 'catatan' => '', 'terkunci' => false];
    $awal = old('_form') === 'kavling'
        ? ['id' => old('_id'), 'blok' => old('blok'), 'nomor' => old('nomor'), 'tipe' => old('tipe'), 'ukuran' => old('ukuran'), 'luas' => old('luas'), 'catatan' => old('catatan'), 'terkunci' => (bool) old('_terkunci')]
        : $kosong;
    $labelStatus = fn ($s) => \App\Models\Kavling::LABEL_STATUS[$s] ?? $s;
@endphp

@section('content')
<div x-data="{
        form: @js($awal),
        kosong: @js($kosong),
        harga: {{ $hargaAktif }},
        get action() { return this.form.id ? '{{ url('master-kavling') }}/' + this.form.id : '{{ route('kavling.store') }}' },
        get estimasi() { return (Number(String(this.form.luas).replace(',', '.')) || 0) * this.harga },
        tambah() { this.form = { ...this.kosong }; this.$dispatch('open-modal', 'kavling') },
        ubah(k) { this.form = { ...k }; this.$dispatch('open-modal', 'kavling') },
     }">

    <x-page-header title="Kavling" :subtitle="'Harga aktif ' . ($tahapAktif?->nama_tahap ?? '') . ': ' . rupiah($hargaAktif) . '/m²'"
                   :breadcrumbs="['Data Master' => null, 'Kavling' => null]">
        <x-slot:actions>
            <x-button variant="secondary" icon="tag" :href="route('skema-harga.index')">Skema Harga</x-button>
            <x-button icon="plus" x-on:click="tambah()">Tambah Kavling</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-7">
        <x-stat-card label="Total" :value="$total" tone="utama" :href="route('kavling.index')"/>
        @foreach (\App\Models\Kavling::STATUS as $s)
            <x-stat-card :label="$labelStatus($s)" :value="$jumlah[$s] ?? 0" :href="route('kavling.index', ['status' => $s])"
                         :class="request('status') === $s ? 'border-brand-500 ring-1 ring-brand-500' : ''"/>
        @endforeach
    </div>

    <div class="card">
        <x-filter-bar placeholder="Cari kode kavling…">
            <x-select name="blok" :options="$bloks->mapWithKeys(fn ($b) => [$b => 'Blok ' . $b])" :value="request('blok')" placeholder="Semua blok"/>
            <x-select name="tipe" :options="collect(\App\Models\Kavling::TIPE)->mapWithKeys(fn ($t) => [$t => $t])" :value="request('tipe')" placeholder="Semua tipe"/>
            <x-select name="status" :options="collect(\App\Models\Kavling::STATUS)->mapWithKeys(fn ($s) => [$s => $labelStatus($s)])" :value="request('status')" placeholder="Semua status"/>
        </x-filter-bar>

        @if ($kavlings->isEmpty())
            <x-empty-state title="Tidak ada kavling" message="Ubah atau hapus filter."/>
        @else
            {{-- Laptop & tablet: tabel --}}
            <div class="table-wrap max-md:hidden">
                <table class="table">
                    <thead>
                        <tr><th>Kode</th><th>Tipe</th><th class="text-right">Luas</th><th class="text-right">Harga/m²</th><th class="text-right">Harga Jual</th><th>Status</th><th>Pembeli</th><th class="w-px"></th></tr>
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
                                <td><a href="{{ route('kavling.show', $k) }}" class="tautan">{{ $k->kode_kavling }}</a></td>
                                <td>{{ $k->tipe }}<div class="text-xs text-slate-500">{{ $k->ukuran ?: '—' }}</div></td>
                                <td class="text-right tabular-nums">{{ $k->luas ? angka($k->luas) . ' m²' : '—' }}</td>
                                <td class="text-right tabular-nums">
                                    <span class="inline-flex items-center gap-1">{{ $hargaM2 ? rupiah($hargaM2) : '—' }}
                                        @if ($t)<span x-data x-tip="'Harga terkunci di transaksi'" tabindex="0"><x-icon name="lock" class="size-3.5 text-slate-400"/></span>@endif</span>
                                </td>
                                <td class="text-right font-medium text-slate-900 tabular-nums">{{ $hargaJual ? rupiah($hargaJual) : 'Menunggu luas' }}</td>
                                <td><x-badge :status="$k->status" :label="$k->label_status"/></td>
                                <td>
                                    @if ($t)
                                        <a href="{{ route('transaksi-penjualan.show', $t) }}" class="group">
                                            <span class="block font-medium text-slate-800 group-hover:text-brand-700">{{ $t->konsumen->nama_lengkap }}</span>
                                            <span class="text-xs text-slate-500">{{ $t->kode_transaksi }}</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex justify-end gap-0.5">
                                        <x-icon-button icon="eye" label="Detail" :href="route('kavling.show', $k)"/>
                                        <x-icon-button icon="pencil" label="Ubah" x-on:click="ubah({{ Js::from($dataForm) }})"/>
                                        <x-delete-button :action="route('kavling.destroy', $k)" :title="'Hapus kavling ' . $k->kode_kavling . '?'" message="Kavling yang pernah bertransaksi tidak bisa dihapus."/>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- HP: kartu --}}
            <ul class="divide-y divide-slate-100 md:hidden">
                @foreach ($kavlings as $k)
                    @php
                        $t = $k->transaksiAktif;
                        $hargaJual = $t ? $t->nilai_jual : $k->harga_jual;
                        $dataForm = ['id' => $k->id, 'blok' => $k->blok, 'nomor' => (int) substr($k->no, strlen($k->blok)), 'tipe' => $k->tipe, 'ukuran' => $k->ukuran, 'luas' => $k->luas ? (float) $k->luas : '', 'catatan' => $k->catatan, 'terkunci' => (bool) $t];
                    @endphp
                    <li class="flex items-start gap-3 px-4 py-3.5">
                        <a href="{{ route('kavling.show', $k) }}" class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-slate-900">{{ $k->kode_kavling }}</span>
                                <x-badge :status="$k->status" :label="$k->label_status"/>
                            </div>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $k->tipe }} · {{ $k->luas ? angka($k->luas) . ' m²' : 'luas belum final' }}</p>
                            <p class="mt-1 text-sm font-medium text-slate-900 tabular-nums">{{ $hargaJual ? rupiah($hargaJual) : 'Menunggu luas' }}</p>
                            @if ($t)<p class="mt-0.5 truncate text-xs text-slate-600">{{ $t->konsumen->nama_lengkap }} · {{ $t->kode_transaksi }}</p>@endif
                        </a>
                        <div class="flex shrink-0">
                            <x-icon-button icon="pencil" label="Ubah" x-on:click="ubah({{ Js::from($dataForm) }})"/>
                            <x-delete-button :action="route('kavling.destroy', $k)" :title="'Hapus kavling ' . $k->kode_kavling . '?'" message="Kavling yang pernah bertransaksi tidak bisa dihapus."/>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Popup tambah / ubah --}}
    <x-modal name="kavling" judul-js="form.id ? 'Ubah Kavling' : 'Tambah Kavling'" :show="old('_form') === 'kavling' && $errors->any()">
        <form method="POST" :action="action" class="flex min-h-0 flex-1 flex-col">
            @csrf
            <template x-if="form.id"><input type="hidden" name="_method" value="PUT"></template>
            <input type="hidden" name="_form" value="kavling">
            <input type="hidden" name="_id" :value="form.id">
            <input type="hidden" name="_terkunci" :value="form.terkunci ? 1 : ''">

            <x-modal-body>
                <template x-if="form.terkunci">
                    <div class="flex items-center gap-2 rounded-kontrol bg-amber-50 px-3 py-2 text-xs text-amber-800">
                        <x-icon name="lock" class="size-4"/> Sedang bertransaksi: blok, nomor, dan luas terkunci.
                    </div>
                </template>
                <div class="grid grid-cols-2 gap-4">
                    <x-field label="Blok" name="blok" required>
                        <x-input name="blok" x-model="form.blok" maxlength="3" x-bind:readonly="form.terkunci" class="uppercase" placeholder="A"/>
                    </x-field>
                    <x-field label="Nomor" name="nomor" required>
                        <x-input name="nomor" inputmode="numeric" x-model="form.nomor" x-bind:readonly="form.terkunci" placeholder="1"/>
                    </x-field>
                </div>
                <p class="-mt-2 text-xs text-slate-500">Kode: <span class="font-semibold text-slate-800" x-text="'{{ \App\Services\Pengaturan::get('prefix_kavling', 'TR') }}-' + (form.blok || '?').toUpperCase() + String(form.nomor || 0).padStart(2, '0')"></span></p>

                <x-field label="Tipe" name="tipe" required>
                    <x-select name="tipe" :options="collect(\App\Models\Kavling::TIPE)->mapWithKeys(fn ($t) => [$t => $t])" x-model="form.tipe"/>
                </x-field>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field label="Ukuran" name="ukuran" hint="Mis. 7 x 14">
                        <x-input name="ukuran" x-model="form.ukuran"/>
                    </x-field>
                    <x-field label="Luas (m²)" name="luas" hint="Kosongkan bila belum final">
                        <x-input name="luas" inputmode="decimal" x-model="form.luas" x-bind:readonly="form.terkunci"/>
                    </x-field>
                </div>
                <dl class="space-y-1 rounded-kontrol bg-slate-50 px-4 py-3 text-sm" x-show="!form.terkunci">
                    <div class="flex justify-between"><dt class="text-slate-500">Harga/m²</dt><dd class="font-medium tabular-nums" x-text="rupiah(harga)"></dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Estimasi harga jual</dt><dd class="font-semibold text-slate-900 tabular-nums" x-text="estimasi ? rupiah(estimasi) : '—'"></dd></div>
                </dl>
                <x-field label="Catatan" name="catatan">
                    <x-textarea name="catatan" rows="2" x-model="form.catatan"/>
                </x-field>
            </x-modal-body>
            <x-modal-footer>
                <button type="submit" class="btn btn-primary" x-text="form.id ? 'Simpan' : 'Tambah'"></button>
            </x-modal-footer>
        </form>
    </x-modal>
</div>
@endsection
