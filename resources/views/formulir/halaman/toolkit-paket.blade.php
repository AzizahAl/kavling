{{-- MARKETING TOOLKIT — paket pembayaran & alur --}}
@include('formulir.halaman._toolkit-gaya')

<p class="tk-judul">PAKET PEMBAYARAN &amp; BIAYA AWAL</p>
<table class="grid">
    <thead><tr><th style="width:30%">Komponen</th><th>Ketentuan</th></tr></thead>
    <tbody>
        <tr><td>Reservasi</td><td>Rp500.000</td></tr>
        <tr><td>Booking fee</td><td>Rp2.000.000</td></tr>
        <tr><td>Pembatalan booking</td><td>Ketentuan internal: Rp1.000.000 dialokasikan untuk komisi/biaya marketing sesuai kebijakan proyek</td></tr>
        <tr><td>DP</td><td>Bebas / disesuaikan kesepakatan</td></tr>
        <tr><td>Cicilan</td><td>Maksimal 18 bulan</td></tr>
        <tr><td>Harga tanah</td><td>Mengikuti unit &amp; pricelist saat transaksi</td></tr>
    </tbody>
</table>

<p class="tk-sub">Alur Pembayaran Sederhana</p>
<ul>
    <li>Calon konsumen memilih unit.</li>
    <li>Sales cek status unit ke admin.</li>
    <li>Jika perlu penahanan awal <span class="sym">→</span> Reservasi.</li>
    <li>Jika konsumen siap lanjut <span class="sym">→</span> Booking.</li>
    <li>Dokumen pemesanan disiapkan dan ditandatangani.</li>
    <li>Skema pembayaran/angsuran disepakati.</li>
    <li>PPJBS/PPJB final diproses sesuai dokumen proyek.</li>
    <li>Pembayaran dan bukti transaksi diarsipkan.</li>
</ul>
<p class="j">Penting: nominal, rekening pembayaran, dan kebijakan pembatalan harus mengikuti instruksi admin proyek terbaru. Jangan menerima pembayaran ke rekening pribadi tanpa otorisasi.</p>

<p class="tk-sub" style="margin-top:16pt">ALUR PEMBELIAN KONSUMEN</p>
<table class="grid">
    <thead><tr><th style="width:12%">Tahap</th><th style="width:28%">Fokus</th><th>Output</th></tr></thead>
    <tbody>
        @foreach ([
            ['Gali kebutuhan', 'Budget, ukuran, tujuan, waktu beli.'],
            ['Presentasi', 'Tunjukkan lokasi, siteplan, unit, harga, dan status legal.'],
            ['Pilih unit', 'Pastikan unit benar-benar tersedia.'],
            ['Kunjungan', 'Ajak survey lokasi bila memungkinkan.'],
            ['Reservasi/Booking', 'Gunakan formulir resmi.'],
            ['SPK', 'Catat unit, harga, cara bayar, dan kesepakatan.'],
            ['PPJBS/PPJB', 'Gunakan dokumen final yang telah disiapkan proyek.'],
            ['Pembayaran', 'Catat setiap transaksi dan bukti.'],
            ['Serah dokumen', 'Arsipkan dokumen konsumen dan berikan salinannya.'],
        ] as [$fokus, $output])
            <tr><td class="c">{{ $loop->iteration }}</td><td>{{ $fokus }}</td><td>{{ $output }}</td></tr>
        @endforeach
    </tbody>
</table>
