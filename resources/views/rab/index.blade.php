@extends('layouts.app')

@section('content')
<style>[x-cloak]{display:none !important;}</style>

<div x-data="rabPage()" @keydown.escape.window="open = false" class="p-6">

    {{-- ===== Header ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">RAB &amp; Realisasi</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola rencana anggaran dan pantau realisasi keuangan proyek.</p>
        </div>
        <button type="button" @click="openTambah()"
            class="inline-flex items-center gap-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2.5 shadow-sm transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Tambah RAB
        </button>
    </div>

    {{-- ===== Notifikasi ===== --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===== Ringkasan ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Anggaran</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 tabular-nums">Rp{{ number_format($totalAnggaran, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-gray-200 p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Realisasi</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 tabular-nums">Rp{{ number_format($totalRealisasi, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-gray-200 p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Selisih</p>
            <p class="mt-2 text-2xl font-bold tabular-nums {{ $totalSelisih < 0 ? 'text-red-600' : 'text-green-600' }}">
                Rp{{ number_format($totalSelisih, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white border border-gray-200 p-5 shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Status Keuangan</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20">
                    {{ $jumlahSesuai }} Sesuai
                </span>
                <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20">
                    {{ $jumlahBelum }} Belum
                </span>
            </div>
        </div>
    </div>

    {{-- ===== Filter ===== --}}
    <form method="GET" class="flex flex-col md:flex-row gap-3 mb-4">
        <div class="relative flex-1">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.2-5.2m0 0A7.5 7.5 0 105.2 5.2a7.5 7.5 0 0010.6 10.6z"/></svg>
            <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari kategori atau uraian RAB..."
                class="w-full bg-white border border-gray-200 pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400">
        </div>
        <select name="kategori" onchange="this.form.submit()"
            class="bg-white border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10">
            <option value="semua">Semua Kategori</option>
            @foreach ($kategoriList as $k)
                <option value="{{ $k }}" @selected(request('kategori') == $k)>{{ $k }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()"
            class="bg-white border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10">
            <option value="semua">Semua Status</option>
            <option value="belum_direalisasikan" @selected(request('status') == 'belum_direalisasikan')>Belum Direalisasikan</option>
            <option value="sudah_direalisasikan" @selected(request('status') == 'sudah_direalisasikan')>Sudah Direalisasikan</option>
        </select>
        <button type="submit"
            class="bg-white border border-gray-200 hover:bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 transition">
            Cari
        </button>
    </form>

    {{-- ===== Tabel ===== --}}
    <div class="bg-white border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-900">Rencana Anggaran Biaya</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left font-semibold px-5 py-3">Kategori</th>
                        <th class="text-left font-semibold px-5 py-3">Uraian</th>
                        <th class="text-right font-semibold px-5 py-3">Anggaran</th>
                        <th class="text-right font-semibold px-5 py-3">Realisasi</th>
                        <th class="text-right font-semibold px-5 py-3">Selisih</th>
                        <th class="text-left font-semibold px-5 py-3">Status</th>
                        <th class="text-left font-semibold px-5 py-3">Catatan</th>
                        <th class="text-center font-semibold px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($rabs as $rab)
                        @php
                            $badge = match($rab->status_keuangan) {
                                'sesuai' => 'bg-green-50 text-green-700 ring-green-600/20',
                                'kurang' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                'lebih'  => 'bg-red-50 text-red-700 ring-red-600/20',
                                default  => 'bg-gray-50 text-gray-600 ring-gray-500/20',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="inline-block text-xs font-medium px-2 py-1 bg-gray-100 text-gray-700">{{ $rab->kategori }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $rab->uraian }}</td>
                            <td class="px-5 py-3.5 text-right tabular-nums whitespace-nowrap">{{ $rab->anggaran ? 'Rp'.number_format($rab->anggaran,0,',','.') : '—' }}</td>
                            <td class="px-5 py-3.5 text-right tabular-nums whitespace-nowrap">{{ $rab->anggaran ? 'Rp'.number_format($rab->realisasi,0,',','.') : '—' }}</td>
                            <td class="px-5 py-3.5 text-right tabular-nums whitespace-nowrap {{ !is_null($rab->selisih) && $rab->selisih < 0 ? 'text-red-600' : 'text-gray-900' }}">
                                {{ !is_null($rab->selisih) ? 'Rp'.number_format($rab->selisih,0,',','.') : '—' }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 ring-1 ring-inset {{ $badge }}">
                                    {{ strtoupper($rab->status_keuangan_label) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-500 max-w-[200px] truncate" title="{{ $rab->catatan }}">{{ $rab->catatan ?: '-' }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1">

                                    {{-- Verifikasi --}}
                                    <form action="{{ route('rab.verifikasi', $rab) }}" method="POST"
                                          onsubmit="return confirm('Tandai RAB ini sesuai realisasi?')">
                                        @csrf @method('PATCH')
                                        <button type="submit" title="Verifikasi" aria-label="Verifikasi"
                                            class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 transition">
                                            <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        </button>
                                    </form>

                                    {{-- Edit --}}
                                    <button type="button" title="Edit" aria-label="Edit"
                                        @click="openEdit(@js($rab->only(['id','kategori','uraian','anggaran','realisasi','status_realisasi','catatan'])))"
                                        class="p-2 text-gray-400 hover:text-gray-900 hover:bg-gray-100 transition">
                                        <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                    </button>

                                    {{-- Hapus --}}
                                    <form action="{{ route('rab.destroy', $rab) }}" method="POST"
                                          onsubmit="return confirm('Hapus data RAB ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Hapus" aria-label="Hapus"
                                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 transition">
                                            <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <p class="text-sm font-medium text-gray-900">Belum ada data RAB</p>
                                <p class="mt-1 text-sm text-gray-500">Klik "Tambah RAB" untuk membuat rencana anggaran pertama.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== MODAL TAMBAH / EDIT ===== --}}
    <div x-show="open" x-cloak
         x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
        <div class="bg-white w-full max-w-xl shadow-2xl max-h-[92vh] flex flex-col"
             @click.outside="open = false"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <form :action="mode === 'edit' ? editUrl : '{{ route('rab.store') }}'" method="POST" class="flex flex-col min-h-0">
                @csrf
                <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">

                {{-- Header --}}
                <div class="flex items-start justify-between px-6 py-5 border-b border-gray-200">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900" x-text="mode === 'edit' ? 'Edit RAB' : 'Tambah RAB'"></h2>
                        <p class="mt-0.5 text-sm text-gray-500">Rencana anggaran biaya untuk kebutuhan proyek.</p>
                    </div>
                    <button type="button" @click="open = false" aria-label="Tutup"
                        class="p-1.5 -mr-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-5 space-y-5 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                            <select name="kategori" x-model="form.kategori" required
                                class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-500">
                                <option value="">Pilih kategori</option>
                                <option value="Tanah">Tanah</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Operasional">Operasional</option>
                                <option value="Cadangan">Cadangan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                            <select name="status_realisasi" x-model="form.status_realisasi"
                                class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-500">
                                <option value="belum_direalisasikan">Belum Direalisasikan</option>
                                <option value="sudah_direalisasikan">Sudah Direalisasikan</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Uraian <span class="text-red-500">*</span></label>
                        <input type="text" name="uraian" x-model="form.uraian" required placeholder="Contoh: Brosur & Ads Digital"
                            class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-500">
                    </div>

                    {{-- Nominal --}}
                    <div class="border border-gray-200 bg-gray-50 p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                            {{-- Anggaran --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Anggaran <span class="text-red-500">*</span></label>
                                <div class="flex items-center bg-white border border-gray-300 px-3 focus-within:ring-2 focus-within:ring-gray-900/10 focus-within:border-gray-500">
                                    <span class="text-sm text-gray-400 mr-2">Rp</span>
                                    <input type="text" inputmode="numeric" autocomplete="off" placeholder="0" required
                                        :value="rupiah(form.anggaran)"
                                        @input="form.anggaran = parseRupiah($event); $event.target.value = rupiah(form.anggaran)"
                                        class="w-full py-2.5 text-sm text-right tabular-nums outline-none bg-transparent">
                                </div>
                                <input type="hidden" name="anggaran" :value="form.anggaran">
                            </div>

                            {{-- Realisasi --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Realisasi Saat Ini</label>
                                <div class="flex items-center border border-gray-300 px-3"
                                     :class="mode === 'edit' ? 'bg-white focus-within:ring-2 focus-within:ring-gray-900/10 focus-within:border-gray-500' : 'bg-gray-100'">
                                    <span class="text-sm text-gray-400 mr-2">Rp</span>
                                    <input type="text" inputmode="numeric" autocomplete="off" placeholder="0"
                                        :value="rupiah(form.realisasi)"
                                        :disabled="mode !== 'edit'"
                                        @input="form.realisasi = parseRupiah($event); $event.target.value = rupiah(form.realisasi)"
                                        class="w-full py-2.5 text-sm text-right tabular-nums outline-none bg-transparent disabled:text-gray-500">
                                </div>
                                <input type="hidden" name="realisasi" :value="form.realisasi" :disabled="mode !== 'edit'">
                            </div>

                            {{-- Selisih --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Selisih (Sisa)</label>
                                <div class="flex items-center border px-3"
                                     :class="selisih() < 0 ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200'">
                                    <span class="text-sm text-gray-400 mr-2">Rp</span>
                                    <input type="text" readonly tabindex="-1" :value="rupiah(selisih())"
                                        class="w-full py-2.5 text-sm text-right tabular-nums font-semibold outline-none bg-transparent"
                                        :class="selisih() < 0 ? 'text-red-600' : 'text-green-700'">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Catatan Tambahan</label>
                        <textarea name="catatan" x-model="form.catatan" rows="3" placeholder="Masukkan catatan jika diperlukan..."
                            class="w-full border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-500"></textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <button type="button" @click="open = false"
                        class="bg-white border border-gray-300 hover:bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium px-5 py-2.5 shadow-sm transition">
                        Simpan RAB
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function rabPage() {
    const kosong = () => ({
        kategori: '', uraian: '', anggaran: 0, realisasi: 0,
        status_realisasi: 'belum_direalisasikan', catatan: '',
    });

    // URL update dibuat dari route Laravel (hasilnya /rab-realisasi/{id})
    const updateUrlTemplate = @js(route('rab.update', '__ID__'));

    return {
        open: false,
        mode: 'tambah',
        editUrl: '',
        form: kosong(),

        openTambah() {
            this.mode = 'tambah';
            this.editUrl = '';
            this.form = kosong();
            this.open = true;
        },

        openEdit(rab) {
            this.mode = 'edit';
            this.editUrl = updateUrlTemplate.replace('__ID__', rab.id);
            this.form = {
                kategori: rab.kategori ?? '',
                uraian: rab.uraian ?? '',
                anggaran: Math.round(Number(rab.anggaran) || 0),
                realisasi: Math.round(Number(rab.realisasi) || 0),
                status_realisasi: rab.status_realisasi ?? 'belum_direalisasikan',
                catatan: rab.catatan ?? '',
            };
            this.open = true;
        },

        rupiah(n) {
            n = Number(n) || 0;
            if (n === 0) return '';
            const minus = n < 0 ? '-' : '';
            return minus + new Intl.NumberFormat('id-ID').format(Math.abs(n));
        },

        parseRupiah(e) {
            const digit = e.target.value.replace(/\D/g, '');
            return digit ? parseInt(digit, 10) : 0;
        },

        selisih() {
            return (Number(this.form.anggaran) || 0) - (Number(this.form.realisasi) || 0);
        },
    }
}
</script>
@endsection