{{-- LAMPIRAN D — Dokumen yang Diterima Pembeli --}}
@include('formulir.halaman._ppjb-gaya')
@include('formulir.halaman._lampiran-kepala', ['judulLampiran' => 'DOKUMEN YANG DITERIMA PEMBELI', 'lampiran' => 'LAMPIRAN D'])

<p class="lamp-sub">CHECKLIST DOKUMEN</p>
<table class="grid">
    <thead><tr><th style="width:10%">No</th><th>Dokumen</th><th style="width:14%">Ada</th></tr></thead>
    <tbody>
        @foreach (['Form Reservasi', 'Form Booking', 'Kwitansi Reservasi', 'Kwitansi Booking', 'Surat Pemesanan Kavling', 'PPJB', 'Jadwal Pembayaran', 'Kartu Pembayaran', 'Siteplan', 'Pricelist', 'Berita Acara Serah Terima', 'Surat Pelunasan', 'AJB (setelah persyaratan terpenuhi)', 'Dokumen lain (jika ada)'] as $dok)
            <tr><td class="c">{{ $loop->iteration }}</td><td>{{ $dok }}</td><td class="c"><span class="kotak" style="margin:0"></span></td></tr>
        @endforeach
    </tbody>
</table>

<p class="j" style="margin-top:10pt">Saya menyatakan telah menerima dokumen-dokumen tersebut sebagaimana diberi tanda centang.</p>
<p>Garut, <x-isian lebar="45mm" :nilai="$d['tanggal'] ?? null"/></p>
<table class="ttd lamp-ttd">
    <tr>
        <td>PIHAK PERTAMA<div class="ruang"></div>(@include('formulir.halaman._nama-ttd', ['nama' => $d['penjual']['nama'] ?? null]))</td>
        <td>PIHAK KEDUA<div class="ruang"></div>(@include('formulir.halaman._nama-ttd', ['nama' => $d['pembeli']['nama'] ?? null]))</td>
    </tr>
</table>
