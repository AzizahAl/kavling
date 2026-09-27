@extends('layouts.app')

@section('content')
<div class="p-6">

    {{-- Breadcrumb --}}
    <p class="text-sm text-gray-500 mb-1">Data Master &gt; <span class="text-gray-800 font-medium">Master Kavling</span></p>
    <h1 class="text-2xl font-bold text-gray-900">Master Kavling</h1>
    <p class="text-gray-500 mb-6">Kelola data kavling, harga, ukuran, dan status ketersediaan kavling proyek.</p>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 text-green-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase">Total Kavling</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-400">Kavling</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase">Tersedia</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['tersedia'] }}</p>
            <p class="text-xs text-gray-400">Kavling</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase">Reservasi</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['reservasi'] }}</p>
            <p class="text-xs text-gray-400">Kavling</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase">Booking</p>
            <p class="text-2xl font-bold text-orange-600">{{ $stats['booking'] }}</p>
            <p class="text-xs text-gray-400">Kavling</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase">DP</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['dp'] }}</p>
            <p class="text-xs text-gray-400">Kavling</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase">Terjual</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['terjual'] }}</p>
            <p class="text-xs text-gray-400">Kavling</p>
        </div>
    </div>

    {{-- Toolbar: tambah + filter --}}
    <form method="GET" action="{{ route('kavling.index') }}" class="flex flex-wrap items-center gap-3 mb-4">
        <button type="button" onclick="openTambahKavlingModal()"
           class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">
            + Tambah Kavling
        </button>

        <select name="status" onchange="this.form.submit()"
                class="border rounded-lg px-3 py-2 text-sm text-gray-700">
            <option value="">Filter Status</option>
            <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
            <option value="reservasi" {{ request('status') == 'reservasi' ? 'selected' : '' }}>Reservasi</option>
            <option value="booking" {{ request('status') == 'booking' ? 'selected' : '' }}>Booking</option>
            <option value="dp" {{ request('status') == 'dp' ? 'selected' : '' }}>DP</option>
            <option value="terjual" {{ request('status') == 'terjual' ? 'selected' : '' }}>Terjual</option>
        </select>

        <select name="blok" onchange="this.form.submit()"
                class="border rounded-lg px-3 py-2 text-sm text-gray-700">
            <option value="">Filter Blok</option>
            @foreach($bloks as $blok)
                <option value="{{ $blok }}" {{ request('blok') == $blok ? 'selected' : '' }}>{{ $blok }}</option>
            @endforeach
        </select>

        @if(request('status') || request('blok'))
            <a href="{{ route('kavling.index') }}" class="text-sm text-green-600 font-medium">Reset Filter</a>
        @endif

        <span class="ml-auto text-sm text-gray-500">Menampilkan {{ $kavlings->count() }} kavling</span>
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Kode Kavling</th>
                    <th class="px-4 py-3 text-left">Blok</th>
                    <th class="px-4 py-3 text-left">No</th>
                    <th class="px-4 py-3 text-left">Tipe</th>
                    <th class="px-4 py-3 text-left">Ukuran</th>
                    <th class="px-4 py-3 text-left">Luas m²</th>
                    <th class="px-4 py-3 text-left">Harga/m²</th>
                    <th class="px-4 py-3 text-left">Harga Jual</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($kavlings as $kavling)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $kavling->kode_kavling }}</td>
                        <td class="px-4 py-3">{{ $kavling->blok }}</td>
                        <td class="px-4 py-3">{{ $kavling->no }}</td>
                        <td class="px-4 py-3">{{ $kavling->tipe }}</td>
                        <td class="px-4 py-3">{{ $kavling->ukuran }}</td>
                        <td class="px-4 py-3">{{ $kavling->luas }}</td>
                        <td class="px-4 py-3">Rp{{ number_format($kavling->harga_per_m2, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">Rp{{ number_format($kavling->harga_jual, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            @php
                                $badge = [
                                    'tersedia'  => 'bg-green-100 text-green-700',
                                    'reservasi' => 'bg-yellow-100 text-yellow-700',
                                    'booking'   => 'bg-orange-100 text-orange-700',
                                    'dp'        => 'bg-blue-100 text-blue-700',
                                    'terjual'   => 'bg-red-100 text-red-700',
                                ][$kavling->status] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badge }}">
                                {{ ucfirst($kavling->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('kavling.show', $kavling) }}" title="Detail" class="text-gray-500 hover:text-gray-800">👁</a>
                                <a href="{{ route('kavling.edit', $kavling) }}" title="Edit" class="text-blue-500 hover:text-blue-700">✏️</a>
                                <form action="{{ route('kavling.destroy', $kavling) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus kavling ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus" class="text-red-500 hover:text-red-700">🗑</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-400">Belum ada data kavling.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Tambah Kavling --}}
    <div id="tambahKavlingModal" class="fixed inset-0 z-50 hidden items-center justify-center">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50" onclick="closeTambahKavlingModal()"></div>

        {{-- Modal Box --}}
        <div class="relative bg-white rounded-xl shadow-lg w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h2 class="text-lg font-bold text-gray-900">Tambah Kavling</h2>
                <button type="button" onclick="closeTambahKavlingModal()"
                        class="text-gray-400 hover:text-gray-700 text-xl leading-none">&times;</button>
            </div>

            <form action="{{ route('kavling.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                @include('kavling._form')

                <div class="flex gap-3 pt-4 border-t mt-4">
                    <button type="submit" class="bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">
                        Simpan
                    </button>
                    <button type="button" onclick="closeTambahKavlingModal()"
                            class="px-5 py-2 rounded-lg text-sm font-medium border text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function openTambahKavlingModal() {
        const modal = document.getElementById('tambahKavlingModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeTambahKavlingModal() {
        const modal = document.getElementById('tambahKavlingModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Kalau ada error validasi dari server (redirect back), modal otomatis kebuka lagi
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            openTambahKavlingModal();
        });
    @endif
</script>
@endsection