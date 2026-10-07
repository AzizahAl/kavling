@extends('layouts.app')
@section('title', 'RAB & Realisasi')

@php
    $periodeForm = $periode ?? now()->format('Y-m');
    $kosong = ['id' => null, 'tanggal_pengeluaran' => '', 'kategori' => '', 'uraian' => '', 'anggaran' => '', 'realisasi' => '', 'catatan' => ''];
    $awal = old('_form') === 'rab'
        ? ['id' => old('_id'), 'tanggal_pengeluaran' => old('tanggal_pengeluaran'), 'kategori' => old('kategori'), 'uraian' => old('uraian'), 'anggaran' => old('anggaran'), 'realisasi' => old('realisasi'), 'catatan' => old('catatan')]
        : $kosong;
    $dataRab = fn ($r) => ['id' => $r->id, 'tanggal_pengeluaran' => $r->tanggal_pengeluaran?->toDateString() ?? '', 'kategori' => $r->kategori, 'uraian' => $r->uraian, 'anggaran' => $r->anggaran !== null ? (float) $r->anggaran : '', 'realisasi' => $r->realisasi !== null ? (float) $r->realisasi : '', 'catatan' => $r->catatan];
    // Popup centang: hanya untuk baris yang anggaran & realisasinya terisi; tanggal pengeluaran dikonfirmasi sebelum masuk kas
    $dataCentang = fn ($r) => ['id' => $r->id, 'uraian' => $r->uraian, 'anggaran' => $r->anggaran !== null ? (float) $r->anggaran : null, 'realisasi' => $r->realisasi !== null ? (float) $r->realisasi : '', 'tanggal' => $r->tanggal_pengeluaran?->toDateString() ?? now()->toDateString()];
    $centangAwal = old('_form') === 'centang'
        ? ['id' => old('_id'), 'uraian' => old('_uraian'), 'anggaran' => old('_anggaran'), 'realisasi' => old('_realisasi'), 'tanggal' => old('tanggal_pengeluaran')]
        : ['id' => null, 'uraian' => '', 'anggaran' => null, 'realisasi' => '', 'tanggal' => now()->toDateString()];
    $opsiPeriode = $daftarPeriode->push(now()->format('Y-m'))->when($periode, fn ($c) => $c->push($periode))->unique()->sortDesc()
        ->mapWithKeys(fn ($p) => [$p => tanggal($p . '-01', 'F Y')])->prepend('Semua periode', 'semua')->all();
    $statusRab = ['belum_dianggarkan', 'belum_direalisasikan', 'sesuai', 'melebihi'];
    $labelStatus = collect($statusRab)->mapWithKeys(fn ($s) => [$s => \App\Support\Status::label($s)])->all();
    $kelasStatus = collect($statusRab)->mapWithKeys(fn ($s) => [$s => \App\Support\Status::kelas($s)])->all();
    $adaFilter = collect(request()->only(['cari', 'kategori', 'status']))->filter(fn ($v) => filled($v))->isNotEmpty();
    $totalAnggaran = (float) $rabs->sum(fn ($r) => (float) $r->anggaran);
    $totalRealisasi = (float) $rabs->sum('realisasi_nilai');
    $kelasSelisih = fn ($v) => $v < 0 ? 'text-red-600' : 'text-green-700';
@endphp

