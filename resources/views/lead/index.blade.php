@extends('layouts.app')
@section('title', 'Lead Harian')

@php
    $kosong = ['id' => null, 'nama' => '', 'no_hp' => '', 'domisili' => '', 'sumber' => 'internet', 'tanggal_lead' => now()->toDateString(), 'agen_id' => (string) ($agens->keys()->first() ?? ''), 'kavling_minat_id' => '', 'catatan' => ''];
    $awal = old('_form') === 'lead'
        ? array_merge($kosong, ['id' => old('_id')], request()->old())
        : $kosong;
    $aksiAwal = in_array(old('_form'), ['prospek', 'closing']) ? ['jenis' => old('_form'), 'id' => old('_id'), 'nama' => old('_nama'), 'agen' => old('_agen')] : ['jenis' => null, 'id' => null, 'nama' => '', 'agen' => null];
@endphp

@section('content')
<div x-data="{
        f: @js($awal), kosong: @js($kosong), aksi: @js($aksiAwal), transaksi: @js($transaksiBebas),
        get action() { return this.f.id ? '{{ url('lead-harian') }}/' + this.f.id : '{{ route('lead.store') }}' },
        get urlAksi() { return '{{ url('lead-harian') }}/' + this.aksi.id + '/' + this.aksi.jenis },
        get transaksiCocok() { return this.transaksi.filter(t => !t.agen_id || t.agen_id == this.aksi.agen) },
        tambah() { this.f = { ...this.kosong }; this.$dispatch('open-modal', 'lead') },
        ubah(l) { this.f = { ...l }; this.$dispatch('open-modal', 'lead') },
        maju(jenis, l) { this.aksi = { jenis, id: l.id, nama: l.nama, agen: l.agen_id }; this.$dispatch('open-modal', 'aksi') },
     }">

<x-page-header title="Lead Harian" subtitle="Alur marketing: Lead → Prospek → Closing. Setiap perubahan tahap dicatat tanggalnya."
               :breadcrumbs="['Marketing' => null, 'Lead Harian' => null]">
    <x-slot:actions>
        <a href="{{ route('lead.rekap') }}" class="btn btn-secondary"><x-icon name="chart" class="h-4 w-4"/> Rekap</a>
        <button type="button" class="btn btn-primary" x-on:click="tambah()" @disabled($agens->isEmpty())><x-icon name="plus" class="h-4 w-4"/> Input Lead</button>
    </x-slot:actions>
</x-page-header>

@if ($agens->isEmpty())
    <div class="mb-6 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <x-icon name="warning"/> Belum ada agen aktif. <a href="{{ route('agen.index') }}" class="font-medium underline">Tambahkan agen</a> dulu sebelum mencatat lead.
    </div>
@endif

