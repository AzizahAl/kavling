{{-- MARKETING TOOLKIT — paket pembayaran & alur --}}
@include('formulir.halaman._toolkit-gaya')

@php
    $p = \App\Services\Pengaturan::semua();
    $dp = ($p['dp_minimal_persen'] ?? 0) > 0 ? 'Minimal ' . persen($p['dp_minimal_persen'], false) : 'Boleh tanpa DP';
    if (($p['dp_anjuran_persen'] ?? 0) > 0) { $dp .= ' · anjuran ' . persen($p['dp_anjuran_persen'], false) . ' dari harga kavling'; }
@endphp
<p class="tk-judul">PAKET PEMBAYARAN &amp; BIAYA AWAL</p>
<table class="grid">
    <thead><tr><th style="width:30%">Komponen</th><th>Ketentuan</th></tr></thead>
    <tbody>
        <tr><td>Reservasi</td><td>{{ rupiah($p['biaya_reservasi'] ?? 0) }} (di luar harga kavling), berlaku {{ $p['masa_reservasi_hari'] ?? '—' }} hari</td></tr>
        <tr><td>Booking fee</td><td>{{ rupiah($p['biaya_booking'] ?? 0) }} (di luar harga kavling)</td></tr>
        <tr><td>Pembatalan booking</td><td>Dipotong {{ rupiah($p['potongan_booking'] ?? 0) }} dari booking fee; reservasi dikembalikan penuh</td></tr>
        <tr><td>DP</td><td>{{ $dp }}</td></tr>
        <tr><td>Angsuran</td><td>Maksimal {{ $p['tenor_maksimal'] ?? '—' }} bulan</td></tr>
        <tr><td>Harga kavling</td><td>Luas × harga/m² tahap aktif saat transaksi dibuat (lihat Pricelist)</td></tr>
    </tbody>
</table>

<p class="tk-sub">Alur Transaksi</p>
<ul>
    <li>Calon konsumen dicatat sebagai lead oleh agen, lalu menjadi prospek.</li>
    <li>Unit dipilih dari kavling berstatus Tersedia; transaksi dibuat dan kavling ditahan {{ $p['batas_tahan_jam'] ?? '—' }} jam.</li>
    <li>Reservasi dibayar <span class="sym">→</span> status Reservasi (Form Reservasi).</li>
    <li>Booking fee dibayar <span class="sym">→</span> status Booking (Form Booking), lalu SPK dibuat.</li>
    <li>DP / angsuran dibayar sesuai jadwal angsuran.</li>
    <li>PPJB ditandatangani; pembayaran sampai lunas, lalu AJB.</li>
    <li>Setiap pembayaran mendapat kwitansi resmi dari admin.</li>
</ul>
<p class="j">Penting: nominal, rekening pembayaran, dan kebijakan pembatalan mengikuti ketentuan admin proyek. Jangan menerima pembayaran ke rekening pribadi tanpa otorisasi.</p>