@section('content')
<div x-data="{
        f: @js($awal), kosong: @js($kosong), label: @js($labelStatus), kelas: @js($kelasStatus), c: @js($centangAwal),
        bukaCentang(d) { this.c = { ...d }; this.$dispatch('open-modal', 'centang') },
        get action() { return this.f.id ? '{{ url('rab-realisasi') }}/' + this.f.id : '{{ route('rab.store') }}' },
        get status() {
            if (this.f.anggaran === '' || this.f.anggaran === null) return 'belum_dianggarkan';
            if (this.f.realisasi === '' || this.f.realisasi === null) return 'belum_direalisasikan';
            return Number(this.f.realisasi) > Number(this.f.anggaran) ? 'melebihi' : 'sesuai';
        },
        get selisih() { return this.f.anggaran === '' || this.f.anggaran === null ? null : Number(this.f.anggaran) - Number(this.f.realisasi || 0) },
        buka(d) {
            this.f = { ...d }; this.$dispatch('open-modal', 'rab');
            this.$nextTick(() => { this.$dispatch('set-money', { name: 'anggaran', value: this.f.anggaran }); this.$dispatch('set-money', { name: 'realisasi', value: this.f.realisasi }) });
        },
     }"
     x-on:money-changed="if (['anggaran', 'realisasi'].includes($event.detail.name)) f[$event.detail.name] = $event.target.value === '' ? '' : $event.detail.value">

<x-page-header title="RAB & Realisasi" subtitle="RAB per periode. Baris yang anggaran dan realisasinya sudah terisi bisa dicentang untuk dicatat di Kas Proyek." :breadcrumbs="['Keuangan' => null, 'RAB & Realisasi' => null]">
    <x-slot:actions>
        @if ($periode && $siapKas->isNotEmpty())
            <form method="POST" action="{{ route('rab.centang-semua') }}" class="inline-flex"
                  data-confirm="{{ $siapKas->count() }} baris RAB periode {{ tanggal($periode . '-01', 'F Y') }} senilai {{ rupiah($siapKas->sum('realisasi_nilai')) }} dicatat di Kas Proyek sebagai pengeluaran, masing-masing pada tanggal pengeluarannya. Setelah itu baris-baris ini dikunci."
                  data-confirm-title="Catat semua ke Kas Proyek?" data-confirm-ok="Ya, Catat Semua" data-confirm-danger="false">
                @csrf
                <input type="hidden" name="periode" value="{{ $periode }}">
                <x-button type="submit" variant="secondary" icon="check">Catat Semua ke Kas ({{ $siapKas->count() }})</x-button>
            </form>
        @endif
        <x-button icon="plus" x-on:click="buka(kosong)">Tambah Item</x-button>
    </x-slot:actions>
</x-page-header>

