@extends('layouts.app')

@section('content')
<div class="p-6">

    {{-- Breadcrumb --}}
    <p class="text-sm text-gray-500 mb-1">Data Master &gt; <span class="text-gray-800 font-medium">Data Agen & Marketing</span></p>
    <h1 class="text-2xl font-bold text-gray-900">Data Agen & Marketing</h1>
    <p class="text-gray-500 mb-6">Kelola performa agen, prospek, closing, dan komisi penjualan.</p>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 text-green-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Total Agen
            </p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_agen'] }} <span class="text-sm font-normal text-gray-400">Agen</span></p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18"/><path d="M8 4v4"/><path d="M16 4v4"/></svg>
                Total Lead
            </p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_lead'] }} <span class="text-sm font-normal text-gray-400">Lead</span></p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M6 21v-2a6 6 0 0 1 12 0v2"/></svg>
                Total Prospek
            </p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_prospek'] }} <span class="text-sm font-normal text-gray-400">Prospek</span></p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Total Closing
            </p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_closing'] }} <span class="text-sm font-normal text-gray-400">Closing</span></p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                Nilai Penjualan
            </p>
            <p class="text-2xl font-bold text-gray-900 mt-1">Rp{{ $stats['nilai_penjualan_short'] }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4 8 4v14"/><path d="M9 9h1"/><path d="M9 13h1"/><path d="M14 9h1"/><path d="M14 13h1"/></svg>
                Total Komisi
            </p>
            <p class="text-2xl font-bold text-gray-900 mt-1">Rp{{ $stats['total_komisi_short'] }}</p>
        </div>
    </div>

    {{-- Toolbar: tambah + search + filter --}}
    <form method="GET" action="{{ route('agen.index') }}" class="flex flex-wrap items-center gap-3 mb-4">
        <button type="button" onclick="openTambahAgenModal()"
           class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Agen
        </button>

        <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama / Kode"
                   class="border rounded-lg pl-9 pr-3 py-2 text-sm text-gray-700 w-56">
        </div>

        <button type="button" onclick="toggleFilterPanel()"
                class="border rounded-lg px-3 py-2 text-sm text-gray-700 flex items-center gap-1.5 hover:bg-gray-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            Filter
        </button>

        @if(request('search') || request('sort'))
            <a href="{{ route('agen.index') }}" class="text-sm text-green-600 font-medium">Reset Filter</a>
        @endif

        <span class="ml-auto text-sm text-gray-500">Menampilkan {{ $agens->count() }} dari {{ $stats['total_agen'] }} agen</span>
    </form>

    {{-- Panel Filter (opsional, disembunyikan default) --}}
    <div id="filterPanel" class="hidden bg-white border rounded-xl p-4 mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Urutkan Berdasarkan</label>
            <select name="sort" onchange="this.form.submit()" form="filterForm"
                    class="w-full border rounded-lg px-3 py-2 text-sm text-gray-700">
                <option value="">Default</option>
                <option value="closing_desc" {{ request('sort') == 'closing_desc' ? 'selected' : '' }}>Closing Terbanyak</option>
                <option value="komisi_desc" {{ request('sort') == 'komisi_desc' ? 'selected' : '' }}>Komisi Tertinggi</option>
                <option value="sisa_desc" {{ request('sort') == 'sisa_desc' ? 'selected' : '' }}>Sisa Komisi Tertinggi</option>
            </select>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Kode Agen</th>
                    <th class="px-4 py-3 text-left">Nama Agen</th>
                    <th class="px-4 py-3 text-left">No HP</th>
                    <th class="px-4 py-3 text-left">Lead</th>
                    <th class="px-4 py-3 text-left">Prospek</th>
                    <th class="px-4 py-3 text-left">Closing</th>
                    <th class="px-4 py-3 text-left">Nilai Penjualan</th>
                    <th class="px-4 py-3 text-left">Komisi %</th>
                    <th class="px-4 py-3 text-left">Komisi Terhitung</th>
                    <th class="px-4 py-3 text-left">Dibayar</th>
                    <th class="px-4 py-3 text-left">Sisa Komisi</th>
                    <th class="px-4 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($agens as $agen)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $agen->kode_agen }}</td>
                        <td class="px-4 py-3">{{ $agen->nama_agen }}</td>
                        <td class="px-4 py-3">{{ $agen->no_hp }}</td>
                        <td class="px-4 py-3">{{ $agen->lead }}</td>
                        <td class="px-4 py-3">{{ $agen->prospek }}</td>
                        <td class="px-4 py-3">{{ $agen->closing }}</td>
                        <td class="px-4 py-3">Rp{{ number_format($agen->nilai_penjualan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ rtrim(rtrim(number_format($agen->komisi_persen, 2, ',', '.'), '0'), ',') }}%</td>
                        <td class="px-4 py-3">Rp{{ number_format($agen->komisi_terhitung, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-green-600 font-medium">Rp{{ number_format($agen->dibayar, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 font-medium {{ $agen->sisa_komisi > 0 ? 'text-red-600' : 'text-gray-700' }}">
                            Rp{{ number_format($agen->sisa_komisi, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('agen.show', $agen) }}" title="Detail" class="text-gray-500 hover:text-gray-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="{{ route('agen.edit', $agen) }}" title="Edit" class="text-blue-500 hover:text-blue-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <form action="{{ route('agen.destroy', $agen) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus agen ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus" class="text-red-500 hover:text-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="px-4 py-8 text-center text-gray-400">Belum ada data agen.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Tambah Agen --}}
    <div id="tambahAgenModal" class="fixed inset-0 z-50 hidden items-center justify-center">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50" onclick="closeTambahAgenModal()"></div>

        {{-- Modal Box --}}
        <div class="relative bg-white rounded-xl shadow-lg w-full max-w-xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between px-6 py-5 border-b">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Tambah Agen Baru</h2>
                    <p class="text-sm text-gray-500 mt-1">Lengkapi informasi agen untuk menambahkan data agen dan marketing baru.</p>
                </div>
                <button type="button" onclick="closeTambahAgenModal()"
                        class="text-gray-400 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <form action="{{ route('agen.store') }}" method="POST" id="formTambahAgen" class="p-6 space-y-5">
                @csrf
                @include('agen._form')

                <div class="flex justify-end gap-3 pt-4 border-t mt-2">
                    <button type="button" onclick="closeTambahAgenModal()"
                            class="px-5 py-2 rounded-lg text-sm font-medium border text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">
                        Simpan Agen
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function openTambahAgenModal() {
        const modal = document.getElementById('tambahAgenModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeTambahAgenModal() {
        const modal = document.getElementById('tambahAgenModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function toggleFilterPanel() {
        document.getElementById('filterPanel').classList.toggle('hidden');
    }

    // Auto-hitung Komisi Terhitung & Sisa Komisi di dalam modal
    function hitungKomisi() {
        const nilaiInput  = document.getElementById('nilai_penjualan');
        const persenInput = document.getElementById('komisi_persen');
        const dibayarInput = document.getElementById('dibayar');
        const terhitungInput = document.getElementById('komisi_terhitung');
        const sisaInput = document.getElementById('sisa_komisi');

        const nilai   = parseFloat(nilaiInput.value) || 0;
        const persen  = parseFloat(persenInput.value) || 0;
        const dibayar = parseFloat(dibayarInput.value) || 0;

        const terhitung = Math.round(nilai * (persen / 100));
        const sisa = terhitung - dibayar;

        terhitungInput.value = terhitung;
        sisaInput.value = sisa;
    }

    document.addEventListener('DOMContentLoaded', function () {
        ['nilai_penjualan', 'komisi_persen', 'dibayar'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', hitungKomisi);
        });

        @if($errors->any())
            openTambahAgenModal();
        @endif
    });
</script>
@endsection