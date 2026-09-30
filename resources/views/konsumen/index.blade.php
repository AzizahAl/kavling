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

    {{-- ===================== Stat Cards ===================== --}}
    @php
        $cardBase  = 'bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4 min-h-[96px] flex flex-col justify-between';
        $cardLabel = 'text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap';
        $cardValue = 'text-2xl font-bold text-gray-900 leading-none';

        $countCards = [
            'Total Konsumen' => $stats['total_konsumen'],
            'Reservasi'      => $stats['reservasi'],
            'Booking'        => $stats['booking'],
            'DP'             => $stats['dp'],
            'Cash / Lunas'   => $stats['cash_lunas'],
            'Angsuran'       => $stats['angsuran'],
        ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-4">
        @foreach($countCards as $label => $value)
            <div class="{{ $cardBase }}">
                <p class="{{ $cardLabel }}">{{ $label }}</p>
                <p class="{{ $cardValue }} mt-3">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="{{ $cardBase }}">
            <p class="{{ $cardLabel }}">Total Nilai Penjualan</p>
            <p class="{{ $cardValue }} mt-3">Rp{{ number_format($stats['total_nilai_penjualan'], 0, ',', '.') }}</p>
        </div>
        <div class="{{ $cardBase }}">
            <p class="{{ $cardLabel }}">Total Pembayaran Masuk</p>
            <p class="{{ $cardValue }} mt-3">Rp{{ number_format($stats['total_pembayaran_masuk'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- ===================== Toolbar ===================== --}}
    <form method="GET" action="{{ route('konsumen.index') }}" class="flex flex-wrap items-center gap-3 mb-4">
        <div class="relative flex-1 min-w-[220px] max-w-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, ID, kavling..."
                   class="w-full border border-gray-200 rounded-lg pl-9 pr-3 py-2 text-sm text-gray-700 bg-white">
        </div>

        <select name="status" onchange="this.form.submit()" class="border border-gray-200 bg-white rounded-lg px-3 py-2 text-sm text-gray-700">
            <option value="">Semua Status</option>
            <option value="reservasi" {{ request('status') == 'reservasi' ? 'selected' : '' }}>Reservasi</option>
            <option value="booking" {{ request('status') == 'booking' ? 'selected' : '' }}>Booking</option>
            <option value="dp" {{ request('status') == 'dp' ? 'selected' : '' }}>DP</option>
        </select>

        <select name="metode" onchange="this.form.submit()" class="border border-gray-200 bg-white rounded-lg px-3 py-2 text-sm text-gray-700">
            <option value="">Semua Metode</option>
            <option value="cash_lunas" {{ request('metode') == 'cash_lunas' ? 'selected' : '' }}>Cash / Lunas</option>
            <option value="angsuran" {{ request('metode') == 'angsuran' ? 'selected' : '' }}>Angsuran</option>
        </select>

        <select name="agen" onchange="this.form.submit()" class="border border-gray-200 bg-white rounded-lg px-3 py-2 text-sm text-gray-700">
            <option value="">Semua Agen</option>
            @foreach($agens as $agenOption)
                <option value="{{ $agenOption->id }}" {{ request('agen') == $agenOption->id ? 'selected' : '' }}>
                    {{ $agenOption->nama_agen }}
                </option>
            @endforeach
        </select>

        <select name="dokumen" onchange="this.form.submit()" class="border border-gray-200 bg-white rounded-lg px-3 py-2 text-sm text-gray-700">
            <option value="">Filter Dokumen</option>
            <option value="status_ppjb:proses" {{ request('dokumen') == 'status_ppjb:proses' ? 'selected' : '' }}>PPJB Proses</option>
            <option value="status_ppjb:selesai" {{ request('dokumen') == 'status_ppjb:selesai' ? 'selected' : '' }}>PPJB Selesai</option>
            <option value="status_ajb:proses" {{ request('dokumen') == 'status_ajb:proses' ? 'selected' : '' }}>AJB Proses</option>
            <option value="status_ajb:selesai" {{ request('dokumen') == 'status_ajb:selesai' ? 'selected' : '' }}>AJB Selesai</option>
        </select>

        @if(request('search') || request('status') || request('metode') || request('agen') || request('dokumen'))
            <a href="{{ route('konsumen.index') }}"
               class="border border-gray-200 bg-white rounded-lg px-3 py-2 text-sm text-gray-700 flex items-center gap-1.5 hover:bg-gray-50">
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

    {{-- ===================== Tabel ===================== --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase text-[11px] tracking-wider">
                <tr class="whitespace-nowrap">
                    <th class="px-4 py-3 text-left font-semibold">Konsumen</th>
                    <th class="px-4 py-3 text-left font-semibold">Kavling</th>
                    <th class="px-4 py-3 text-left font-semibold">Tanggal Masuk</th>
                    <th class="px-4 py-3 text-left font-semibold">Agen</th>
                    <th class="px-4 py-3 text-left font-semibold">Status Transaksi Awal</th>
                    <th class="px-4 py-3 text-left font-semibold">Reservasi</th>
                    <th class="px-4 py-3 text-left font-semibold">Booking</th>
                    <th class="px-4 py-3 text-left font-semibold">DP</th>
                    <th class="px-4 py-3 text-left font-semibold">PPJB</th>
                    <th class="px-4 py-3 text-left font-semibold">AJB/Legal</th>
                    <th class="px-4 py-3 text-center font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($konsumens as $konsumen)
                    @php
                        $statusBadge = [
                            'reservasi'  => 'bg-gray-100 text-gray-700',
                            'booking'    => 'bg-yellow-100 text-yellow-700',
                            'dp'         => 'bg-blue-100 text-blue-700',
                            'cash_lunas' => 'bg-green-100 text-green-700',
                            'angsuran'   => 'bg-purple-100 text-purple-700',
                        ][$konsumen->status_transaksi] ?? 'bg-gray-100 text-gray-700';

                        $statusLabel = [
                            'reservasi'  => 'RESERVASI',
                            'booking'    => 'BOOKING',
                            'dp'         => 'DP',
                            'cash_lunas' => 'CASH/LUNAS',
                            'angsuran'   => 'ANGSURAN',
                        ][$konsumen->status_transaksi] ?? strtoupper($konsumen->status_transaksi);

                        $metodeLabel = [
                            'cash_lunas' => 'Cash / Lunas',
                            'angsuran'   => 'Angsuran' . ($konsumen->jumlah_angsuran ? ' ' . $konsumen->jumlah_angsuran . 'x' : ''),
                        ][$konsumen->skema_bayar] ?? null;

                        $dokBadge = fn($v) => match($v) {
                            'selesai' => 'bg-green-100 text-green-700',
                            'proses'  => 'bg-yellow-100 text-yellow-700',
                            default   => 'bg-gray-100 text-gray-500',
                        };

                        // Status DP dihitung dari total pembayaran masuk (withSum di controller)
                        $terbayar   = (float) ($konsumen->riwayat_pembayarans_sum_nominal ?? 0);
                        $awalTarget = (float) $konsumen->nominal_reservasi + (float) $konsumen->nominal_booking;
                        $dpTarget   = (float) $konsumen->down_payment;

                        if ($dpTarget <= 0) {
                            $statusDp = 'belum';
                        } elseif ($terbayar >= $awalTarget + $dpTarget) {
                            $statusDp = 'selesai';
                        } elseif ($terbayar > $awalTarget) {
                            $statusDp = 'proses';
                        } else {
                            $statusDp = 'belum';
                        }

                        $badge = 'inline-block px-2.5 py-1 rounded-full text-[11px] font-semibold tracking-wide';
                    @endphp
                    <tr class="hover:bg-gray-50/70 align-middle">
                        {{-- 1. ID kecil di atas, nama di bawah --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <p class="text-[11px] font-medium text-gray-400 tracking-wide">{{ $konsumen->id_konsumen }}</p>
                            <p class="font-semibold text-gray-900">{{ $konsumen->nama_lengkap }}</p>
                        </td>

                        {{-- 2. Kavling --}}
                        <td class="px-4 py-3.5 whitespace-nowrap font-medium text-gray-900">{{ $konsumen->kavling->kode_kavling ?? '-' }}</td>

                        {{-- 3. Tanggal masuk --}}
                        <td class="px-4 py-3.5 whitespace-nowrap text-gray-600">{{ $konsumen->tanggal_transaksi->format('d M Y') }}</td>

                        {{-- 4. Agen --}}
                        <td class="px-4 py-3.5 whitespace-nowrap text-gray-600">{{ $konsumen->agen->nama_agen ?? '-' }}</td>

                        {{-- 5. Status transaksi awal --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="{{ $badge }} {{ $statusBadge }}">{{ $statusLabel }}</span>
                            @if($metodeLabel)
                                <p class="text-xs text-gray-400 mt-1">{{ $metodeLabel }}</p>
                            @endif
                        </td>

                        {{-- 6. Reservasi --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="{{ $badge }} {{ $dokBadge($konsumen->status_reservasi) }}">{{ strtoupper($konsumen->status_reservasi) }}</span>
                            <p class="text-xs text-gray-500 mt-1">Rp{{ number_format($konsumen->nominal_reservasi, 0, ',', '.') }}</p>
                        </td>

                        {{-- 7. Booking --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="{{ $badge }} {{ $dokBadge($konsumen->status_booking) }}">{{ strtoupper($konsumen->status_booking) }}</span>
                            <p class="text-xs text-gray-500 mt-1">Rp{{ number_format($konsumen->nominal_booking, 0, ',', '.') }}</p>
                        </td>

                        {{-- 8. DP --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="{{ $badge }} {{ $dokBadge($statusDp) }}">{{ strtoupper($statusDp) }}</span>
                            <p class="text-xs text-gray-500 mt-1">Rp{{ number_format($konsumen->down_payment, 0, ',', '.') }}</p>
                        </td>

                        {{-- 9. PPJB --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="{{ $badge }} {{ $dokBadge($konsumen->status_ppjb) }}">{{ strtoupper($konsumen->status_ppjb) }}</span>
                        </td>

                        {{-- 10. AJB/Legal --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="{{ $badge }} {{ $dokBadge($konsumen->status_ajb) }}">{{ strtoupper($konsumen->status_ajb) }}</span>
                        </td>

                        {{-- 11. Aksi --}}
                        <td class="px-4 py-3.5 whitespace-nowrap text-center">
                            <a href="{{ route('konsumen.show', $konsumen) }}"
                               class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 inline-block">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-10 text-center text-gray-400">Belum ada data konsumen.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Tambah Konsumen --}}
    @php
        // Format angka jadi 1.234.567 untuk nilai awal input (termasuk old() setelah error validasi)
        $fmt = fn($v) => ($v !== null && $v !== '') ? number_format((int) $v, 0, ',', '.') : '';

        $resVal   = old('nominal_reservasi', 0);
        $bookVal  = old('nominal_booking', 2000000);
        $dpVal    = old('down_payment', 0);
        $bayarVal = old('total_bayar');
    @endphp

    <div id="tambahKonsumenModal" class="fixed inset-0 z-50 hidden items-center justify-center">
        <div class="absolute inset-0 bg-black/50" onclick="closeTambahKonsumenModal()"></div>

        <div class="relative bg-white rounded-xl shadow-lg w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between px-6 py-5 border-b sticky top-0 bg-white z-10">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Tambah Konsumen</h2>
                    <p class="text-sm text-gray-500 mt-1">Lengkapi informasi konsumen dan data transaksi awal untuk menambahkan konsumen baru.</p>
                </div>
                <button type="button" onclick="closeTambahKonsumenModal()" class="text-gray-400 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <form id="formKonsumen" action="{{ route('konsumen.store') }}" method="POST" class="p-6 space-y-6"
                  onsubmit="return cekDp()">
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

                    {{-- Kode Kavling + Tipe + Harga Jual (dari Master Kavling) --}}
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Kavling <span class="text-red-500">*</span></label>
                            <select name="kavling_id" id="kavling_id" onchange="updateKavlingInfo(true)"
                                    class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="">Pilih Kavling</option>
                                @foreach($kavlings as $kavlingOption)
                                    <option value="{{ $kavlingOption->id }}"
                                            data-kode="{{ $kavlingOption->kode_kavling }}"
                                            data-label="{{ $kavlingOption->kode_kavling }} — {{ $kavlingOption->tipe }} — Rp{{ number_format($kavlingOption->harga_jual, 0, ',', '.') }}"
                                            data-tipe="{{ $kavlingOption->tipe }}"
                                            data-harga="{{ (int) $kavlingOption->harga_jual }}"
                                            {{ old('kavling_id') == $kavlingOption->id ? 'selected' : '' }}>
                                        {{ $kavlingOption->kode_kavling }} — {{ $kavlingOption->tipe }} — Rp{{ number_format($kavlingOption->harga_jual, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                            <input type="text" id="info_tipe" readonly tabindex="-1"
                                class="w-full border rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-600 cursor-not-allowed" placeholder="-">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual</label>
                            <input type="text" id="info_harga" readonly tabindex="-1"
                                class="w-full border rounded-lg px-3 py-2 text-sm bg-gray-100 text-gray-600 cursor-not-allowed" placeholder="-">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Transaksi Awal <span class="text-red-500">*</span></label>
                            <select name="status_transaksi" class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="">Pilih Status</option>
                                <option value="reservasi" {{ old('status_transaksi') == 'reservasi' ? 'selected' : '' }}>Reservasi</option>
                                <option value="booking" {{ old('status_transaksi') == 'booking' ? 'selected' : '' }}>Booking</option>
                                <option value="dp" {{ old('status_transaksi') == 'dp' ? 'selected' : '' }}>DP</option>
                            </select>
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
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran <span class="text-red-500">*</span></label>
                            <select name="metode_pembayaran" id="metode_pembayaran" onchange="toggleMetode()"
                                    class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="">Pilih Metode</option>
                                <option value="cash_lunas" {{ old('metode_pembayaran') == 'cash_lunas' ? 'selected' : '' }}>Cash / Lunas</option>
                                <option value="angsuran" {{ old('metode_pembayaran') == 'angsuran' ? 'selected' : '' }}>Angsuran</option>
                            </select>
                        </div>
                        <div id="tenorWrap" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tenor (bulan) <span class="text-red-500">*</span></label>
                            <input type="number" name="tenor" id="tenor" min="18" step="1" value="{{ old('tenor', 18) }}"
                                   oninput="hitung()" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <p class="text-xs text-gray-400 mt-1">Minimal 18 bulan, bisa diubah.</p>
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
                                <input type="text" inputmode="numeric" autocomplete="off" id="nominal_reservasi_display"
                                    value="{{ $fmt($resVal) }}" oninput="formatUang(this, 'nominal_reservasi')"
                                    class="w-full border rounded-lg pl-8 pr-3 py-2 text-sm">
                                <input type="hidden" name="nominal_reservasi" id="nominal_reservasi" value="{{ $resVal !== null && $resVal !== '' ? (int) $resVal : '' }}">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Booking</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                                <input type="text" inputmode="numeric" autocomplete="off" id="nominal_booking_display"
                                    value="{{ $fmt($bookVal) }}" oninput="formatUang(this, 'nominal_booking')"
                                    class="w-full border rounded-lg pl-8 pr-3 py-2 text-sm">
                                <input type="hidden" name="nominal_booking" id="nominal_booking" value="{{ $bookVal !== null && $bookVal !== '' ? (int) $bookVal : '' }}">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Down Payment (DP)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                                <input type="text" inputmode="numeric" autocomplete="off" id="down_payment_display"
                                    value="{{ $fmt($dpVal) }}" oninput="formatUang(this, 'down_payment')"
                                    class="w-full border rounded-lg pl-8 pr-3 py-2 text-sm">
                                <input type="hidden" name="down_payment" id="down_payment" value="{{ $dpVal !== null && $dpVal !== '' ? (int) $dpVal : '' }}">
                            </div>
                            <p id="dp_hint" class="text-xs text-gray-400 mt-1">Pilih kavling untuk melihat minimal DP</p>
                        </div>
                    </div>

                    {{-- Ringkasan perhitungan otomatis --}}
                    <div class="rounded-lg bg-gray-50 border px-4 py-3 mt-4 text-sm space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Harga Jual</span>
                            <span id="sum_harga" class="font-medium text-gray-900">Rp0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Reservasi + Booking + DP</span>
                            <span id="sum_total_awal" class="font-medium text-gray-900">Rp0</span>
                        </div>
                        <div class="flex justify-between border-t pt-1.5">
                            <span id="sum_sisa_label" class="text-gray-500">Sisa Pelunasan</span>
                            <span id="sum_sisa" class="font-semibold text-gray-900">Rp0</span>
                        </div>
                        <div id="sum_angsuran_row" class="hidden justify-between">
                            <span class="text-gray-500">Angsuran per bulan (<span id="sum_tenor">18</span>x)</span>
                            <span id="sum_angsuran" class="font-semibold text-gray-900">Rp0</span>
                        </div>
                    </div>
                </div>

                {{-- Status Pembayaran (checkbox) --}}
                <div>
                    <p class="text-sm font-semibold text-gray-800 flex items-center gap-2 border-b pb-2 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        Status Pembayaran
                    </p>

                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer w-fit">
                        <input type="hidden" name="sudah_bayar" value="0">
                        <input type="checkbox" name="sudah_bayar" id="sudah_bayar" value="1" onchange="toggleBayar()"
                               {{ old('sudah_bayar') == '1' ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-gray-300 accent-gray-900">
                        Sudah melakukan pembayaran
                    </label>

                    <div id="bayarWrap" class="hidden mt-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Transaksi <span class="text-red-500">*</span></label>
                                <input type="date" name="tanggal_transaksi" value="{{ old('tanggal_transaksi') }}"
                                    class="w-full border rounded-lg px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total Bayar <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                                    <input type="text" inputmode="numeric" autocomplete="off" id="total_bayar_display"
                                        value="{{ $fmt($bayarVal) }}" oninput="formatUang(this, 'total_bayar')"
                                        class="w-full border rounded-lg pl-8 pr-3 py-2 text-sm">
                                    <input type="hidden" name="total_bayar" id="total_bayar" value="{{ $bayarVal !== null && $bayarVal !== '' ? (int) $bayarVal : '' }}">
                                </div>
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
    const $id = (id) => document.getElementById(id);
    const rupiah = (n) => 'Rp' + Math.round(n).toLocaleString('id-ID');
    const angka = (id) => parseFloat($id(id).value) || 0;

    function openTambahKonsumenModal() {
        const modal = $id('tambahKonsumenModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeTambahKonsumenModal() {
        const modal = $id('tambahKonsumenModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // ===== Format ribuan pakai titik (1.234.567), nilai murni disimpan di input hidden =====
    function formatUang(displayEl, hiddenId) {
        const digits = displayEl.value.replace(/\D/g, '');
        const nilai  = digits ? parseInt(digits, 10) : '';
        displayEl.value = digits ? nilai.toLocaleString('id-ID') : '';
        $id(hiddenId).value = nilai;
        hitung();
    }

    function setUang(baseId, nilai) {
        $id(baseId).value = nilai;
        $id(baseId + '_display').value = nilai ? Math.round(nilai).toLocaleString('id-ID') : '';
    }

    // ===== Dropdown kavling: daftar tampil lengkap, setelah dipilih hanya kode =====
    function labelKavling(lengkap) {
        Array.from($id('kavling_id').options).forEach(function (o) {
            if (!o.value) return;
            o.text = lengkap ? o.dataset.label : o.dataset.kode;
        });
    }

    function initDropdownKavling() {
        const sel = $id('kavling_id');
        sel.addEventListener('mousedown', function () { labelKavling(true); });
        sel.addEventListener('focus',     function () { labelKavling(true); });
        sel.addEventListener('change',    function () { labelKavling(false); });
        sel.addEventListener('blur',      function () { labelKavling(false); });
        labelKavling(false);
    }

    // Harga jual diambil dari option kavling yang dipilih (data dari Master Kavling, sudah sesuai tahap)
    function getHargaJual() {
        const opt = $id('kavling_id').selectedOptions[0];
        return opt && opt.value ? (parseInt(opt.dataset.harga) || 0) : 0;
    }

    function getMinDp() {
        return Math.ceil(getHargaJual() * 0.15);
    }

    function updateKavlingInfo(autoIsiDp) {
        const opt   = $id('kavling_id').selectedOptions[0];
        const harga = getHargaJual();

        if (opt && opt.value) {
            $id('info_tipe').value  = opt.dataset.tipe || '-';
            $id('info_harga').value = rupiah(harga);
        } else {
            $id('info_tipe').value  = '';
            $id('info_harga').value = '';
        }

        const minDp = getMinDp();
        $id('dp_hint').textContent = harga > 0
            ? 'Minimal 15% = ' + rupiah(minDp)
            : 'Pilih kavling untuk melihat minimal DP';

        // Saat ganti kavling, DP otomatis diisi minimal 15% (masih bisa dinaikkan)
        if (autoIsiDp) {
            setUang('down_payment', minDp);
        }

        hitung();
    }

    function cekDp() {
        const harga = getHargaJual();
        if (harga > 0 && angka('down_payment') < getMinDp()) {
            alert('DP minimal 15% dari harga jual (' + rupiah(getMinDp()) + ').');
            return false;
        }
        return true;
    }

    function toggleMetode() {
        const angsuran = $id('metode_pembayaran').value === 'angsuran';
        $id('tenorWrap').classList.toggle('hidden', !angsuran);
        hitung();
    }

    // Checkbox "Sudah melakukan pembayaran"
    function toggleBayar() {
        $id('bayarWrap').classList.toggle('hidden', !$id('sudah_bayar').checked);
    }

    function hitung() {
        const harga     = getHargaJual();
        const totalAwal = angka('nominal_reservasi') + angka('nominal_booking') + angka('down_payment');
        const sisa      = Math.max(harga - totalAwal, 0);
        const angsuran  = $id('metode_pembayaran').value === 'angsuran';
        const tenor     = parseInt($id('tenor').value) || 0;

        $id('sum_harga').textContent      = rupiah(harga);
        $id('sum_total_awal').textContent = rupiah(totalAwal);
        $id('sum_sisa').textContent       = rupiah(sisa);
        $id('sum_sisa_label').textContent = angsuran ? 'Sisa yang Diangsur' : 'Sisa Pelunasan';

        const row = $id('sum_angsuran_row');
        if (angsuran && tenor > 0) {
            row.classList.remove('hidden');
            row.classList.add('flex');
            $id('sum_tenor').textContent    = tenor;
            $id('sum_angsuran').textContent = rupiah(sisa / tenor);
        } else {
            row.classList.add('hidden');
            row.classList.remove('flex');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        initDropdownKavling();
        updateKavlingInfo(false);
        toggleMetode();
        toggleBayar();

        @if($errors->any())
            openTambahKonsumenModal();
        @endif
    });
</script>
@endsection