<div class="card">
    <div class="flex flex-col gap-1 border-b border-slate-100 px-3 pt-3 sm:px-4 sm:pt-4">
        <h2 class="card-title">Rencana Anggaran Biaya{{ $periode ? ' · ' . tanggal($periode . '-01', 'F Y') : ' · Semua Periode' }}</h2>
    </div>
    {{-- Filter satu baris: pilihan langsung terkirim, pencarian dengan Enter --}}
    <form method="GET" action="{{ route('rab.index') }}" role="search"
          x-on:change="if ($event.target.type !== 'search') $el.requestSubmit()"
          class="grid grid-cols-2 gap-2 border-b border-slate-100 p-3 sm:p-4 lg:flex lg:items-center">
        <x-select name="periode" :options="$opsiPeriode" :value="$periode ?? 'semua'" aria-label="Periode" class="col-span-2 lg:w-44"/>
        <div class="relative col-span-2 min-w-0 lg:w-60 lg:flex-none">
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"/>
            <input type="search" name="cari" value="{{ request('cari') }}" placeholder="Cari uraian atau catatan…" class="form-input pl-9" enterkeyhint="search" aria-label="Cari">
        </div>
        <x-select name="kategori" :options="collect(array_keys(\App\Models\Rab::KATEGORI))->mapWithKeys(fn ($k) => [$k => $k])" :value="request('kategori')" placeholder="Semua kategori" aria-label="Kategori" class="lg:w-44"/>
        <x-select name="status" :options="$labelStatus" :value="request('status')" placeholder="Semua status" aria-label="Status" class="lg:w-48"/>
        <button type="submit" class="sr-only">Cari</button>
        <div class="col-span-2 flex items-center justify-between gap-2 text-sm text-slate-500 lg:ml-auto">
            <span>{{ $rabs->count() }} dari {{ $jumlahSemua }} item</span>
            @if ($adaFilter)<a href="{{ route('rab.index', ['periode' => $periode ?? 'semua']) }}" class="btn btn-ghost btn-sm text-slate-500"><x-icon name="filter-x" class="size-4"/> Reset</a>@endif
        </div>
    </form>

    @if ($rabs->isEmpty())
        <x-empty-state :title="$adaFilter ? 'Tidak ada item yang cocok' : 'Belum ada item RAB'" :message="$adaFilter ? 'Ubah pencarian atau reset filter.' : null">
            @unless ($adaFilter)<x-button icon="plus" x-on:click="buka(kosong)">Tambah Item</x-button>@endunless
        </x-empty-state>
    @else
        {{-- Muat di laptop; di HP tabel digulir di dalam wadahnya --}}
        <div class="table-wrap">
            <table class="table min-w-[64rem] [&_td]:px-3 [&_th]:px-3">
                <thead>
                    <tr>
                        <th>Kategori</th><th>Uraian</th><th>Tgl Pengeluaran</th><th class="text-right">Anggaran</th><th class="text-right">Realisasi</th>
                        <th class="text-right">Selisih</th><th>Status</th><th>Catatan</th><th class="w-px text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rabs as $r)
                        <tr>
                            <td class="text-slate-600">{{ $r->kategori }}</td>
                            <td class="max-w-64">
                                <span class="block truncate font-medium text-slate-900" title="{{ $r->uraian }}">{{ $r->uraian }}</span>
                                @if ($r->isDicentang())
                                    <span class="mt-0.5 inline-flex items-center gap-1 text-xs font-medium text-green-700" x-data
                                          x-tip="@js('Masuk kas' . ($r->dicentang_pada ? ' ' . tanggal($r->dicentang_pada, 'j M Y, H:i') : '') . ($r->pencentang ? ' oleh ' . $r->pencentang->name : ''))" tabindex="0">
                                        <x-icon name="check-circle" class="size-3.5"/> Masuk kas
                                    </span>
                                @endif
                            </td>
                            <td class="text-slate-600">{{ $r->tanggal_pengeluaran ? tanggal($r->tanggal_pengeluaran) : '—' }}</td>
                            <td class="text-right tabular-nums">{{ $r->anggaran !== null ? rupiah($r->anggaran) : '—' }}</td>
                            <td class="text-right tabular-nums">{{ $r->realisasi !== null ? rupiah($r->realisasi) : '—' }}</td>
                            <td class="text-right font-medium tabular-nums {{ $r->selisih !== null ? $kelasSelisih($r->selisih) : 'text-slate-400' }}">{{ $r->selisih !== null ? rupiah($r->selisih) : '—' }}</td>
                            <td><x-badge :status="$r->status"/></td>
                            <td class="max-w-48">
                                @if ($r->catatan)
                                    <span class="block truncate text-slate-600" x-data x-tip="@js($r->catatan)" tabindex="0">{{ $r->catatan }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td>@include('rab._aksi')</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">Total</td>
                        <td class="text-right tabular-nums">{{ rupiah($totalAnggaran) }}</td>
                        <td class="text-right tabular-nums">{{ rupiah($totalRealisasi) }}</td>
                        <td class="text-right tabular-nums {{ $kelasSelisih($totalAnggaran - $totalRealisasi) }}">{{ rupiah($totalAnggaran - $totalRealisasi) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>

{{-- Popup tambah / ubah item --}}
<x-modal name="rab" judul-js="f.id ? 'Ubah Item RAB' : 'Tambah Item RAB'" :show="old('_form') === 'rab' && $errors->any()">
    <form method="POST" :action="action" class="flex min-h-0 flex-1 flex-col" novalidate>
        @csrf
        <template x-if="f.id"><input type="hidden" name="_method" value="PUT"></template>
        <input type="hidden" name="_form" value="rab"><input type="hidden" name="_id" :value="f.id">
        <x-modal-body>
            <input type="hidden" name="_periode" value="{{ $periodeForm }}">
            <x-field label="Tanggal Pengeluaran" name="tanggal_pengeluaran" hint="Dipakai sebagai tanggal di Kas Proyek. Periode RAB mengikuti bulan tanggal ini.">
                <x-input type="date" name="tanggal_pengeluaran" x-model="f.tanggal_pengeluaran"/>
            </x-field>
            <x-field label="Kategori" name="kategori" required>
                <x-select name="kategori" :options="collect(array_keys(\App\Models\Rab::KATEGORI))->mapWithKeys(fn ($k) => [$k => $k])" placeholder="Pilih kategori" x-model="f.kategori"/>
            </x-field>
            <x-field label="Uraian" name="uraian" required><x-input name="uraian" x-model="f.uraian"/></x-field>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field label="Anggaran" name="anggaran"><x-money name="anggaran"/></x-field>
                <x-field label="Realisasi" name="realisasi"><x-money name="realisasi"/></x-field>
            </div>
            <dl class="grid grid-cols-2 gap-3 rounded-kontrol bg-slate-50 px-4 py-3 text-sm">
                <div>
                    <dt class="text-xs text-slate-500">Selisih</dt>
                    <dd class="font-semibold tabular-nums" :class="selisih === null ? 'text-slate-400' : (selisih < 0 ? 'text-red-600' : 'text-green-700')" x-text="selisih === null ? '—' : rupiah(selisih)"></dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">Status</dt>
                    <dd class="mt-0.5"><span class="badge" :class="kelas[status]" x-text="label[status]"></span></dd>
                </div>
            </dl>
            <x-field label="Catatan" name="catatan"><x-textarea name="catatan" rows="2" x-model="f.catatan"/></x-field>
        </x-modal-body>
        <x-modal-footer><x-button type="submit" x-text="f.id ? 'Simpan' : 'Tambah'">Simpan</x-button></x-modal-footer>
    </form>
</x-modal>

{{-- Popup centang: konfirmasi realisasi & tanggal pengeluaran, lalu masuk Kas Proyek --}}
<x-modal name="centang" title="Catat ke Kas Proyek" max-width="md" :show="old('_form') === 'centang' && $errors->any()">
    <form method="POST" :action="'{{ url('rab-realisasi') }}/' + c.id + '/centang'" class="flex min-h-0 flex-1 flex-col" novalidate>
        @csrf
        <input type="hidden" name="_form" value="centang"><input type="hidden" name="_id" :value="c.id">
        <input type="hidden" name="_uraian" :value="c.uraian"><input type="hidden" name="_anggaran" :value="c.anggaran"><input type="hidden" name="_realisasi" :value="c.realisasi">
        <x-modal-body>
            <div class="rounded-kontrol bg-slate-50 px-4 py-3 text-sm">
                <p class="font-medium text-slate-900" x-text="c.uraian"></p>
                <dl class="mt-2 grid grid-cols-2 gap-3">
                    <div><dt class="text-xs text-slate-500">Anggaran</dt><dd class="font-medium tabular-nums" x-text="rupiah(c.anggaran)"></dd></div>
                    <div><dt class="text-xs text-slate-500">Realisasi (masuk kas)</dt><dd class="font-semibold text-red-600 tabular-nums" x-text="rupiah(c.realisasi)"></dd></div>
                </dl>
            </div>
            <x-field label="Tanggal Pengeluaran" name="tanggal_pengeluaran" for="centang_tanggal" required>
                <x-input type="date" name="tanggal_pengeluaran" id="centang_tanggal" x-model="c.tanggal" :max="now()->toDateString()"/>
            </x-field>
            <p class="flex gap-2 text-xs text-slate-500"><x-icon name="info" class="size-4 shrink-0"/>Tercatat sebagai pengeluaran di Kas Proyek pada tanggal pengeluaran, lalu baris ini dikunci. Bisa dibatalkan lewat ikon centang hijau.</p>
        </x-modal-body>
        <x-modal-footer><x-button type="submit" icon="check">Catat ke Kas</x-button></x-modal-footer>
    </form>
</x-modal>
</div>
@endsection
