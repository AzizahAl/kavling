@extends('layouts.app')

@section('content')
<div class="p-6">

    {{-- Breadcrumb --}}
    <div class="flex items-start justify-between mb-1">
        <div>
            <p class="text-sm text-gray-500 mb-1">Penjualan &gt; <span class="text-gray-800 font-medium">Data Konsumen</span></p>
            <h1 class="text-2xl font-bold text-gray-900">Data Konsumen</h1>
        </div>
        <span class="border rounded-lg px-3 py-2 text-sm text-gray-700 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
            Project: Tectona Residen
        </span>
    </div>
    <p class="text-gray-500 mb-6">Kelola dan pantau data konsumen, status transaksi, serta progres dokumen penjualan kavling.</p>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 text-green-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-4">
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Konsumen</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['total_konsumen'] }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Reservasi</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['reservasi'] }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Booking</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['booking'] }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">DP</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['dp'] }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Cash / Lunas</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['cash_lunas'] }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Angsuran</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['angsuran'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Nilai Penjualan</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">Rp{{ number_format($stats['total_nilai_penjualan'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Pembayaran Masuk</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">Rp{{ number_format($stats['total_pembayaran_masuk'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <form method="GET" action="{{ route('konsumen.index') }}" class="flex flex-wrap items-center gap-3 mb-4">
        <div class="relative flex-1 min-w-[220px] max-w-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, ID, kavling..."
                   class="w-full border rounded-lg pl-9 pr-3 py-2 text-sm text-gray-700">
        </div>

        <select name="status" onchange="this.form.submit()" class="border rounded-lg px-3 py-2 text-sm text-gray-700">
            <option value="">Semua Status</option>
            <option value="reservasi" {{ request('status') == 'reservasi' ? 'selected' : '' }}>Reservasi</option>
            <option value="booking" {{ request('status') == 'booking' ? 'selected' : '' }}>Booking</option>
            <option value="dp" {{ request('status') == 'dp' ? 'selected' : '' }}>DP</option>
            <option value="cash_lunas" {{ request('status') == 'cash_lunas' ? 'selected' : '' }}>Cash / Lunas</option>
            <option value="angsuran" {{ request('status') == 'angsuran' ? 'selected' : '' }}>Angsuran</option>
        </select>

        <select name="agen" onchange="this.form.submit()" class="border rounded-lg px-3 py-2 text-sm text-gray-700">
            <option value="">Semua Agen</option>
            @foreach($agens as $agenOption)
                <option value="{{ $agenOption->id }}" {{ request('agen') == $agenOption->id ? 'selected' : '' }}>
                    {{ $agenOption->nama_agen }}
                </option>
            @endforeach
        </select>

        <select name="dokumen" onchange="this.form.submit()" class="border rounded-lg px-3 py-2 text-sm text-gray-700">
            <option value="">Filter Dokumen</option>
            <option value="status_ppjb:proses" {{ request('dokumen') == 'status_ppjb:proses' ? 'selected' : '' }}>PPJB Proses</option>
            <option value="status_ppjb:selesai" {{ request('dokumen') == 'status_ppjb:selesai' ? 'selected' : '' }}>PPJB Selesai</option>
            <option value="status_ajb:proses" {{ request('dokumen') == 'status_ajb:proses' ? 'selected' : '' }}>AJB Proses</option>
            <option value="status_ajb:selesai" {{ request('dokumen') == 'status_ajb:selesai' ? 'selected' : '' }}>AJB Selesai</option>
        </select>

        @if(request('search') || request('status') || request('agen') || request('dokumen'))
            <a href="{{ route('konsumen.index') }}"
               class="border rounded-lg px-3 py-2 text-sm text-gray-700 flex items-center gap-1.5 hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                Reset Filter
            </a>
        @endif

        <button type="button" onclick="openTambahKonsumenModal()"
            class="ml-auto bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Konsumen
        </button>
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">ID Konsumen / Nama</th>
                    <th class="px-4 py-3 text-left">Kavling</th>
                    <th class="px-4 py-3 text-left">Tanggal Masuk</th>
                    <th class="px-4 py-3 text-left">Status / Agen</th>
                    <th class="px-4 py-3 text-left">Reservasi</th>
                    <th class="px-4 py-3 text-left">Booking/SPK</th>
                    <th class="px-4 py-3 text-left">PPJB</th>
                    <th class="px-4 py-3 text-left">AJB/Legal</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($konsumens as $konsumen)
                    @php
                        $statusBadge = [
                            'reservasi'  => 'bg-gray-100 text-gray-600',
                            'booking'    => 'bg-yellow-100 text-yellow-700',
                            'dp'         => 'bg-blue-100 text-blue-700',
                            'cash_lunas' => 'bg-green-100 text-green-700',
                            'angsuran'   => 'bg-purple-100 text-purple-700',
                        ][$konsumen->status_transaksi] ?? 'bg-gray-100 text-gray-600';

                        $statusLabel = [
                            'reservasi'  => 'RESERVASI',
                            'booking'    => 'BOOKING',
                            'dp'         => 'DP',
                            'cash_lunas' => 'CASH/LUNAS',
                            'angsuran'   => 'ANGSURAN',
                        ][$konsumen->status_transaksi] ?? strtoupper($konsumen->status_transaksi);

                        $dokBadge = fn($v) => match($v) {
                            'selesai' => 'bg-green-100 text-green-700',
                            'proses'  => 'bg-yellow-100 text-yellow-700',
                            default   => 'bg-gray-100 text-gray-500',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 align-top">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $konsumen->id_konsumen }}</p>
                            <p class="text-gray-500">{{ $konsumen->nama_lengkap }}</p>
                        </td>
                        <td class="px-4 py-3 font-medium">{{ $konsumen->kavling->kode_kavling ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $konsumen->tanggal_transaksi->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusBadge }}">{{ $statusLabel }}</span>
                            <p class="text-gray-500 mt-1">{{ $konsumen->agen->nama_agen ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $dokBadge($konsumen->status_reservasi) }}">
                                {{ strtoupper($konsumen->status_reservasi) }}
                            </span>
                            <p class="text-gray-500 mt-1">Rp{{ number_format($konsumen->nominal_reservasi, 0, ',', '.') }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $dokBadge($konsumen->status_booking) }}">
                                {{ strtoupper($konsumen->status_booking) }}
                            </span>
                            <p class="text-gray-500 mt-1">Rp{{ number_format($konsumen->nominal_booking, 0, ',', '.') }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $dokBadge($konsumen->status_ppjb) }}">
                                {{ strtoupper($konsumen->status_ppjb) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $dokBadge($konsumen->status_ajb) }}">
                                {{ strtoupper($konsumen->status_ajb) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('konsumen.show', $konsumen) }}"
                               class="border rounded-lg px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 inline-block">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-400">Belum ada data konsumen.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Tambah Konsumen --}}
    <div id="tambahKonsumenModal" class="fixed inset-0 z-50 hidden items-center justify-center">
        <div class="absolute inset-0 bg-black/50" onclick="closeTambahKonsumenModal()"></div>

        <div class="relative bg-white rounded-xl shadow-lg w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between px-6 py-5 border-b sticky top-0 bg-white">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Tambah Konsumen</h2>
                    <p class="text-sm text-gray-500 mt-1">Lengkapi informasi konsumen dan data transaksi awal untuk menambahkan konsumen baru.</p>
                </div>
                <button type="button" onclick="closeTambahKonsumenModal()" class="text-gray-400 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <form action="{{ route('konsumen.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                @if($errors->any())
                    <div class="rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Informasi Konsumen --}}
                <div>
                    <p class="text-sm font-semibold text-gray-800 flex items-center gap-2 border-b pb-2 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Informasi Konsumen
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID Konsumen</label>
                            <input type="text" readonly value="{{ $nextIdKonsumen ?? 'Otomatis' }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm bg-blue-50 text-gray-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Masukkan nama lengkap">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">NIK (Nomor Induk Kependudukan) <span class="text-red-500">*</span></label>
                            <input type="text" name="nik" maxlength="16" value="{{ old('nik') }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Masukkan 16 digit NIK">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. HP / WhatsApp <span class="text-red-500">*</span></label>
                            <input type="text" name="no_hp" value="{{ old('no_hp') }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Contoh: 081234567890">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Masukkan alamat email aktif">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Sesuai KTP <span class="text-red-500">*</span></label>
                            <textarea name="alamat" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"
                                placeholder="Masukkan alamat lengkap...">{{ old('alamat') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Informasi Kavling & Transaksi --}}
                <div>
                    <p class="text-sm font-semibold text-gray-800 flex items-center gap-2 border-b pb-2 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        Informasi Kavling & Transaksi
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Kavling <span class="text-red-500">*</span></label>
                            <select name="kavling_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="">Pilih Kavling</option>
                                @foreach($kavlings as $kavlingOption)
                                    <option value="{{ $kavlingOption->id }}" {{ old('kavling_id') == $kavlingOption->id ? 'selected' : '' }}>
                                        {{ $kavlingOption->kode_kavling }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Transaksi Awal <span class="text-red-500">*</span></label>
                            <select name="status_transaksi" class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="">Pilih Status</option>
                                <option value="reservasi" {{ old('status_transaksi') == 'reservasi' ? 'selected' : '' }}>Reservasi</option>
                                <option value="booking" {{ old('status_transaksi') == 'booking' ? 'selected' : '' }}>Booking</option>
                                <option value="dp" {{ old('status_transaksi') == 'dp' ? 'selected' : '' }}>DP</option>
                                <option value="cash_lunas" {{ old('status_transaksi') == 'cash_lunas' ? 'selected' : '' }}>Cash / Lunas</option>
                                <option value="angsuran" {{ old('status_transaksi') == 'angsuran' ? 'selected' : '' }}>Angsuran</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Transaksi <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_transaksi" value="{{ old('tanggal_transaksi') }}"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Agen / Marketing <span class="text-red-500">*</span></label>
                            <select name="agen_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="">Pilih Agen</option>
                                @foreach($agens as $agenOption)
                                    <option value="{{ $agenOption->id }}" {{ old('agen_id') == $agenOption->id ? 'selected' : '' }}>
                                        {{ $agenOption->nama_agen }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Informasi Pembayaran Awal --}}
                <div>
                    <p class="text-sm font-semibold text-gray-800 flex items-center gap-2 border-b pb-2 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        Informasi Pembayaran Awal
                    </p>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Reservasi</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                                <input type="number" step="0.01" name="nominal_reservasi" value="{{ old('nominal_reservasi', 0) }}"
                                    class="w-full border rounded-lg pl-8 pr-3 py-2 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Booking</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                                <input type="number" step="0.01" name="nominal_booking" value="{{ old('nominal_booking', 0) }}"
                                    class="w-full border rounded-lg pl-8 pr-3 py-2 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Down Payment (DP)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                                <input type="number" step="0.01" name="down_payment" value="{{ old('down_payment', 0) }}"
                                    class="w-full border rounded-lg pl-8 pr-3 py-2 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Catatan --}}
                <div>
                    <p class="text-sm font-semibold text-gray-800 flex items-center gap-2 border-b pb-2 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Catatan Tambahan
                    </p>
                    <textarea name="catatan" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"
                        placeholder="Tambahkan catatan khusus jika ada (opsional)...">{{ old('catatan') }}</textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeTambahKonsumenModal()"
                        class="px-5 py-2 rounded-lg text-sm font-medium border text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="bg-gray-900 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Simpan Konsumen
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function openTambahKonsumenModal() {
        const modal = document.getElementById('tambahKonsumenModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeTambahKonsumenModal() {
        const modal = document.getElementById('tambahKonsumenModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if($errors->any())
            openTambahKonsumenModal();
        @endif
    });
</script>
@endsection