<div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
    @foreach (['Hari Ini' => $hariIni, 'Bulan ' . tanggal(now(), 'F') => $bulanIni] as $judul => $a)
        <div @class(['rounded-xl border p-4 shadow-xs sm:p-5', 'border-forest-800 bg-forest-800 text-white' => $loop->first, 'border-slate-200 bg-white' => ! $loop->first])>
            <p @class(['text-xs font-medium tracking-wide uppercase', 'text-forest-200' => $loop->first, 'text-slate-500' => ! $loop->first])>{{ $judul }}</p>
            <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                @foreach (['lead' => 'Lead', 'prospek' => 'Prospek', 'closing' => 'Closing'] as $k => $l)
                    <div>
                        <p class="text-2xl font-bold tabular-nums">{{ $a[$k] }}</p>
                        <p @class(['text-xs', 'text-forest-200' => $loop->parent->first, 'text-slate-500' => ! $loop->parent->first])>{{ $l }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<div class="mb-4 flex gap-2 overflow-x-auto pb-1">
    <a href="{{ request()->fullUrlWithQuery(['tahap' => null, 'page' => null]) }}" @class(['btn btn-sm', 'btn-primary' => ! request('tahap'), 'btn-secondary' => request('tahap')])>Semua <span class="opacity-60">{{ $perTahap->sum() }}</span></a>
    @foreach (\App\Models\Lead::TAHAP as $k => $l)
        <a href="{{ request()->fullUrlWithQuery(['tahap' => $k, 'page' => null]) }}" @class(['btn btn-sm', 'btn-primary' => request('tahap') === $k, 'btn-secondary' => request('tahap') !== $k])>{{ $l }} <span class="opacity-60">{{ $perTahap[$k] ?? 0 }}</span></a>
    @endforeach
</div>

<div class="card">
    <x-filter-bar placeholder="Cari nama, HP, atau kode…">
        @if (request('tahap'))<input type="hidden" name="tahap" value="{{ request('tahap') }}">@endif
        <x-select name="agen" :options="$agens" :value="request('agen')" placeholder="Semua agen" class="sm:w-44"/>
        <x-select name="sumber" :options="\App\Models\Lead::SUMBER" :value="request('sumber')" placeholder="Semua sumber" class="sm:w-48"/>
        <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-input col-span-2 sm:w-44" title="Tanggal aktivitas">
    </x-filter-bar>

    @if ($leads->isEmpty())
        <x-empty-state title="Belum ada lead" message="Catat calon konsumen yang datanya sudah didapat hari ini.">
            @if ($agens->isNotEmpty())<button type="button" class="btn btn-primary" x-on:click="tambah()"><x-icon name="plus" class="h-4 w-4"/> Input Lead</button>@endif
        </x-empty-state>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Calon Konsumen</th><th>Sumber</th><th>Agen / Penginput</th><th>Perjalanan</th><th>Tahap</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @foreach ($leads as $l)
                        @php $data = ['id' => $l->id, 'nama' => $l->nama, 'no_hp' => $l->no_hp, 'domisili' => $l->domisili, 'sumber' => $l->sumber, 'tanggal_lead' => $l->tanggal_lead->toDateString(), 'agen_id' => (string) $l->agen_id, 'kavling_minat_id' => (string) $l->kavling_minat_id, 'catatan' => $l->catatan]; @endphp
                        <tr>
                            <td>
                                <span class="font-semibold text-slate-900">{{ $l->nama }}</span>
                                <div class="text-xs text-slate-500">{{ $l->kode }}{{ $l->no_hp ? ' · ' . $l->no_hp : '' }}{{ $l->kavlingMinat ? ' · minat ' . $l->kavlingMinat->kode_kavling : '' }}</div>
                            </td>
                            <td>{{ $l->label_sumber }}</td>
                            <td>
                                {{ $l->agen->nama_agen }}
                                <div class="text-xs text-slate-500">diinput {{ $l->penginput->name ?? '—' }}</div>
                            </td>
                            <td class="text-xs text-slate-600">
                                <div>Lead: {{ tanggal($l->tanggal_lead) }}</div>
                                @if ($l->tanggal_prospek)<div>Prospek: {{ tanggal($l->tanggal_prospek) }}</div>@endif
                                @if ($l->tanggal_closing)<div>Closing: {{ tanggal($l->tanggal_closing) }} ·
                                    <a href="{{ route('transaksi-penjualan.show', $l->transaksi_id) }}" class="font-medium text-forest-700 hover:underline">{{ $l->transaksi?->kavling?->kode_kavling }}</a></div>@endif
                            </td>
                            <td><x-badge :status="$l->tahap"/></td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    @if ($l->tahap === 'lead')
                                        <button type="button" class="btn btn-sm btn-secondary" x-on:click="maju('prospek', @js($data))">Jadikan Prospek</button>
                                    @elseif ($l->tahap === 'prospek')
                                        <button type="button" class="btn btn-sm btn-gold" x-on:click="maju('closing', @js($data))">Closing</button>
                                    @endif
                                    <button type="button" class="btn-icon" title="Ubah" x-on:click="ubah(@js($data))"><x-icon name="pencil" class="h-[18px] w-[18px]"/></button>
                                    @if ($l->tahap !== 'lead')
                                        <form method="POST" action="{{ route('lead.mundur', $l) }}" data-confirm="Tahap {{ $l->nama }} akan dikembalikan satu langkah ({{ $l->tahap === 'closing' ? 'Closing → Prospek, tautan transaksi dilepas' : 'Prospek → Lead' }})." data-confirm-title="Kembalikan tahap?" data-confirm-ok="Ya, kembalikan" data-confirm-danger="false">
                                            @csrf <button type="submit" class="btn-icon" title="Kembalikan tahap"><x-icon name="arrow-left" class="h-[18px] w-[18px]"/></button>
                                        </form>
                                    @endif
                                    @if ($l->tahap !== 'closing')
                                        <x-delete-button :action="route('lead.destroy', $l)" title="Hapus lead {{ $l->nama }}?"/>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($leads->hasPages())<div class="border-t border-slate-100 px-4 py-3">{{ $leads->links() }}</div>@endif
    @endif
</div>

{{-- Modal input / ubah lead --}}
<x-modal name="lead" max-width="xl" :show="old('_form') === 'lead' && $errors->any()">
    <form method="POST" :action="action">
        @csrf
        <template x-if="f.id"><input type="hidden" name="_method" value="PUT"></template>
        <input type="hidden" name="_form" value="lead"><input type="hidden" name="_id" :value="f.id">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-semibold text-slate-900" x-text="f.id ? 'Ubah Lead' : 'Input Lead Baru'"></h3>
            <button type="button" class="btn-icon -mr-2" x-on:click="$dispatch('close-modal', 'lead')"><x-icon name="x"/></button>
        </div>
        <div class="grid gap-4 p-5 sm:grid-cols-2">
            <x-field label="Nama Calon Konsumen" name="nama" required class="sm:col-span-2">
                <input type="text" name="nama" id="nama" x-model="f.nama" class="form-input">
            </x-field>
            <x-field label="No. HP / WhatsApp" name="no_hp">
                <input type="tel" name="no_hp" id="no_hp" x-model="f.no_hp" class="form-input">
            </x-field>
            <x-field label="Domisili" name="domisili">
                <input type="text" name="domisili" id="domisili" x-model="f.domisili" class="form-input">
            </x-field>
            <x-field label="Sumber" name="sumber" required>
                <select name="sumber" id="sumber" x-model="f.sumber" class="form-input">
                    @foreach (\App\Models\Lead::SUMBER as $v => $lbl)<option value="{{ $v }}">{{ $lbl }}</option>@endforeach
                </select>
            </x-field>
            <x-field label="Tanggal Lead" name="tanggal_lead" required>
                <input type="date" name="tanggal_lead" id="tanggal_lead" x-model="f.tanggal_lead" max="{{ now()->toDateString() }}" class="form-input">
            </x-field>
            <x-field label="Agen Penanggung Jawab" name="agen_id" required>
                <select name="agen_id" id="agen_id" x-model="f.agen_id" class="form-input">
                    @foreach ($agens as $id => $nama)<option value="{{ $id }}">{{ $nama }}</option>@endforeach
                </select>
            </x-field>
            <x-field label="Minat Kavling" name="kavling_minat_id">
                <select name="kavling_minat_id" id="kavling_minat_id" x-model="f.kavling_minat_id" class="form-input">
                    <option value="">— Belum tahu —</option>
                    @foreach ($kavlings as $id => $kode)<option value="{{ $id }}">{{ $kode }}</option>@endforeach
                </select>
            </x-field>
            <x-field label="Catatan" name="catatan" class="sm:col-span-2">
                <textarea name="catatan" id="catatan" rows="2" x-model="f.catatan" class="form-input"></textarea>
            </x-field>
        </div>
        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'lead')">Batal</button>
            <button type="submit" class="btn btn-primary" x-text="f.id ? 'Simpan Perubahan' : 'Simpan Lead'"></button>
        </div>
    </form>
