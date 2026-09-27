@if($errors->any())
    <div class="rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Informasi Agen --}}
<div>
    <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Informasi Agen</p>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Agen</label>
            <input type="text" name="kode_agen" class="w-full border rounded-lg px-3 py-2 text-sm"
                value="{{ old('kode_agen', $agen->kode_agen ?? '') }}" placeholder="AG-005">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
            <input type="text" name="no_hp" class="w-full border rounded-lg px-3 py-2 text-sm"
                value="{{ old('no_hp', $agen->no_hp ?? '') }}" placeholder="0812-3456-7890">
        </div>
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Agen</label>
            <input type="text" name="nama_agen" class="w-full border rounded-lg px-3 py-2 text-sm"
                value="{{ old('nama_agen', $agen->nama_agen ?? '') }}" placeholder="Masukkan nama lengkap agen">
        </div>
    </div>
</div>

<hr>

{{-- Data Performa Penjualan --}}
<div>
    <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Data Performa Penjualan</p>
    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lead</label>
            <input type="number" name="lead" class="w-full border rounded-lg px-3 py-2 text-sm"
                value="{{ old('lead', $agen->lead ?? 0) }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Prospek</label>
            <input type="number" name="prospek" class="w-full border rounded-lg px-3 py-2 text-sm"
                value="{{ old('prospek', $agen->prospek ?? 0) }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Closing</label>
            <input type="number" name="closing" class="w-full border rounded-lg px-3 py-2 text-sm"
                value="{{ old('closing', $agen->closing ?? 0) }}">
        </div>
    </div>
</div>

<hr>

{{-- Penjualan & Komisi --}}
<div>
    <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Penjualan & Komisi</p>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Penjualan</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                <input type="number" step="0.01" id="nilai_penjualan" name="nilai_penjualan"
                    class="w-full border rounded-lg pl-8 pr-3 py-2 text-sm"
                    value="{{ old('nilai_penjualan', $agen->nilai_penjualan ?? 0) }}">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Komisi (%)</label>
            <div class="relative">
                <input type="number" step="0.01" id="komisi_persen" name="komisi_persen"
                    class="w-full border rounded-lg pl-3 pr-8 py-2 text-sm"
                    value="{{ old('komisi_persen', $agen->komisi_persen ?? 0) }}">
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">%</span>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Komisi Terhitung</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                <input type="number" id="komisi_terhitung" name="komisi_terhitung" readonly
                    class="w-full border rounded-lg pl-8 pr-3 py-2 text-sm bg-blue-50 text-gray-500"
                    value="{{ old('komisi_terhitung', $agen->komisi_terhitung ?? 0) }}">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dibayar</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                <input type="number" step="0.01" id="dibayar" name="dibayar"
                    class="w-full border rounded-lg pl-8 pr-3 py-2 text-sm"
                    value="{{ old('dibayar', $agen->dibayar ?? 0) }}">
            </div>
        </div>
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Sisa Komisi</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                <input type="number" id="sisa_komisi" name="sisa_komisi" readonly
                    class="w-full border rounded-lg pl-8 pr-3 py-2 text-sm bg-blue-50 text-gray-500"
                    value="{{ old('sisa_komisi', $agen->sisa_komisi ?? 0) }}">
            </div>
        </div>
    </div>
</div>