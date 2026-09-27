@extends('layouts.app')

@section('content')
<div class="p-6">

    {{-- Breadcrumb --}}
    <p class="text-sm text-gray-500 mb-1">Data Master &gt; <span class="text-gray-800 font-medium">Skema Harga</span></p>
    <h1 class="text-2xl font-bold text-gray-900">Skema Harga</h1>
    <p class="text-gray-500 mb-6">Kelola tahapan harga otomatis berdasarkan jumlah unit kavling yang telah terjual.</p>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 text-green-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Tahap</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['total_tahap'] }} <span class="text-sm font-normal text-gray-400">Tahap</span></p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Harga Awal</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">Rp{{ number_format($stats['harga_awal'], 0, ',', '.') }}<span class="text-sm font-normal text-gray-400">/m&sup2;</span></p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Harga Tertinggi</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">Rp{{ number_format($stats['harga_tertinggi'], 0, ',', '.') }}<span class="text-sm font-normal text-gray-400">/m&sup2;</span></p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Kavling</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['total_kavling'] }} <span class="text-sm font-normal text-gray-400">Kavling</span></p>
        </div>
        <div class="bg-gray-900 rounded-xl p-4">
            <p class="text-xs text-gray-300 uppercase font-medium flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Harga Aktif
            </p>
            <p class="text-2xl font-bold text-white mt-2">Rp{{ number_format($stats['harga_aktif'], 0, ',', '.') }}<span class="text-sm font-normal text-gray-400">/m&sup2;</span></p>
        </div>
    </div>

    {{-- Card Tabel --}}
    <div class="bg-white rounded-xl border">
        <div class="flex items-center justify-between px-6 py-5">
            <h2 class="text-lg font-bold text-gray-900">Skema Harga Otomatis</h2>
            <button type="button" onclick="openTambahTahapModal()"
                class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Tahap
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-indigo-50/60 text-gray-600 text-xs uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left">Tahap</th>
                        <th class="px-6 py-3 text-left">Unit Mulai</th>
                        <th class="px-6 py-3 text-left">Unit Sampai</th>
                        <th class="px-6 py-3 text-left">Harga/m&sup2;</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($tahaps as $tahap)
                        @php
                            $badge = match($tahap->status_label) {
                                'Aktif'    => 'bg-green-100 text-green-700',
                                'Selesai'  => 'bg-gray-200 text-gray-600',
                                default    => 'bg-gray-100 text-gray-500',
                            };
                            $rowBg = $tahap->status_label === 'Aktif' ? 'bg-indigo-50/40' : '';
                        @endphp
                        <tr class="{{ $rowBg }} hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $tahap->nama_tahap }}</td>
                            <td class="px-6 py-4">{{ $tahap->unit_mulai }}</td>
                            <td class="px-6 py-4">{{ $tahap->unit_sampai }}</td>
                            <td class="px-6 py-4 font-medium">Rp {{ number_format($tahap->harga_per_m2, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $badge }}">{{ $tahap->status_label }}</span>
                            </td>
                            <td class="px-6 py-4 relative">
                                <button type="button" onclick="toggleAksiMenu({{ $tahap->id }})" class="text-gray-400 hover:text-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                </button>

                                <div id="aksiMenu{{ $tahap->id }}" class="hidden absolute right-6 top-10 z-10 bg-white border rounded-lg shadow-lg w-36 py-1">
                                    <button type="button" onclick="openEditTahapModal({{ $tahap->id }}, '{{ $tahap->nama_tahap }}', {{ $tahap->unit_mulai }}, {{ $tahap->unit_sampai }}, {{ $tahap->harga_per_m2 }})"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </button>
                                    <form action="{{ route('skema-harga.destroy', $tahap) }}" method="POST"
                                          onsubmit="return confirm('Yakin hapus tahap ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada tahap harga.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Tahap --}}
    <div id="tambahTahapModal" class="fixed inset-0 z-50 hidden items-center justify-center">
        <div class="absolute inset-0 bg-black/50" onclick="closeTambahTahapModal()"></div>

        <div class="relative bg-white rounded-xl shadow-lg w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between px-6 py-5 border-b">
                <div>
                    <h2 id="modalTitle" class="text-lg font-bold text-gray-900">Tambah Tahap Harga</h2>
                    <p id="modalSubtitle" class="text-sm text-gray-500 mt-1">Tambahkan tahapan harga berdasarkan jumlah unit kavling yang telah terjual.</p>
                </div>
                <button type="button" onclick="closeTambahTahapModal()" class="text-gray-400 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <form id="formTahap" action="{{ route('skema-harga.store') }}" method="POST" class="p-6 space-y-5">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                @if($errors->any())
                    <div class="rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <p class="text-xs font-semibold text-gray-500 uppercase border-b pb-2">Informasi Tahap</p>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Tahap</label>
                    <input type="text" id="nama_tahap" name="nama_tahap"
                        class="w-full border rounded-lg px-3 py-2 text-sm"
                        value="{{ old('nama_tahap', 'Tahap ' . $nextTahapNumber) }}">
                    <p class="text-xs text-gray-400 mt-1">Nama atau urutan tahapan harga.</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Terjual Mulai</label>
                        <div class="relative">
                            <input type="number" id="unit_mulai" name="unit_mulai"
                                class="w-full border rounded-lg pl-3 pr-12 py-2 text-sm"
                                value="{{ old('unit_mulai', $nextUnitMulai) }}">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Unit</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Harga tahap ini mulai berlaku setelah jumlah unit tertentu terjual.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Terjual Sampai</label>
                        <div class="relative">
                            <input type="number" id="unit_sampai" name="unit_sampai"
                                class="w-full border rounded-lg pl-3 pr-12 py-2 text-sm"
                                value="{{ old('unit_sampai', $nextUnitMulai + 2) }}">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Unit</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Masukkan batas akhir jumlah unit untuk tahap ini.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga / m&sup2;</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                        <input type="number" step="0.01" id="harga_per_m2" name="harga_per_m2"
                            class="w-full border rounded-lg pl-9 pr-3 py-2 text-sm font-medium"
                            value="{{ old('harga_per_m2', 0) }}">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Harga kavling per meter persegi pada tahap ini.</p>
                </div>

                <div class="bg-blue-50 rounded-lg p-4 flex gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <div class="text-sm text-blue-900">
                        <p class="font-semibold">Aturan Tahap Harga:</p>
                        <p class="text-blue-800 mt-0.5">Rentang unit pada tahap baru tidak boleh bertabrakan dengan tahap harga lainnya.</p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <div class="text-sm">
                        <p class="text-xs font-semibold text-gray-500 uppercase">Preview Tahap</p>
                        <p id="previewText" class="font-medium text-gray-800 mt-1">
                            Tahap {{ $nextTahapNumber }} &bull; Unit {{ $nextUnitMulai }}&ndash;{{ $nextUnitMulai + 2 }} &bull; Rp0/m&sup2;
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t mt-2">
                    <button type="button" onclick="closeTambahTahapModal()"
                        class="px-5 py-2 rounded-lg text-sm font-medium border text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" id="submitButton" class="bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">
                        Simpan Tahap
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function openTambahTahapModal() {
        document.getElementById('modalTitle').textContent = 'Tambah Tahap Harga';
        document.getElementById('modalSubtitle').textContent = 'Tambahkan tahapan harga berdasarkan jumlah unit kavling yang telah terjual.';
        document.getElementById('formTahap').action = "{{ route('skema-harga.store') }}";
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('submitButton').textContent = 'Simpan Tahap';

        const modal = document.getElementById('tambahTahapModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function openEditTahapModal(id, nama, unitMulai, unitSampai, harga) {
        document.getElementById('modalTitle').textContent = 'Edit Tahap Harga';
        document.getElementById('modalSubtitle').textContent = 'Perbarui informasi tahapan harga ini.';
        document.getElementById('formTahap').action = "{{ url('/skema-harga') }}/" + id;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('submitButton').textContent = 'Simpan Perubahan';

        document.getElementById('nama_tahap').value = nama;
        document.getElementById('unit_mulai').value = unitMulai;
        document.getElementById('unit_sampai').value = unitSampai;
        document.getElementById('harga_per_m2').value = harga;
        updatePreview();

        document.querySelectorAll('[id^="aksiMenu"]').forEach(el => el.classList.add('hidden'));

        const modal = document.getElementById('tambahTahapModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeTambahTahapModal() {
        const modal = document.getElementById('tambahTahapModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function toggleAksiMenu(id) {
        document.querySelectorAll('[id^="aksiMenu"]').forEach(el => {
            if (el.id !== 'aksiMenu' + id) el.classList.add('hidden');
        });
        document.getElementById('aksiMenu' + id).classList.toggle('hidden');
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('[onclick^="toggleAksiMenu"]') && !e.target.closest('[id^="aksiMenu"]')) {
            document.querySelectorAll('[id^="aksiMenu"]').forEach(el => el.classList.add('hidden'));
        }
    });

    function updatePreview() {
        const nama    = document.getElementById('nama_tahap').value || '-';
        const mulai   = document.getElementById('unit_mulai').value || 0;
        const sampai  = document.getElementById('unit_sampai').value || 0;
        const harga   = parseFloat(document.getElementById('harga_per_m2').value) || 0;

        const hargaFormatted = new Intl.NumberFormat('id-ID').format(harga);

        document.getElementById('previewText').innerHTML =
            `${nama} &bull; Unit ${mulai}&ndash;${sampai} &bull; Rp${hargaFormatted}/m&sup2;`;
    }

    document.addEventListener('DOMContentLoaded', function () {
        ['nama_tahap', 'unit_mulai', 'unit_sampai', 'harga_per_m2'].forEach(function (id) {
            document.getElementById(id).addEventListener('input', updatePreview);
        });

        @if($errors->any())
            openTambahTahapModal();
        @endif
    });
</script>
@endsection