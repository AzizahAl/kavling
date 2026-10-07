@extends('layouts.app')
@php $ubah = (bool) $transaksi; @endphp
@section('title', $ubah ? 'Ubah Transaksi ' . $transaksi->kode_transaksi : 'Transaksi Baru')

@php
    $awal = [
        'mode'      => old('konsumen_mode', 'lama'),
        'konsumen'  => $pilihKonsumen ? ['id' => $pilihKonsumen->id, 'id_konsumen' => $pilihKonsumen->id_konsumen, 'nama_lengkap' => $pilihKonsumen->nama_lengkap, 'nik' => $pilihKonsumen->nik, 'no_hp' => $pilihKonsumen->no_hp] : null,
        'kavlingId' => (int) old('kavling_id', $pilihKavling) ?: '',
        'jenis'     => old('jenis_pembayaran', $transaksi->jenis_pembayaran ?? 'angsuran'),
        'tenor'     => (int) old('tenor', $transaksi->tenor ?? $aturan['tenor_maks']),
        'dp'        => (float) old('nominal_dp', $transaksi->nominal_dp ?? 0),
        'dpDisentuh' => $ubah || old('nominal_dp') !== null,
        'tanggal'   => old('tanggal', $transaksi?->tanggal?->toDateString() ?? now()->toDateString()),
        'bayarJenis' => old('bayar_jenis', 'reservasi'),
        'bayar'     => (float) old('bayar_nominal', 0),
    ];
    if (old('konsumen_id') && ! $awal['konsumen'] && ($k = \App\Models\Konsumen::find(old('konsumen_id')))) {
        $awal['konsumen'] = $k->only(['id', 'id_konsumen', 'nama_lengkap', 'nik', 'no_hp']);
    }
    $kavlingList = $ubah
        ? collect([['id' => $transaksi->kavling_id, 'kode' => $transaksi->kavling->kode_kavling, 'tipe' => $transaksi->kavling->tipe, 'ukuran' => $transaksi->kavling->ukuran, 'luas' => (float) $transaksi->luas, 'harga_m2' => (float) $transaksi->harga_per_m2, 'harga' => (float) $transaksi->nilai_jual]])
        : $kavlings;
    // Kavling tanpa luas belum bisa dijual (sebelumnya tampil sebagai opsi nonaktif)
    $opsiKavling = $ubah ? [] : $kavlings->filter(fn ($k) => $k['harga'])->mapWithKeys(fn ($k) => [$k['id'] => "{$k['kode']} · {$k['tipe']} · " . angka($k['luas']) . ' m² · ' . rupiah($k['harga'])]);
    $belumLuas = $ubah ? collect() : $kavlings->reject(fn ($k) => $k['harga'])->pluck('kode');
    $jenisBayar = collect(\App\Models\Pembayaran::JENIS)->map(fn ($l, $v) => ['v' => $v, 'l' => $l])->values();
@endphp

@section('content')
<x-page-header :title="$ubah ? 'Ubah Transaksi' : 'Transaksi Baru'"
               :subtitle="$ubah ? $transaksi->kode_transaksi . ' · kavling & harga terkunci' : 'Harga dikunci saat disimpan.'"
               :back="$ubah ? route('transaksi-penjualan.show', $transaksi) : route('transaksi-penjualan.index')"
               :breadcrumbs="['Transaksi Penjualan' => route('transaksi-penjualan.index'), ($ubah ? 'Ubah' : 'Baru') => null]"/>

