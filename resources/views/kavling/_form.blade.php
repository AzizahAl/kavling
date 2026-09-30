@php
    $suffix = isset($kavling) ? $kavling->id : 'new';
    // old() cuma dipakai kalau error ini emang punya form yang SAMA (dicocokkan lewat edit_id)
    $isOldForThis = old('edit_id') !== null && (string) old('edit_id') === (string) $suffix;

    // Harga/m² selalu otomatis: kavling existing pakai harga tersimpan, kavling baru pakai harga tahap aktif
    $perM2Val  = isset($kavling) ? (int) $kavling->harga_per_m2 : (int) ($hargaAktif ?? 0);
    $namaTahap = isset($kavling)
        ? ($kavling->tahap->nama_tahap ?? '-')
        : (($tahapAktif->nama_tahap ?? '-'));
@endphp

@if($errors->any() && $isOldForThis)
    <div class="rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<input type="hidden" name="edit_id" value="{{ $suffix }}">

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Blok</label>
        <input type="text" id="blok_{{ $suffix }}" name="blok" maxlength="5" autocomplete="off"
            class="w-full border rounded-lg px-3 py-2 text-sm uppercase"
            value="{{ $isOldForThis ? old('blok') : ($kavling->blok ?? '') }}">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor</label>
        <input type="number" id="nomor_{{ $suffix }}" name="nomor" min="1" autocomplete="off"
            class="w-full border rounded-lg px-3 py-2 text-sm"
            value="{{ $isOldForThis ? old('nomor') : (isset($kavling) ? preg_replace('/\D/', '', $kavling->no) : '') }}">
    </div>
</div>

<div class="rounded-lg bg-gray-50 border px-3 py-2 mt-3 text-sm text-gray-600">
    No: <strong id="previewNo_{{ $suffix }}">{{ $kavling->no ?? '-' }}</strong>
    &nbsp;|&nbsp;
    Kode Kavling: <strong id="previewKode_{{ $suffix }}">{{ $kavling->kode_kavling ?? '-' }}</strong>
</div>

