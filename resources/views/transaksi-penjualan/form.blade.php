@extends('layouts.app')
@php $ubah = (bool) $transaksi; @endphp
@section('title', $ubah ? 'Ubah Transaksi ' . $transaksi->kode_transaksi : 'Transaksi Baru')

@php
    $awal = [
        'mode'      => old('konsumen_mode', 'lama'),
        'konsumen'  => $pilihKonsumen ? ['id' => $pilihKonsumen->id, 'id_konsumen' => $pilihKonsumen->id_konsumen, 'nama_lengkap' => $pilihKonsumen->nama_lengkap, 'nik' => $pilihKonsumen->nik, 'no_hp' => $pilihKonsumen->no_hp] : null,
        'kavlingId' => (int) old('kavling_id', $pilihKavling),
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
@endphp

@section('content')
<x-page-header :title="$ubah ? 'Ubah Transaksi' : 'Transaksi Penjualan Baru'"
               :subtitle="$ubah ? $transaksi->kode_transaksi . ' · kavling & harga terkunci' : 'Harga dikunci pada tahap aktif saat transaksi disimpan.'"
               :back="$ubah ? route('transaksi-penjualan.show', $transaksi) : route('transaksi-penjualan.index')"
               :breadcrumbs="['Transaksi Penjualan' => route('transaksi-penjualan.index'), ($ubah ? 'Ubah' : 'Baru') => null]"/>

<form method="POST" action="{{ $ubah ? route('transaksi-penjualan.update', $transaksi) : route('transaksi-penjualan.store') }}"
      x-data="transaksiForm(@js($awal), @js($kavlingList), @js($aturan))"
      x-on:money-changed="uangBerubah($event.detail)">
    @csrf
    @if ($ubah) @method('PUT') @endif

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
        <div class="space-y-6">
            {{-- 1. Konsumen --}}
            <x-card title="1. Konsumen">
                @unless ($ubah)
                    <input type="hidden" name="konsumen_mode" :value="mode">
                    <div class="mb-4 inline-flex rounded-lg bg-slate-100 p-1 text-sm">
                        <button type="button" class="rounded-md px-4 py-1.5 font-medium transition" :class="mode === 'lama' ? 'bg-white shadow-xs text-slate-900' : 'text-slate-500'" x-on:click="mode = 'lama'">Konsumen terdaftar</button>
                        <button type="button" class="rounded-md px-4 py-1.5 font-medium transition" :class="mode === 'baru' ? 'bg-white shadow-xs text-slate-900' : 'text-slate-500'" x-on:click="mode = 'baru'">Konsumen baru</button>
                    </div>
                @endunless

                <div x-show="mode === 'lama'">
                    <input type="hidden" name="konsumen_id" :value="konsumen?.id">
                    <template x-if="konsumen">
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-forest-200 bg-forest-50 px-4 py-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900" x-text="konsumen.nama_lengkap"></p>
                                <p class="text-xs text-slate-500" x-text="konsumen.id_konsumen + ' · NIK ' + konsumen.nik + ' · ' + konsumen.no_hp"></p>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary" x-on:click="konsumen = null; $nextTick(() => $refs.cari?.focus())">Ganti</button>
                        </div>
                    </template>
                    <div x-show="!konsumen" class="relative">
                        <x-icon name="search" class="pointer-events-none absolute top-3 left-3 h-4 w-4 text-slate-400"/>
                        <input type="search" x-ref="cari" x-model="q" x-on:input.debounce.300ms="cari()" x-on:focus="cari()"
                               placeholder="Ketik nama, ID, NIK, atau nomor HP…" class="form-input pl-9 {{ $errors->has('konsumen_id') ? 'is-invalid' : '' }}" autocomplete="off">
                        <div x-show="hasil.length" x-on:click.outside="hasil = []" class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
                            <template x-for="k in hasil" :key="k.id">
                                <button type="button" class="block w-full px-4 py-2.5 text-left text-sm hover:bg-slate-50" x-on:click="konsumen = k; hasil = []">
                                    <span class="font-medium text-slate-900" x-text="k.nama_lengkap"></span>
                                    <span class="block text-xs text-slate-500" x-text="k.id_konsumen + ' · ' + k.no_hp"></span>
                                </button>
                            </template>
                        </div>
                        <p x-show="q.length > 1 && !hasil.length && sudahCari" class="form-hint">Tidak ditemukan. <button type="button" class="font-medium text-forest-600 underline" x-on:click="mode = 'baru'">Tambah sebagai konsumen baru</button></p>
                        @error('konsumen_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                @unless ($ubah)
                    <div x-show="mode === 'baru'" x-cloak>
                        @include('konsumen._fields', ['p' => 'konsumen_', 'ringkas' => true])
                    </div>
                @endunless
            </x-card>

            {{-- 2. Kavling --}}
            <x-card title="2. Kavling">
                @if ($ubah)
                    <input type="hidden" name="kavling_id" value="{{ $transaksi->kavling_id }}">
                @else
                    <x-field label="Pilih kavling tersedia" name="kavling_id" required>
                        <select name="kavling_id" id="kavling_id" x-model.number="kavlingId" x-on:change="kavlingBerubah()" class="form-input {{ $errors->has('kavling_id') ? 'is-invalid' : '' }}">
                            <option value="0">— Pilih kavling —</option>
                            @foreach ($kavlings as $k)
                                <option value="{{ $k['id'] }}" @disabled(! $k['harga'])>{{ $k['kode'] }} · {{ $k['tipe'] }} · {{ $k['luas'] ? angka($k['luas']) . ' m² · ' . rupiah($k['harga']) : 'luas belum final' }}</option>
                            @endforeach
                        </select>
                    </x-field>
                    @if ($kavlings->isEmpty())
                        <p class="form-hint text-amber-700">Tidak ada kavling berstatus tersedia.</p>
                    @endif
                @endif

                <div x-show="kavling" x-cloak class="mt-4 grid grid-cols-2 gap-3 rounded-lg bg-slate-50 p-4 text-sm sm:grid-cols-4">
                    <div><p class="text-xs text-slate-500">Kavling</p><p class="font-semibold" x-text="kavling?.kode"></p></div>
                    <div><p class="text-xs text-slate-500">Luas</p><p class="font-semibold" x-text="angka(kavling?.luas) + ' m²'"></p></div>
                    <div><p class="text-xs text-slate-500">Harga / m² @if ($ubah)(terkunci)@else({{ $tahapAktif?->nama_tahap }})@endif</p><p class="font-semibold" x-text="rupiah(kavling?.harga_m2)"></p></div>
                    <div><p class="text-xs text-slate-500">Harga Jual</p><p class="font-bold text-forest-700" x-text="rupiah(harga)"></p></div>
                </div>
            </x-card>

            {{-- 3. Skema pembayaran --}}
            <x-card title="3. Skema Pembayaran">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field label="Tanggal Transaksi" name="tanggal" required>
                        <input type="date" name="tanggal" id="tanggal" x-model="tanggal" max="{{ now()->toDateString() }}" class="form-input {{ $errors->has('tanggal') ? 'is-invalid' : '' }}">
                    </x-field>
                    <x-field label="Agen Penanggung Jawab" name="agen_id">
                        <x-select name="agen_id" :options="$agens" :value="$transaksi?->agen_id" placeholder="— Tanpa agen —"/>
                    </x-field>
                </div>

                <p class="form-label mt-5">Metode Pembayaran <span class="text-red-500">*</span></p>
                <div class="grid grid-cols-2 gap-3">
                    @foreach (['cash' => ['Cash', 'Dibayar penuh, bisa bertahap tanpa jadwal'], 'angsuran' => ['Angsuran', 'Sisa setelah DP dicicil maks. ' . $aturan['tenor_maks'] . ' bulan']] as $val => [$lbl, $ket])
                        <label class="cursor-pointer rounded-xl border p-4 transition" :class="jenis === '{{ $val }}' ? 'border-forest-500 bg-forest-50 ring-1 ring-forest-500' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="jenis_pembayaran" value="{{ $val }}" x-model="jenis" class="sr-only">
                            <span class="block font-semibold text-slate-900">{{ $lbl }}</span>
                            <span class="mt-0.5 block text-xs text-slate-500">{{ $ket }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <x-field label="Down Payment (DP)" name="nominal_dp">
                        <x-money name="nominal_dp" :value="$awal['dp']"/>
                        <p class="form-hint" x-show="!errors.dp">
                            <span x-text="persenDp"></span> dari harga jual.
                            <button type="button" class="font-medium text-forest-600 underline" x-show="harga" x-on:click="isiDpAnjuran()">Pakai anjuran {{ persen($aturan['dp_anjuran'], false) }}</button>
                            <button type="button" class="ml-1 font-medium text-slate-500 underline" x-on:click="setDp(0)">Tanpa DP</button>
                        </p>
                        <p class="form-error" x-show="errors.dp" x-text="errors.dp"></p>
                    </x-field>
                    <x-field label="Tenor (bulan)" name="tenor" x-show="jenis === 'angsuran'">
                        <select name="tenor" id="tenor" x-model.number="tenor" :disabled="jenis !== 'angsuran'" class="form-input {{ $errors->has('tenor') ? 'is-invalid' : '' }}">
                            @for ($i = 1; $i <= $aturan['tenor_maks']; $i++)<option value="{{ $i }}">{{ $i }} bulan</option>@endfor
                        </select>
                    </x-field>
                </div>
                <p class="mt-3 text-xs text-slate-500">DP terpisah dari biaya reservasi ({{ rupiah($aturan['biaya_reservasi']) }}) dan booking fee ({{ rupiah($aturan['biaya_booking']) }}) yang berada di luar harga kavling.</p>
            </x-card>

            {{-- 4. Pembayaran awal (opsional) --}}
            @unless ($ubah)
                <x-card title="4. Pembayaran Awal" subtitle="Opsional. Bisa juga dicatat nanti dari halaman detail transaksi.">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-field label="Jenis Pembayaran" name="bayar_jenis">
                            <select name="bayar_jenis" id="bayar_jenis" x-model="bayarJenis" x-on:change="saranBayar()" class="form-input">
                                @foreach (\App\Models\Pembayaran::JENIS as $v => $l)<option value="{{ $v }}" x-show="'{{ $v }}' !== 'angsuran' || jenis === 'angsuran'">{{ $l }}</option>@endforeach
                            </select>
                        </x-field>
                        <x-field label="Nominal" name="bayar_nominal" hint="Kosongkan bila belum ada uang masuk">
                            <x-money name="bayar_nominal" :value="$awal['bayar'] ?: null"/>
                        </x-field>
                        <x-field label="Metode" name="bayar_metode">
                            <x-select name="bayar_metode" :options="\App\Models\Pembayaran::METODE"/>
                        </x-field>
                        <x-field label="No. Bukti / Referensi" name="bayar_no_bukti">
                            <x-input name="bayar_no_bukti" placeholder="Opsional"/>
                        </x-field>
                    </div>
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
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Pokok diangsur</dt><dd class="tabular-nums" x-text="rupiah(pokok)"></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Tenor</dt><dd x-text="tenor + ' bulan'"></dd></div>
                            <div class="flex justify-between gap-3 rounded-lg bg-forest-50 px-3 py-2"><dt class="text-forest-700">Cicilan / bulan</dt><dd class="font-bold text-forest-700 tabular-nums" x-text="rupiah(cicilan)"></dd></div>
                            <div class="flex justify-between gap-3" x-show="cicilanAkhir !== cicilan"><dt class="text-slate-500">Cicilan terakhir</dt><dd class="tabular-nums" x-text="rupiah(cicilanAkhir)"></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500">Jatuh tempo pertama</dt><dd x-text="jatuhTempoPertama"></dd></div>
                        </div>
                    </template>
                    <div class="border-t border-slate-100 pt-2.5 text-xs text-slate-500">
                        Di luar harga: reservasi {{ rupiah($aturan['biaya_reservasi']) }} + booking {{ rupiah($aturan['biaya_booking']) }}.
                    </div>
                </dl>
                <div class="mt-5 flex flex-col gap-2">
                    <button type="submit" class="btn btn-primary w-full" :disabled="!kavling || !!errors.dp"><x-icon name="check" class="h-4 w-4"/> {{ $ubah ? 'Simpan Perubahan' : 'Simpan Transaksi' }}</button>
                    <a href="{{ $ubah ? route('transaksi-penjualan.show', $transaksi) : route('transaksi-penjualan.index') }}" class="btn btn-secondary w-full">Batal</a>
                </div>
            </x-card>
        </aside>
    </div>
</form>
@endsection

@push('scripts')
<script>
    function transaksiForm(awal, kavlings, aturan) {
        return {
            ...awal, kavlings, aturan, q: '', hasil: [], sudahCari: false,
            get kavling() { return this.kavlings.find(k => k.id === Number(this.kavlingId)) || null },
            get harga() { return this.kavling?.harga || 0 },
            get pokok() { return Math.max(0, this.harga - this.dp) },
            get cicilan() { return this.tenor > 0 ? Math.floor(this.pokok / this.tenor) : 0 },
            get cicilanAkhir() { return this.pokok - this.cicilan * (this.tenor - 1) },
            get persenDp() { return this.harga ? (this.dp / this.harga * 100).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + '%' : '0%' },
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
                if (this.harga && this.jenis === 'angsuran' && this.dp >= this.harga) return { dp: 'DP sama dengan harga jual — pilih Cash.' };
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