<form method="POST" action="{{ $ubah ? route('transaksi-penjualan.update', $transaksi) : route('transaksi-penjualan.store') }}"
      x-data="transaksiForm(@js($awal), @js($kavlingList), @js($aturan), @js($jenisBayar))"
      x-on:money-changed="uangBerubah($event.detail)">
    @csrf
    @if ($ubah) @method('PUT') @endif

    <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
        <div class="min-w-0 space-y-5">
            {{-- 1. Konsumen --}}
            <x-card title="Konsumen">
                @unless ($ubah)
                    <input type="hidden" name="konsumen_mode" :value="mode">
                    <div class="mb-4 inline-flex rounded-kontrol bg-slate-200/60 p-1 text-[13px]" role="tablist">
                        <button type="button" role="tab" class="h-8 rounded-lg px-3 font-medium transition-colors" :class="mode === 'lama' ? 'bg-white text-slate-900 shadow-kartu' : 'text-slate-600 hover:text-slate-900'" :aria-selected="mode === 'lama'" x-on:click="mode = 'lama'">Terdaftar</button>
                        <button type="button" role="tab" class="h-8 rounded-lg px-3 font-medium transition-colors" :class="mode === 'baru' ? 'bg-white text-slate-900 shadow-kartu' : 'text-slate-600 hover:text-slate-900'" :aria-selected="mode === 'baru'" x-on:click="mode = 'baru'">Konsumen baru</button>
                    </div>
                @endunless

                <div x-show="mode === 'lama'" @class(['is-invalid' => $errors->has('konsumen_id')])>
                    <input type="hidden" name="konsumen_id" :value="konsumen?.id">
                    <template x-if="konsumen">
                        <div class="flex items-center justify-between gap-3 rounded-kontrol border border-brand-200 bg-brand-50 px-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-slate-900" x-text="konsumen.nama_lengkap"></p>
                                <p class="truncate text-xs text-slate-500" x-text="konsumen.id_konsumen + ' · NIK ' + konsumen.nik + ' · ' + konsumen.no_hp"></p>
                            </div>
                            <x-button variant="secondary" size="sm" x-on:click="konsumen = null; $nextTick(() => $refs.cari?.focus())">Ganti</x-button>
                        </div>
                    </template>
                    <div x-show="!konsumen" class="relative">
                        <label for="cari-konsumen" class="form-label wajib">Cari konsumen</label>
                        <div class="relative">
                            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"/>
                            <input type="search" id="cari-konsumen" x-ref="cari" x-model="q" x-on:input.debounce.300ms="cari()" x-on:focus="cari()"
                                   placeholder="Nama, ID, NIK, atau HP…" @class(['form-input pl-9', 'is-invalid' => $errors->has('konsumen_id')]) autocomplete="off">
                        </div>
                        <div x-show="hasil.length" x-cloak x-on:click.outside="hasil = []" x-transition.opacity.duration.150ms
                             class="absolute z-20 mt-1.5 w-full overflow-hidden rounded-kontrol border border-slate-200 bg-white p-1 shadow-menu">
                            <template x-for="k in hasil" :key="k.id">
                                <button type="button" class="block w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-slate-100" x-on:click="konsumen = k; hasil = []">
                                    <span class="font-medium text-slate-900" x-text="k.nama_lengkap"></span>
                                    <span class="block text-xs text-slate-500" x-text="k.id_konsumen + ' · ' + k.no_hp"></span>
                                </button>
                            </template>
                        </div>
                        <p x-show="q.length > 1 && !hasil.length && sudahCari" class="form-hint">Tidak ditemukan. <button type="button" class="tautan" x-on:click="mode = 'baru'">Tambah konsumen baru</button></p>
                        @error('konsumen_id')<p class="form-error"><x-icon name="alert" class="mt-px size-3.5"/>{{ $message }}</p>@enderror
                    </div>
                </div>

                @unless ($ubah)
                    <div x-show="mode === 'baru'" x-cloak>
                        @include('konsumen._fields', ['p' => 'konsumen_', 'ringkas' => true])
                    </div>
                @endunless
            </x-card>

            {{-- 2. Kavling --}}
            <x-card title="Kavling">
                @if ($ubah)
                    <input type="hidden" name="kavling_id" value="{{ $transaksi->kavling_id }}">
                @else
                    <x-field label="Kavling tersedia" name="kavling_id" required
                             :hint="$belumLuas->isNotEmpty() ? $belumLuas->implode(', ') . ' belum bisa dipilih (luas belum final).' : null">
                        <x-select name="kavling_id" :options="$opsiKavling" placeholder="Pilih kavling" x-model="kavlingId" x-on:change="kavlingBerubah()" :cari="true"/>
                    </x-field>
                    @if ($kavlings->isEmpty())
                        <p class="form-hint text-amber-700">Tidak ada kavling tersedia.</p>
                    @endif
                @endif

                <dl x-show="kavling" x-cloak class="mt-4 grid grid-cols-2 gap-3 rounded-kontrol bg-slate-50 p-4 text-sm sm:grid-cols-4">
                    <div><dt class="text-xs text-slate-500">Kavling</dt><dd class="font-semibold" x-text="kavling?.kode"></dd></div>
                    <div><dt class="text-xs text-slate-500">Luas</dt><dd class="font-semibold tabular-nums" x-text="angka(kavling?.luas) + ' m²'"></dd></div>
                    <div><dt class="text-xs text-slate-500">Harga/m² {{ $ubah ? '(terkunci)' : '· ' . $tahapAktif?->nama_tahap }}</dt><dd class="font-semibold tabular-nums" x-text="rupiah(kavling?.harga_m2)"></dd></div>
                    <div><dt class="text-xs text-slate-500">Harga Jual</dt><dd class="font-semibold text-brand-700 tabular-nums" x-text="rupiah(harga)"></dd></div>
                </dl>
            </x-card>

            {{-- 3. Skema pembayaran --}}
            <x-card title="Skema Pembayaran">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field label="Tanggal Transaksi" name="tanggal" required>
                        <x-input type="date" name="tanggal" x-model="tanggal" :max="now()->toDateString()"/>
                    </x-field>
                    @if (auth()->user()->isAdmin())
                        <x-field label="Agen" name="agen_id">
                            <x-select name="agen_id" :options="$agens" :value="$transaksi?->agen_id" placeholder="Tanpa agen"/>
                        </x-field>
                    @else
                        <x-field label="Agen">
                            <x-input name="_agen" :value="$agens->first()" readonly class="bg-slate-50"/>
                        </x-field>
                    @endif
                </div>

                <fieldset class="mt-5">
                    <legend class="form-label wajib">Metode Pembayaran</legend>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach (['cash' => ['Cash', 'Tanpa jadwal cicilan'], 'angsuran' => ['Angsuran', 'Maks. ' . $aturan['tenor_maks'] . ' bulan']] as $val => [$lbl, $ket])
                            <label class="cursor-pointer rounded-kontrol border p-3.5 transition-colors has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-brand-500"
                                   :class="jenis === '{{ $val }}' ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500' : 'border-slate-300 hover:border-slate-400 bg-white'">
                                <input type="radio" name="jenis_pembayaran" value="{{ $val }}" x-model="jenis" class="sr-only">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="font-medium text-slate-900">{{ $lbl }}</span>
                                    <span class="flex size-4 items-center justify-center rounded-full border" :class="jenis === '{{ $val }}' ? 'border-brand-600 bg-brand-600' : 'border-slate-300'"><span class="size-1.5 rounded-full bg-white"></span></span>
                                </span>
                                <span class="mt-0.5 block text-xs text-slate-500">{{ $ket }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('jenis_pembayaran')<p class="form-error"><x-icon name="alert" class="mt-px size-3.5"/>{{ $message }}</p>@enderror
                </fieldset>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <x-field label="DP" name="nominal_dp">
                        <x-money name="nominal_dp" :value="$awal['dp']"/>
                        <p class="form-hint" x-show="!errors.dp">
                            <span x-text="persenDp"></span> dari harga ·
                            <button type="button" class="tautan" x-show="harga" x-on:click="isiDpAnjuran()">Anjuran {{ persen($aturan['dp_anjuran'], false) }}</button>
                            <button type="button" class="tautan ml-1 text-slate-600" x-on:click="setDp(0)">Tanpa DP</button>
                        </p>
                        <p class="form-error" x-show="errors.dp" x-cloak><x-icon name="alert" class="mt-px size-3.5"/><span x-text="errors.dp"></span></p>
                    </x-field>
                    <x-field label="Tenor" name="tenor" required x-show="jenis === 'angsuran'">
                        <x-select name="tenor" :options="collect(range(1, $aturan['tenor_maks']))->mapWithKeys(fn ($i) => [$i => $i . ' bulan'])" x-model="tenor" x-bind:disabled="jenis !== 'angsuran'"/>
                    </x-field>
                </div>
                <p class="mt-3 text-xs text-slate-500">Reservasi {{ rupiah($aturan['biaya_reservasi']) }} & booking {{ rupiah($aturan['biaya_booking']) }} di luar harga kavling.</p>
            </x-card>

            {{-- 4. Pembayaran awal --}}
            @unless ($ubah)
                <x-card title="Pembayaran Awal" subtitle="Opsional">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-field label="Jenis" name="bayar_jenis">
                            <x-select name="bayar_jenis" data-opsi-expr="opsiBayar" x-model="bayarJenis" x-on:change="saranBayar()"/>
                        </x-field>
                        <x-field label="Nominal" name="bayar_nominal" hint="Kosongkan bila belum ada uang masuk">
                            <x-money name="bayar_nominal" :value="$awal['bayar'] ?: null"/>
                        </x-field>
                        <x-field label="Metode" name="bayar_metode">
                            <x-select name="bayar_metode" :options="\App\Models\Pembayaran::METODE_KONSUMEN"/>
                        </x-field>
                        <x-field label="No. Bukti" name="bayar_no_bukti">
                            <x-input name="bayar_no_bukti" placeholder="Opsional"/>
                        </x-field>
                        <x-field label="A/N Penyetor/Transfer" name="bayar_nama_penyetor">
                            <x-input name="bayar_nama_penyetor" placeholder="Opsional"/>
                        </x-field>
                        <x-field label="Nama Bank" name="bayar_bank_penyetor">
                            <x-select name="bayar_bank_penyetor" :options="\App\Support\Bank::pilihan()" placeholder="Pilih bank (opsional)"/>
                        </x-field>
                        <x-field label="No Rek. Penyetor" name="bayar_rekening_penyetor">
                            <x-input name="bayar_rekening_penyetor" inputmode="numeric" placeholder="Opsional"/>
                        </x-field>
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Data penyetor dicetak di Form Reservasi.</p>
                </x-card>
            @endunless

            <x-card>
                <x-field label="Catatan" name="catatan">
                    <x-textarea name="catatan" :value="$transaksi?->catatan" rows="2"/>
                </x-field>
            </x-card>
        </div>

        {{-- Ringkasan --}}
        <aside class="lg:sticky lg:top-24">
            <x-card title="Ringkasan">
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Harga kavling</dt><dd class="font-semibold tabular-nums" x-text="rupiah(harga)"></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">DP</dt><dd class="tabular-nums" x-text="rupiah(dp)"></dd></div>
                    <template x-if="jenis === 'angsuran'">
                        <div class="space-y-2.5">
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Diangsur</dt><dd class="tabular-nums" x-text="rupiah(pokok)"></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Tenor</dt><dd x-text="tenor + ' bulan'"></dd></div>
                            <div class="flex justify-between gap-3 rounded-kontrol bg-brand-50 px-3 py-2"><dt class="font-medium text-brand-800">Cicilan/bulan</dt><dd class="font-semibold text-brand-800 tabular-nums" x-text="rupiah(cicilan)"></dd></div>
                            <div class="flex justify-between gap-3" x-show="cicilanAkhir !== cicilan"><dt class="text-slate-500">Cicilan terakhir</dt><dd class="tabular-nums" x-text="rupiah(cicilanAkhir)"></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Jatuh tempo ke-1</dt><dd x-text="jatuhTempoPertama"></dd></div>
                        </div>
                    </template>
                </dl>
                <div class="mt-5 flex flex-col gap-2">
                    <button type="submit" class="btn btn-primary w-full" :disabled="!kavling || !!errors.dp"><x-icon name="check" class="size-4"/> {{ $ubah ? 'Simpan' : 'Simpan Transaksi' }}</button>
                    <x-button variant="secondary" class="w-full" :href="$ubah ? route('transaksi-penjualan.show', $transaksi) : route('transaksi-penjualan.index')">Batal</x-button>
                </div>
            </x-card>
        </aside>
    </div>
</form>
@endsection

@push('scripts')
<script>
    function transaksiForm(awal, kavlings, aturan, jenisBayar) {
        return {
            ...awal, kavlings, aturan, jenisBayar, q: '', hasil: [], sudahCari: false,
            get kavling() { return this.kavlings.find(k => k.id === Number(this.kavlingId)) || null },
            get harga() { return this.kavling?.harga || 0 },
            get pokok() { return Math.max(0, this.harga - this.dp) },
            get cicilan() { return this.tenor > 0 ? Math.floor(this.pokok / this.tenor) : 0 },
            get cicilanAkhir() { return this.pokok - this.cicilan * (this.tenor - 1) },
            get persenDp() { return this.harga ? (this.dp / this.harga * 100).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + '%' : '0%' },
            get opsiBayar() { return this.jenisBayar.filter(o => o.v !== 'angsuran' || this.jenis === 'angsuran') },
            get jatuhTempoPertama() {
                if (!this.tanggal) return '—';
                const d = new Date(this.tanggal + 'T00:00:00'), hari = d.getDate();
                d.setDate(1); d.setMonth(d.getMonth() + 1);
                d.setDate(Math.min(hari, new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate()));
                return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
            },
            get errors() {
                const min = Math.ceil(this.harga * this.aturan.dp_min / 100);
                if (this.harga && this.dp < min) return { dp: 'DP minimal ' + rupiah(min) + ' (' + this.aturan.dp_min + '%).' };
                if (this.harga && this.dp > this.harga) return { dp: 'DP melebihi harga jual.' };
                if (this.harga && this.jenis === 'angsuran' && this.dp >= this.harga) return { dp: 'DP sama dengan harga jual. Pilih Cash.' };
                return {};
            },
            init() { if (this.kavling && !this.dpDisentuh) this.isiDpAnjuran() },
            setDp(v) { this.dp = v; this.dpDisentuh = true; this.$dispatch('set-money', { name: 'nominal_dp', value: v }) },
            isiDpAnjuran() { this.setDp(Math.round(this.harga * this.aturan.dp_anjuran / 100)) },
            kavlingBerubah() { if (!this.dpDisentuh || this.dp === 0) this.isiDpAnjuran(); this.dpDisentuh = false },
            uangBerubah({ name, value }) {
                if (name === 'nominal_dp') { this.dp = value; this.dpDisentuh = true }
                if (name === 'bayar_nominal') this.bayar = value;
            },
            saranBayar() {
                const saran = { reservasi: this.aturan.biaya_reservasi, booking: this.aturan.biaya_booking, dp: this.dp, angsuran: this.cicilan, pelunasan: this.pokok };
                this.$dispatch('set-money', { name: 'bayar_nominal', value: saran[this.bayarJenis] || '' });
            },
            async cari() {
                if (this.q.length < 2) { this.hasil = []; return }
                const r = await fetch('{{ route('konsumen.cari') }}?q=' + encodeURIComponent(this.q), { headers: { Accept: 'application/json' } });
                this.hasil = r.ok ? await r.json() : []; this.sudahCari = true;
            },
            angka: (n) => window.angka(n),
            rupiah: (n) => window.rupiah(n),
        }
    }
</script>
@endpush
