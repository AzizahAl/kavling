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
            @if(isset($agen))
                {{-- Mode edit: kode agen udah fix, gak bisa diubah --}}
                <input type="text" value="{{ $agen->kode_agen }}" disabled
                    class="w-full border rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500">
            @else
                {{-- Mode tambah: cuma preview, otomatis dari server, di-refresh via JS tiap modal dibuka --}}
                <div class="w-full border rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500">
                    <span id="kodeAgenPreview">{{ $nextKodeAgen ?? 'AG-XXX' }}</span>
                    <span class="text-xs text-gray-400">(otomatis)</span>
                </div>
            @endif
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
            <input type="text" id="no_hp_{{ $suffix ?? 'new' }}" name="no_hp" autocomplete="off"
                class="w-full border rounded-lg px-3 py-2 text-sm"
                value="{{ old('no_hp', $agen->no_hp ?? '') }}">
        </div>
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Agen</label>
            <input type="text" id="nama_agen_{{ $suffix ?? 'new' }}" name="nama_agen" autocomplete="off"
                class="w-full border rounded-lg px-3 py-2 text-sm"
                value="{{ old('nama_agen', $agen->nama_agen ?? '') }}">
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
            <input type="number" id="lead_{{ $suffix ?? 'new' }}" name="lead" autocomplete="off"
                class="w-full border rounded-lg px-3 py-2 text-sm"
                value="{{ old('lead', $agen->lead ?? 0) }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Prospek</label>
            <input type="number" id="prospek_{{ $suffix ?? 'new' }}" name="prospek" autocomplete="off"
                class="w-full border rounded-lg px-3 py-2 text-sm"
                value="{{ old('prospek', $agen->prospek ?? 0) }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Closing</label>
            <input type="number" id="closing_{{ $suffix ?? 'new' }}" name="closing" autocomplete="off"
                class="w-full border rounded-lg px-3 py-2 text-sm"
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
                <input type="number" step="0.01" id="nilai_penjualan_{{ $suffix ?? 'new' }}" name="nilai_penjualan" autocomplete="off"
                    class="w-full border rounded-lg pl-8 pr-3 py-2 text-sm"
                    value="{{ old('nilai_penjualan', $agen->nilai_penjualan ?? 0) }}">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Komisi (%)</label>
            <div class="relative">
                <input type="number" step="0.01" id="komisi_persen_{{ $suffix ?? 'new' }}" name="komisi_persen" autocomplete="off"
                    class="w-full border rounded-lg pl-3 pr-8 py-2 text-sm"
                    value="{{ old('komisi_persen', $agen->komisi_persen ?? 0) }}">
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">%</span>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Komisi Terhitung</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                <input type="number" id="komisi_terhitung_{{ $suffix ?? 'new' }}" name="komisi_terhitung" readonly autocomplete="off"
                    class="w-full border rounded-lg pl-8 pr-3 py-2 text-sm bg-blue-50 text-gray-500"
                    value="{{ old('komisi_terhitung', $agen->komisi_terhitung ?? 0) }}">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dibayar</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                <input type="number" step="0.01" id="dibayar_{{ $suffix ?? 'new' }}" name="dibayar" autocomplete="off"
                    class="w-full border rounded-lg pl-8 pr-3 py-2 text-sm"
                    value="{{ old('dibayar', $agen->dibayar ?? 0) }}">
            </div>
        </div>
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Sisa Komisi</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                <input type="number" id="sisa_komisi_{{ $suffix ?? 'new' }}" name="sisa_komisi" readonly autocomplete="off"
                    class="w-full border rounded-lg pl-8 pr-3 py-2 text-sm bg-blue-50 text-gray-500"
                    value="{{ old('sisa_komisi', $agen->sisa_komisi ?? 0) }}">
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const suffix = "{{ $suffix ?? 'new' }}";

    function hitungKomisi() {
        const nilaiInput     = document.getElementById('nilai_penjualan_' + suffix);
        const persenInput    = document.getElementById('komisi_persen_' + suffix);
        const dibayarInput   = document.getElementById('dibayar_' + suffix);
        const terhitungInput = document.getElementById('komisi_terhitung_' + suffix);
        const sisaInput      = document.getElementById('sisa_komisi_' + suffix);

        const nilai   = parseFloat(nilaiInput.value) || 0;
        const persen  = parseFloat(persenInput.value) || 0;
        const dibayar = parseFloat(dibayarInput.value) || 0;

        const terhitung = Math.round(nilai * (persen / 100));
        const sisa = terhitung - dibayar;

        terhitungInput.value = terhitung;
        sisaInput.value = sisa;
    }

    ['nilai_penjualan_' + suffix, 'komisi_persen_' + suffix, 'dibayar_' + suffix].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', hitungKomisi);
    });

    // Reset cuma buat form Tambah — dipaksa manual per field, JANGAN andelin formEl.reset() doang,
    // soalnya reset() cuma balikin ke value awal render dan gampang ketiban autofill Chrome lagi.
    if (suffix === 'new') {
        window.resetFormAgen = function () {
            const ids = [
                'no_hp_new',
                'nama_agen_new',
                'lead_new',
                'prospek_new',
                'closing_new',
                'nilai_penjualan_new',
                'komisi_persen_new',
                'komisi_terhitung_new',
                'dibayar_new',
                'sisa_komisi_new',
            ];

            ids.forEach(function (id) {
                const el = document.getElementById(id);
                if (!el) return;

                if (el.type === 'number') {
                    el.value = 0;
                } else {
                    el.value = '';
                }
            });
        };
    }
})();
</script>