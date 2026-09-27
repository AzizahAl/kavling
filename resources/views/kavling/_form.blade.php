@if($errors->any())
    <div class="rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Kavling</label>
        <input type="text" name="kode_kavling" class="w-full border rounded-lg px-3 py-2 text-sm"
            value="{{ old('kode_kavling', $kavling->kode_kavling ?? '') }}" placeholder="TR-A01">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Blok</label>
        <input type="text" name="blok" class="w-full border rounded-lg px-3 py-2 text-sm"
            value="{{ old('blok', $kavling->blok ?? '') }}" placeholder="A">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">No</label>
        <input type="text" name="no" class="w-full border rounded-lg px-3 py-2 text-sm"
            value="{{ old('no', $kavling->no ?? '') }}" placeholder="A1">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
        <input type="text" name="tipe" class="w-full border rounded-lg px-3 py-2 text-sm"
            value="{{ old('tipe', $kavling->tipe ?? '') }}" placeholder="Prima">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran</label>
        <input type="text" name="ukuran" class="w-full border rounded-lg px-3 py-2 text-sm"
            value="{{ old('ukuran', $kavling->ukuran ?? '') }}" placeholder="7 x 14">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Luas (m²)</label>
        <input type="number" step="0.01" name="luas" class="w-full border rounded-lg px-3 py-2 text-sm"
            value="{{ old('luas', $kavling->luas ?? '') }}" placeholder="98">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Harga per m²</label>
        <input type="number" step="0.01" name="harga_per_m2" class="w-full border rounded-lg px-3 py-2 text-sm"
            value="{{ old('harga_per_m2', $kavling->harga_per_m2 ?? '') }}" placeholder="500000">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual</label>
        <input type="number" step="0.01" name="harga_jual" class="w-full border rounded-lg px-3 py-2 text-sm"
            value="{{ old('harga_jual', $kavling->harga_jual ?? '') }}" placeholder="49000000">
    </div>

    <div class="col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
            @foreach(['tersedia', 'reservasi', 'booking', 'dp', 'terjual'] as $status)
                <option value="{{ $status }}"
                    {{ old('status', $kavling->status ?? '') == $status ? 'selected' : '' }}>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
    </div>
</div>