</x-modal>

{{-- Modal naik tahap (prospek / closing) --}}
<x-modal name="aksi" max-width="lg" :show="in_array(old('_form'), ['prospek', 'closing']) && $errors->any()">
    <form method="POST" :action="urlAksi">
        @csrf
        <input type="hidden" name="_form" :value="aksi.jenis"><input type="hidden" name="_id" :value="aksi.id">
        <input type="hidden" name="_nama" :value="aksi.nama"><input type="hidden" name="_agen" :value="aksi.agen">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h3 class="text-base font-semibold text-slate-900" x-text="(aksi.jenis === 'closing' ? 'Closing: ' : 'Jadikan Prospek: ') + aksi.nama"></h3>
            <button type="button" class="btn-icon -mr-2" x-on:click="$dispatch('close-modal', 'aksi')"><x-icon name="x"/></button>
        </div>
        <div class="space-y-4 p-5">
            <p class="text-sm text-slate-600" x-show="aksi.jenis === 'prospek'">Lead sudah ditindaklanjuti dan berpotensi membeli.</p>
            <x-field label="Tanggal" name="tanggal" required>
                <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" max="{{ now()->toDateString() }}" class="form-input">
            </x-field>
            <template x-if="aksi.jenis === 'closing'">
                <x-field label="Transaksi Penjualan" name="transaksi_id" required hint="Hanya transaksi aktif milik agen ini (atau tanpa agen) yang belum terhubung ke lead lain.">
                    <select name="transaksi_id" id="transaksi_id" class="form-input">
                        <option value="">— Pilih transaksi —</option>
                        <template x-for="t in transaksiCocok" :key="t.id"><option :value="t.id" x-text="t.label"></option></template>
                    </select>
                    <p class="form-hint" x-show="!transaksiCocok.length">Belum ada transaksi yang cocok. <a href="{{ route('transaksi-penjualan.create') }}" class="font-medium text-forest-700 underline">Buat transaksi dulu</a>.</p>
                </x-field>
            </template>
            <x-field label="Catatan" name="catatan">
                <input type="text" name="catatan" id="catatan_aksi" class="form-input">
            </x-field>
        </div>
        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal', 'aksi')">Batal</button>
            <button type="submit" class="btn btn-primary" x-text="aksi.jenis === 'closing' ? 'Simpan Closing' : 'Jadikan Prospek'"></button>
        </div>
    </form>
</x-modal>
</div>
@endsection
