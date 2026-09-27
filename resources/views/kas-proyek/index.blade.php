@extends('layouts.app')

@section('content')
<div x-data="kasPage()" class="p-6">

    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kas Proyek</h1>
            <p class="text-sm text-gray-500">Monitor seluruh arus kas masuk dan keluar proyek.</p>
        </div>
        <button @click="openTambah()"
            class="flex items-center gap-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Transaksi
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-2">
            {{ session('success') }}
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border rounded-2xl p-5 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Masuk</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp{{ number_format($totalMasuk, 0, ',', '.') }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-green-50 text-green-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l-6.75-6.75M12 19.5l6.75-6.75" />
                </svg>
            </div>
        </div>
        <div class="bg-white border rounded-2xl p-5 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Keluar</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp{{ number_format($totalKeluar, 0, ',', '.') }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-red-50 text-red-500 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l-6.75 6.75M12 4.5l6.75 6.75" />
                </svg>
            </div>
        </div>
        <div class="bg-white border rounded-2xl p-5 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Saldo Proyek</p>
                <p class="text-2xl font-bold text-green-600 mt-1">Rp{{ number_format($saldoProyek, 0, ',', '.') }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-4.5-9h15a1.5 1.5 0 011.5 1.5v9a1.5 1.5 0 01-1.5 1.5h-15a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 011.5-1.5z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Tabs + Search -->
    <form method="GET" class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
        <div class="flex bg-gray-100 rounded-xl p-1 w-fit">
            @foreach (['semua' => 'Semua', 'masuk' => 'Masuk', 'keluar' => 'Keluar'] as $val => $label)
                <button type="submit" name="tab" value="{{ $val }}"
                    class="px-4 py-1.5 text-sm rounded-lg font-medium transition
                        {{ request('tab', 'semua') == $val ? 'bg-white shadow text-gray-900' : 'text-gray-500' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
        <div class="flex gap-3">
            <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari transaksi..."
                class="border rounded-xl px-3.5 py-2 text-sm w-64">
            <select name="kategori" onchange="this.form.submit()" class="border rounded-xl px-3.5 py-2 text-sm">
                <option value="semua">Semua Kategori</option>
                @foreach ($kategoriList as $k)
                    <option value="{{ $k }}" @selected(request('kategori') == $k)>{{ $k }}</option>
                @endforeach
            </select>
            <button type="submit" class="border rounded-xl px-4 py-2 text-sm text-gray-600">Filter</button>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Tanggal</th>
                    <th class="text-left px-4 py-3">Kode</th>
                    <th class="text-left px-4 py-3">Kategori</th>
                    <th class="text-left px-4 py-3">Uraian</th>
                    <th class="text-right px-4 py-3">Masuk</th>
                    <th class="text-right px-4 py-3">Keluar</th>
                    <th class="text-right px-4 py-3">Saldo</th>
                    <th class="text-left px-4 py-3">Sumber/Transaksi</th>
                    <th class="text-left px-4 py-3">Catatan</th>
                    <th class="text-center px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($transaksis as $t)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $t->tanggal->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $t->kode }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-1 rounded-full bg-gray-100 text-gray-600">{{ $t->kategori }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $t->uraian }}</td>
                        <td class="px-4 py-3 text-right text-green-600 font-medium">
                            {{ $t->jenis === 'masuk' ? '+ Rp'.number_format($t->nominal,0,',','.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right text-red-500 font-medium">
                            {{ $t->jenis === 'keluar' ? '- Rp'.number_format($t->nominal,0,',','.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold {{ $t->saldo_berjalan < 0 ? 'text-red-500' : 'text-gray-900' }}">
                            Rp{{ number_format($t->saldo_berjalan, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->sumber ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->catatan ?: '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-3">
                                <button type="button" class="text-gray-500 hover:text-gray-800" title="Edit"
                                    @click="openEdit({{ $t->id }}, '{{ $t->tanggal->format('Y-m-d') }}', '{{ $t->kode }}', '{{ $t->kategori }}', '{{ $t->jenis }}', '{{ addslashes($t->uraian) }}', {{ $t->nominal }}, '{{ addslashes($t->sumber ?? '') }}', '{{ addslashes($t->catatan ?? '') }}')">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5v5.25a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18.75V6.75a2.25 2.25 0 012.25-2.25h5.25" />
                                    </svg>
                                </button>
                                <form action="{{ route('kas-proyek.destroy', $t) }}" method="POST" onsubmit="return confirm('Hapus transaksi ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-6 text-center text-gray-400">Belum ada transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- MODAL TAMBAH / EDIT TRANSAKSI KAS -->
    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4" style="display:none;">

        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="open = false"
            class="w-full max-w-xl bg-white rounded-2xl shadow-2xl overflow-hidden">

            <form :action="mode === 'edit' ? editUrl : '{{ route('kas-proyek.store') }}'" method="POST">
                @csrf
                <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">

                <!-- Header -->
                <div class="relative bg-gradient-to-r from-gray-900 to-gray-700 px-6 py-5">
                    <button type="button" @click="open = false"
                        class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center text-white">
                            <svg x-show="mode === 'edit'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5v5.25a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18.75V6.75a2.25 2.25 0 012.25-2.25h5.25" />
                            </svg>
                            <svg x-show="mode !== 'edit'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-4.5-9h15a1.5 1.5 0 011.5 1.5v9a1.5 1.5 0 01-1.5 1.5h-15a1.5 1.5 0 01-1.5-1.5v-9a1.5 1.5 0 011.5-1.5z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-white" x-text="mode === 'edit' ? 'Edit Transaksi Kas' : 'Tambah Transaksi Kas'"></h2>
                            <p class="text-xs text-gray-300">Catat transaksi pemasukan atau pengeluaran kas proyek</p>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="px-6 py-5 space-y-5 max-h-[75vh] overflow-y-auto">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Tanggal Transaksi</label>
                            <input type="date" name="tanggal" x-model="form.tanggal" required
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Kode Transaksi</label>
                            <input type="text" name="kode" x-model="form.kode" required
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 outline-none transition">
                            <button type="button" @click="generateKode()" class="flex items-center gap-1 text-[11px] text-gray-400 hover:text-gray-600 mt-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                Buat kode otomatis
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Kategori</label>
                            <select name="kategori" x-model="form.kategori" required
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 outline-none transition">
                                <option value="">Pilih kategori</option>
                                <option value="Penjualan">Penjualan</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Operasional">Operasional</option>
                                <option value="Tanah">Tanah</option>
                                <option value="Cadangan">Cadangan</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Jenis Transaksi</label>
                            <div class="grid grid-cols-2 gap-1.5 p-1 bg-gray-100 rounded-xl">
                                <button type="button" @click="setJenis('masuk')"
                                    :class="form.jenis === 'masuk' ? 'bg-gray-900 text-white shadow' : 'text-gray-500'"
                                    class="text-xs font-medium py-1.5 rounded-lg transition">Pemasukan</button>
                                <button type="button" @click="setJenis('keluar')"
                                    :class="form.jenis === 'keluar' ? 'bg-gray-900 text-white shadow' : 'text-gray-500'"
                                    class="text-xs font-medium py-1.5 rounded-lg transition">Pengeluaran</button>
                            </div>
                            <input type="hidden" name="jenis" :value="form.jenis">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Uraian</label>
                        <input type="text" name="uraian" x-model="form.uraian" required
                            placeholder="Contoh: Biaya iklan Meta Ads Q4"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                            Nominal <span x-text="form.jenis === 'masuk' ? '(Masuk)' : '(Keluar)'"></span>
                        </label>
                        <div class="flex items-center gap-1 border border-gray-200 rounded-xl px-3.5 py-2.5 bg-gray-50 focus-within:bg-white focus-within:ring-2 focus-within:ring-gray-900/10 focus-within:border-gray-400 transition">
                            <span class="text-sm text-gray-400 font-medium">Rp</span>
                            <input type="number" min="0" step="1" name="nominal" x-model.number="form.nominal" required
                                class="w-full bg-transparent text-sm font-semibold text-gray-900 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Sumber / Transaksi</label>
                        <input type="text" name="sumber" x-model="form.sumber" list="sumber-list"
                            placeholder="Contoh: Rekening Operasional, Kas Kecil Proyek"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 outline-none transition">
                        <datalist id="sumber-list">
                            <option value="Rekening Operasional">
                            <option value="Kas Kecil Proyek">
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Catatan</label>
                        <textarea name="catatan" x-model="form.catatan" rows="2"
                            placeholder="Tambahkan catatan transaksi jika diperlukan..."
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-gray-50 focus:bg-white focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 outline-none transition resize-none"></textarea>
                    </div>

                    <!-- Ringkasan -->
                    <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3">
                        <p class="text-[11px] font-semibold text-green-700 uppercase tracking-wide mb-2">Ringkasan Transaksi</p>
                        <div class="grid grid-cols-3 gap-3 text-sm">
                            <div>
                                <p class="text-xs text-gray-500">Jenis Transaksi</p>
                                <p class="font-semibold" :class="form.jenis === 'masuk' ? 'text-green-700' : 'text-red-600'"
                                    x-text="form.jenis === 'masuk' ? 'Pemasukan' : 'Pengeluaran'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Kategori</p>
                                <p class="font-semibold text-gray-800" x-text="form.kategori || '-'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Nominal</p>
                                <p class="font-semibold text-gray-900" x-text="'Rp' + new Intl.NumberFormat('id-ID').format(form.nominal || 0)"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <button type="button" @click="open = false"
                        class="px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-200/60 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 rounded-xl shadow-sm transition">
                        <span x-text="mode === 'edit' ? 'Simpan Perubahan' : 'Simpan Transaksi'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function kasPage() {
    return {
        open: false,
        mode: 'tambah',
        editUrl: '',
        nextKodeMasuk: '{{ $nextKodeMasuk }}',
        nextKodeKeluar: '{{ $nextKodeKeluar }}',
        form: {
            tanggal: new Date().toISOString().slice(0,10),
            kode: '',
            kategori: '',
            jenis: 'masuk',
            uraian: '',
            nominal: 0,
            sumber: '',
            catatan: '',
        },
        openTambah() {
            this.mode = 'tambah';
            this.editUrl = '';
            this.form = {
                tanggal: new Date().toISOString().slice(0,10),
                kode: this.nextKodeMasuk,
                kategori: '', jenis: 'masuk', uraian: '', nominal: 0, sumber: '', catatan: '',
            };
            this.open = true;
        },
        openEdit(id, tanggal, kode, kategori, jenis, uraian, nominal, sumber, catatan) {
            this.mode = 'edit';
            this.editUrl = `/kas-proyek/${id}`;
            this.form = { tanggal, kode, kategori, jenis, uraian, nominal, sumber, catatan };
            this.open = true;
        },
        setJenis(jenis) {
            this.form.jenis = jenis;
            if (this.mode === 'tambah') {
                this.form.kode = jenis === 'masuk' ? this.nextKodeMasuk : this.nextKodeKeluar;
            }
        },
        generateKode() {
            this.form.kode = this.form.jenis === 'masuk' ? this.nextKodeMasuk : this.nextKodeKeluar;
        }
    }
}
</script>
@endsection