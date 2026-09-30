@extends('layouts.app')

@section('content')
<div class="p-6">

    <p class="text-sm text-gray-500 mb-1">Data Master &gt; <span class="text-gray-800 font-medium">Master Kavling</span></p>
    <h1 class="text-2xl font-bold text-gray-900">Master Kavling</h1>
    <p class="text-gray-500 mb-6">Kelola data kavling, harga, ukuran, dan status ketersediaan kavling proyek.</p>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-gray-50 border border-gray-200 text-gray-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Kavling</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-400">Kavling</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Tersedia</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['tersedia'] }}</p>
            <p class="text-xs text-gray-400">Kavling</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Reservasi</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['reservasi'] }}</p>
            <p class="text-xs text-gray-400">Kavling</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Booking</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['booking'] }}</p>
            <p class="text-xs text-gray-400">Kavling</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">DP</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['dp'] }}</p>
            <p class="text-xs text-gray-400">Kavling</p>
        </div>
        <div class="bg-gray-900 rounded-xl border border-gray-900 p-4">
            <p class="text-xs text-gray-300 uppercase tracking-wide">Terjual</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['terjual'] }}</p>
            <p class="text-xs text-gray-400">Kavling</p>
        </div>
    </div>

    {{-- Info Tahap Berlaku --}}
    <p class="text-sm text-gray-500 mb-3">
        Tahap berlaku: <strong class="text-gray-800">{{ $tahapAktif->nama_tahap ?? '-' }}</strong>
        — Rp{{ number_format($hargaAktif, 0, ',', '.') }}/m²
    </p>

    {{-- Toolbar --}}
    <form method="GET" action="{{ route('kavling.index') }}" class="flex flex-wrap items-center gap-3 mb-4">
        <button type="button" onclick="openTambahKavlingModal()"
           class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">
            + Tambah Kavling
        </button>

        <select name="status" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700">
            <option value="">Filter Status</option>
            <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
            <option value="reservasi" {{ request('status') == 'reservasi' ? 'selected' : '' }}>Reservasi</option>
            <option value="booking" {{ request('status') == 'booking' ? 'selected' : '' }}>Booking</option>
            <option value="dp" {{ request('status') == 'dp' ? 'selected' : '' }}>DP</option>
            <option value="terjual" {{ request('status') == 'terjual' ? 'selected' : '' }}>Terjual</option>
        </select>

        <select name="blok" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700">
            <option value="">Filter Blok</option>
            @foreach($bloks as $blok)
                <option value="{{ $blok }}" {{ request('blok') == $blok ? 'selected' : '' }}>{{ $blok }}</option>
            @endforeach
        </select>

        @if(request('status') || request('blok'))
            <a href="{{ route('kavling.index') }}" class="text-sm text-gray-500 hover:text-gray-800 font-medium">Reset Filter</a>
        @endif

        <span class="ml-auto text-sm text-gray-500">Menampilkan {{ $kavlings->count() }} kavling</span>
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-medium">Kode Kavling</th>
                    <th class="px-4 py-3 text-left font-medium">Blok</th>
                    <th class="px-4 py-3 text-left font-medium">No</th>
                    <th class="px-4 py-3 text-left font-medium">Tipe</th>
                    <th class="px-4 py-3 text-left font-medium">Ukuran</th>
                    <th class="px-4 py-3 text-left font-medium">Luas m²</th>
                    <th class="px-4 py-3 text-left font-medium">Tahap</th>
                    <th class="px-4 py-3 text-left font-medium">Harga/m²</th>
                    <th class="px-4 py-3 text-left font-medium">Harga Jual</th>
                    <th class="px-4 py-3 text-left font-medium">Status</th>
                    <th class="px-4 py-3 text-left font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($kavlings as $kavling)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $kavling->kode_kavling }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $kavling->blok }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $kavling->no }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $kavling->tipe }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $kavling->ukuran }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ number_format($kavling->luas, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $kavling->tahap->nama_tahap ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">Rp{{ number_format($kavling->harga_per_m2, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-gray-900 font-medium">Rp{{ number_format($kavling->harga_jual, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            @php
                                $badge = [
                                    'tersedia'  => 'bg-[#E8F5E9] text-[#2E7D32]',
                                    'reservasi' => 'bg-[#E8F0FE] text-[#356AE6]',
                                    'booking'   => 'bg-[#FFF4E5] text-[#B76E00]',
                                    'dp'        => 'bg-[#FFF8D9] text-[#9A7B00]',
                                    'terjual'   => 'bg-[#FDECEC] text-[#C94A4A]',
                                ][$kavling->status] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">
                                {{ ucfirst($kavling->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <button type="button" onclick="openEditKavlingModal({{ $kavling->id }})" title="Edit"
                                        class="text-gray-400 hover:text-gray-900 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                                <form action="{{ route('kavling.destroy', $kavling) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus kavling ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus"
                                            class="text-gray-400 hover:text-red-600 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                            <path d="M10 11v6"></path>
                                            <path d="M14 11v6"></path>
                                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-gray-400">Belum ada data kavling.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Tambah Kavling --}}
    <div id="tambahKavlingModal" class="fixed inset-0 z-50 hidden items-center justify-center">
        <div class="absolute inset-0 bg-black/50" onclick="closeTambahKavlingModal()"></div>
        <div class="relative bg-white rounded-xl shadow-lg w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">Tambah Kavling</h2>
                <button type="button" onclick="closeTambahKavlingModal()"
                        class="text-gray-400 hover:text-gray-700 text-xl leading-none">&times;</button>
            </div>
            <form action="{{ route('kavling.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                @include('kavling._form')
                <div class="flex gap-3 pt-4 border-t border-gray-200 mt-4">
                    <button type="submit" class="bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">
                        Simpan
                    </button>
                    <button type="button" onclick="closeTambahKavlingModal()"
                            class="px-5 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Kavling (satu per baris) --}}
    @foreach($kavlings as $kavling)
        <div id="editKavlingModal{{ $kavling->id }}" class="fixed inset-0 z-50 hidden items-center justify-center">
            <div class="absolute inset-0 bg-black/50" onclick="closeEditKavlingModal({{ $kavling->id }})"></div>
            <div class="relative bg-white rounded-xl shadow-lg w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-bold text-gray-900">Edit Kavling — {{ $kavling->kode_kavling }}</h2>
                    <button type="button" onclick="closeEditKavlingModal({{ $kavling->id }})"
                            class="text-gray-400 hover:text-gray-700 text-xl leading-none">&times;</button>
                </div>
                <form action="{{ route('kavling.update', $kavling) }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    @include('kavling._form', ['kavling' => $kavling])
                    <div class="flex gap-3 pt-4 border-t border-gray-200 mt-4">
                        <button type="submit" class="bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">
                            Update
                        </button>
                        <button type="button" onclick="closeEditKavlingModal({{ $kavling->id }})"
                                class="px-5 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

</div>

<script>
    function openTambahKavlingModal() {
        const modal = document.getElementById('tambahKavlingModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        if (typeof resetFormKavling === 'function') resetFormKavling();
    }

    function closeTambahKavlingModal() {
        const modal = document.getElementById('tambahKavlingModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openEditKavlingModal(id) {
        const modal = document.getElementById('editKavlingModal' + id);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditKavlingModal(id) {
        const modal = document.getElementById('editKavlingModal' + id);
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    {{-- Buka modal yang BENER aja, dicocokkan lewat old('edit_id') --}}
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            const editId = "{{ old('edit_id') }}";
            if (editId && editId !== 'new') {
                openEditKavlingModal(editId);
            } else {
                openTambahKavlingModal();
            }
        });
    @endif
</script>
@endsection