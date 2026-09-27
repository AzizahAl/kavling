@extends('layouts.app')

@section('content')
<div x-data="rabPage()" class="p-6">

    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">RAB & Realisasi</h1>
            <p class="text-sm text-gray-500">Kelola rencana anggaran dan pantau realisasi keuangan proyek.</p>
        </div>
        <button @click="openTambah()"
            class="flex items-center gap-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-lg">
            <span>+</span> Tambah RAB
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-2">
            {{ session('success') }}
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase">Total Anggaran</p>
            <p class="text-xl font-bold text-gray-900">Rp{{ number_format($totalAnggaran, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase">Total Realisasi</p>
            <p class="text-xl font-bold text-gray-900">Rp{{ number_format($totalRealisasi, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase">Total Selisih</p>
            <p class="text-xl font-bold text-green-600">Rp{{ number_format($totalSelisih, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase">Status Keuangan</p>
            <div class="flex gap-2 mt-1">
                <span class="text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-700">{{ $jumlahSesuai }} Sesuai</span>
                <span class="text-xs font-medium px-2 py-1 rounded-full bg-gray-100 text-gray-600">{{ $jumlahBelum }} Belum</span>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <form method="GET" class="flex flex-col md:flex-row gap-3 mb-4">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari kategori atau uraian RAB..."
            class="flex-1 border rounded-lg px-3 py-2 text-sm">
        <select name="kategori" class="border rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="semua">Semua Kategori</option>
            @foreach ($kategoriList as $k)
                <option value="{{ $k }}" @selected(request('kategori') == $k)>{{ $k }}</option>
            @endforeach
        </select>
        <select name="status" class="border rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="semua">Semua Status</option>
            <option value="belum_direalisasikan" @selected(request('status') == 'belum_direalisasikan')>Belum Direalisasikan</option>
            <option value="sudah_direalisasikan" @selected(request('status') == 'sudah_direalisasikan')>Sudah Direalisasikan</option>
        </select>
        <button type="submit" class="border rounded-lg px-3 py-2 text-sm text-gray-500">Cari</button>
    </form>

    <!-- Table -->
    <div class="bg-white border rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b font-semibold text-gray-900">Rencana Anggaran Biaya</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Kategori</th>
                    <th class="text-left px-4 py-3">Uraian</th>
                    <th class="text-right px-4 py-3">Anggaran</th>
                    <th class="text-right px-4 py-3">Realisasi</th>
                    <th class="text-right px-4 py-3">Selisih</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Catatan</th>
                    <th class="text-center px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($rabs as $rab)
                    <tr>
                        <td class="px-4 py-3">{{ $rab->kategori }}</td>
                        <td class="px-4 py-3">{{ $rab->uraian }}</td>
                        <td class="px-4 py-3 text-right">{{ $rab->anggaran ? 'Rp'.number_format($rab->anggaran,0,',','.') : '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ $rab->anggaran ? 'Rp'.number_format($rab->realisasi,0,',','.') : '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ !is_null($rab->selisih) ? 'Rp'.number_format($rab->selisih,0,',','.') : '—' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $badgeColor = match($rab->status_keuangan) {
                                    'sesuai' => 'bg-green-100 text-green-700',
                                    'kurang' => 'bg-yellow-100 text-yellow-700',
                                    'lebih'  => 'bg-red-100 text-red-700',
                                    default  => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            <span class="text-xs font-medium px-2 py-1 rounded-full {{ $badgeColor }}">
                                {{ strtoupper($rab->status_keuangan_label) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $rab->catatan ?: '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-3">
                                <form action="{{ route('rab.verifikasi', $rab) }}" method="POST" onsubmit="return confirm('Tandai RAB ini sesuai realisasi?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-green-600" title="Verifikasi">✓</button>
                                </form>
                                <button type="button" class="text-gray-500" title="Edit"
                                    @click="openEdit({{ $rab->id }}, '{{ $rab->kategori }}', '{{ addslashes($rab->uraian) }}', {{ $rab->anggaran ?? 0 }}, {{ $rab->realisasi }}, '{{ $rab->status_realisasi }}', '{{ addslashes($rab->catatan ?? '') }}')">
                                    ✏️
                                </button>
                                <form action="{{ route('rab.destroy', $rab) }}" method="POST" onsubmit="return confirm('Hapus data RAB ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500" title="Hapus">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-gray-400">Belum ada data RAB.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- MODAL TAMBAH / EDIT RAB -->
    <div x-show="open" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" style="display:none;">
        <div class="bg-white rounded-xl w-full max-w-lg shadow-xl" @click.outside="open = false">
            <form :action="mode === 'edit' ? editUrl : '{{ route('rab.store') }}'" method="POST">
                @csrf
                <template x-if="mode === 'edit'">
                    @method('PUT')
                </template>
                <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">

                <div class="flex items-start justify-between px-6 py-4 border-b">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900" x-text="mode === 'edit' ? 'Edit RAB' : 'Tambah RAB'"></h2>
                        <p class="text-xs text-gray-500">Tambahkan rencana anggaran biaya untuk kebutuhan proyek.</p>
                    </div>
                    <button type="button" @click="open = false" class="text-gray-400">✕</button>
                </div>

                <div class="px-6 py-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Kategori <span class="text-red-500">*</span></label>
                            <select name="kategori" x-model="form.kategori" required class="mt-1 w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="">Pilih kategori</option>
                                <option value="Tanah">Tanah</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Operasional">Operasional</option>
                                <option value="Cadangan">Cadangan</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700">Status</label>
                            <select name="status_realisasi" x-model="form.status_realisasi" class="mt-1 w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="belum_direalisasikan">Belum Direalisasikan</option>
                                <option value="sudah_direalisasikan">Sudah Direalisasikan</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Uraian <span class="text-red-500">*</span></label>
                        <input type="text" name="uraian" x-model="form.uraian" required
                            class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" placeholder="Contoh: Brosur & Ads Digital">
                    </div>

                    <div class="grid grid-cols-3 gap-3 bg-gray-50 border rounded-lg p-4">
                        <div>
                            <label class="text-xs font-medium text-gray-700">Anggaran <span class="text-red-500">*</span></label>
                            <div class="mt-1 flex items-center border rounded-lg bg-white px-2">
                                <span class="text-xs text-gray-400 mr-1">Rp</span>
                                <input type="number" min="0" step="1" name="anggaran" x-model.number="form.anggaran"
                                    required class="w-full py-2 text-sm text-right outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700">Realisasi Saat Ini</label>
                            <div class="mt-1 flex items-center border rounded-lg bg-gray-100 px-2">
                                <span class="text-xs text-gray-400 mr-1">Rp</span>
                                <input type="number" name="realisasi" x-model.number="form.realisasi"
                                    :disabled="mode !== 'edit'" class="w-full py-2 text-sm text-right bg-gray-100 outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700">Selisih (Sisa)</label>
                            <div class="mt-1 flex items-center border rounded-lg bg-green-50 px-2">
                                <span class="text-xs text-gray-400 mr-1">Rp</span>
                                <input type="text" readonly :value="selisihFormatted()"
                                    class="w-full py-2 text-sm text-right bg-green-50 outline-none">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">Catatan Tambahan</label>
                        <textarea name="catatan" x-model="form.catatan" rows="3"
                            class="mt-1 w-full border rounded-lg px-3 py-2 text-sm" placeholder="Masukkan catatan jika diperlukan..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 py-4 border-t bg-gray-50 rounded-b-xl">
                    <button type="button" @click="open = false" class="border rounded-lg px-4 py-2 text-sm text-gray-700">Batal</button>
                    <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-lg">
                        Simpan RAB
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function rabPage() {
    return {
        open: false,
        mode: 'tambah',
        editUrl: '',
        form: {
            kategori: '',
            uraian: '',
            anggaran: 0,
            realisasi: 0,
            status_realisasi: 'belum_direalisasikan',
            catatan: '',
        },
        openTambah() {
            this.mode = 'tambah';
            this.editUrl = '';
            this.form = { kategori:'', uraian:'', anggaran:0, realisasi:0, status_realisasi:'belum_direalisasikan', catatan:'' };
            this.open = true;
        },
        openEdit(id, kategori, uraian, anggaran, realisasi, status, catatan) {
            this.mode = 'edit';
            this.editUrl = `/rab/${id}`;
            this.form = { kategori, uraian, anggaran, realisasi, status_realisasi: status, catatan };
            this.open = true;
        },
        selisihFormatted() {
            const s = (this.form.anggaran || 0) - (this.form.realisasi || 0);
            return new Intl.NumberFormat('id-ID').format(s);
        }
    }
}
</script>
@endsection