<div class="grid grid-cols-2 gap-4 mt-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
        <select id="tipe_{{ $suffix }}" name="tipe" autocomplete="off" class="w-full border rounded-lg px-3 py-2 text-sm">
            <option value="">-- Pilih Tipe --</option>
            @foreach(['Prima', 'Standard', 'Standard Hook'] as $t)
                <option value="{{ $t }}"
                    {{ ($isOldForThis ? old('tipe') : ($kavling->tipe ?? '')) == $t ? 'selected' : '' }}>
                    {{ $t }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran</label>
        <input type="text" id="ukuran_{{ $suffix }}" name="ukuran" autocomplete="off"
            class="w-full border rounded-lg px-3 py-2 text-sm"
            value="{{ $isOldForThis ? old('ukuran') : ($kavling->ukuran ?? '') }}">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Luas (m²)</label>
        <input type="number" step="1" id="luas_{{ $suffix }}" name="luas" autocomplete="off"
            class="w-full border rounded-lg px-3 py-2 text-sm"
            value="{{ $isOldForThis ? old('luas') : (isset($kavling) ? (int) $kavling->luas : '') }}">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Harga per m²
            <span class="text-xs text-gray-400">(otomatis · {{ $namaTahap }})</span>
        </label>
        <input type="text" id="harga_per_m2_display_{{ $suffix }}" readonly tabindex="-1"
            class="w-full border rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-600 cursor-not-allowed"
            value="{{ number_format($perM2Val, 0, ',', '.') }}">
        <input type="hidden" id="harga_per_m2_{{ $suffix }}" value="{{ $perM2Val }}">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual</label>
        @php
            $jualVal = $isOldForThis ? old('harga_jual') : (isset($kavling) ? (int) $kavling->harga_jual : '');
        @endphp
        <input type="text" id="harga_jual_display_{{ $suffix }}" autocomplete="off"
            inputmode="numeric"
            class="w-full border rounded-lg px-3 py-2 text-sm"
            value="{{ $jualVal !== '' && $jualVal !== null ? number_format((int) $jualVal, 0, ',', '.') : '' }}">
        <input type="hidden" id="harga_jual_{{ $suffix }}" name="harga_jual" value="{{ $jualVal }}">
    </div>

    <div class="col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select id="status_{{ $suffix }}" name="status" autocomplete="off" class="w-full border rounded-lg px-3 py-2 text-sm">
            @foreach(['tersedia', 'reservasi', 'booking', 'dp', 'terjual'] as $status)
                <option value="{{ $status }}"
                    {{ ($isOldForThis ? old('status') : ($kavling->status ?? '')) == $status ? 'selected' : '' }}>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<script>
(function () {
    const suffix = "{{ $suffix }}";

    const blokInput  = document.getElementById('blok_' + suffix);
    const nomorInput = document.getElementById('nomor_' + suffix);
    const previewNo   = document.getElementById('previewNo_' + suffix);
    const previewKode = document.getElementById('previewKode_' + suffix);

    const tipeSelect  = document.getElementById('tipe_' + suffix);
    const ukuranInput = document.getElementById('ukuran_' + suffix);
    const luasInput   = document.getElementById('luas_' + suffix);

    const perM2Display = document.getElementById('harga_per_m2_display_' + suffix);
    const jualDisplay  = document.getElementById('harga_jual_display_' + suffix);
    const jualHidden   = document.getElementById('harga_jual_' + suffix);
    const statusSelect = document.getElementById('status_' + suffix);

    const tipeMap = {
        'Prima':         { ukuran: '7 x 14', luas: 98 },
        'Standard':      { ukuran: '7 x 10', luas: 70 },
        'Standard Hook': { ukuran: '7 x ±8.5 x ±8.3 x ±10', luas: '' }
    };

    function formatRibuan(angka) {
        angka = angka.toString().replace(/\D/g, '');
        if (!angka) return '';
        return parseInt(angka, 10).toLocaleString('id-ID');
    }

    function unformat(str) {
        return parseInt(str.toString().replace(/\D/g, ''), 10) || 0;
    }

    function updateKodePreview() {
        const blok = blokInput.value.trim().toUpperCase();
        const nomor = nomorInput.value.trim();

        if (blok && nomor) {
            const nomorPadded = nomor.padStart(2, '0');
            previewNo.textContent = blok + nomor;
            previewKode.textContent = 'TR-' + blok + nomorPadded;
        } else {
            previewNo.textContent = '-';
            previewKode.textContent = '-';
        }
    }

    blokInput.addEventListener('input', updateKodePreview);
    nomorInput.addEventListener('input', updateKodePreview);

    // Harga/m² read-only (otomatis dari tahap), jadi Harga Jual dihitung dari luas x harga/m²
    function hitungHargaJual() {
        const luas  = parseInt(luasInput.value) || 0;
        const perM2 = unformat(perM2Display.value);

        if (luas > 0 && perM2 > 0) {
            const total = Math.round(luas * perM2);
            jualDisplay.value = formatRibuan(total);
            jualHidden.value = total;
        }
    }

    tipeSelect.addEventListener('change', function () {
        const data = tipeMap[this.value];
        if (data) {
            ukuranInput.value = data.ukuran;
            luasInput.value = data.luas;
        } else {
            ukuranInput.value = '';
            luasInput.value = '';
        }
        hitungHargaJual();
    });

    luasInput.addEventListener('input', hitungHargaJual);

    jualDisplay.addEventListener('input', function () {
        const raw = unformat(this.value);
        this.value = formatRibuan(raw);
        jualHidden.value = raw;
    });

    if (suffix === 'new') {
        window.resetFormKavling = function () {
            const formEl = blokInput.closest('form');
            if (!formEl) return;

            formEl.reset();
            previewNo.textContent = '-';
            previewKode.textContent = '-';
            tipeSelect.selectedIndex = 0;
            ukuranInput.value = '';
            luasInput.value = '';
            jualDisplay.value = '';
            jualHidden.value = '';
            statusSelect.selectedIndex = 0;
            // harga per m² dibiarkan: nilainya otomatis dari tahap aktif
        };
    }
})();
</script>