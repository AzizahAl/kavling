{{-- MARKETING TOOLKIT — Surat Pemesanan Kavling (SPK) --}}
@include('formulir.halaman._toolkit-gaya')

<p class="tk-judul">SURAT PEMESANAN KAVLING (SPK)</p>
<p>Nomor: <x-isian lebar="60mm" :nilai="$d['nomor'] ?? null"/></p>
<p>Pada hari ini, <x-isian lebar="28mm" :nilai="$d['hari'] ?? null"/> tanggal <x-isian lebar="40mm" :nilai="$d['tanggal'] ?? null"/>, telah terjadi pemesanan kavling antara:</p>
<table class="data tk-form">
    <tr><td class="lbl">PIHAK KONSUMEN / PEMESAN</td><td><x-isian penuh :nilai="$d['nama'] ?? null"/></td></tr>
    <tr><td class="lbl">PIHAK PENJUAL / PENGELOLA</td><td><x-isian penuh :nilai="$d['pengelola'] ?? null"/></td></tr>
</table>

<p class="tk-sub">Objek Pemesanan</p>
<table class="grid">
    <thead><tr><th style="width:35%">Item</th><th>Keterangan</th></tr></thead>
    <tbody>
        <tr><td>Proyek</td><td>Tectona Residen</td></tr>
        <tr><td>Unit</td><td>{{ $d['unit'] ?? '' }}</td></tr>
        <tr><td>Tipe</td><td>{{ $d['tipe'] ?? '' }}</td></tr>
        <tr><td>Ukuran</td><td>{{ $d['ukuran'] ?? '' }}</td></tr>
        <tr><td>Luas</td><td>{{ isset($d['luas']) ? $d['luas'] . ' m²' : '' }}</td></tr>
        <tr><td>Harga/m²</td><td>Rp {{ $d['harga_m2'] ?? '' }}</td></tr>
        <tr><td>Harga total</td><td>Rp {{ $d['harga'] ?? '' }}</td></tr>
        <tr><td>Cara pembayaran</td><td>{{ $d['cara_bayar'] ?? '' }}</td></tr>
    </tbody>
</table>

<p class="tk-sub">Ketentuan Pokok</p>
<ul>
    <li>Unit dan harga mengikuti data yang disepakati dan diverifikasi admin.</li>
    <li>Status legalitas disampaikan sesuai kondisi aktual proyek.</li>
    <li>Pembayaran hanya dilakukan melalui metode/rekening resmi yang ditetapkan pengelola.</li>
    <li>Ketentuan pembatalan, pengalihan, dan biaya lain mengikuti dokumen final proyek.</li>
    <li>SPK ini menjadi dasar administrasi pemesanan dan tidak menggantikan dokumen PPJBS/PPJB final.</li>
</ul>

@include('formulir.halaman._toolkit-ttd')
