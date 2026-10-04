{{-- PPJB (konsumen) + LAMPIRAN.docx — naskah utama --}}
@include('formulir.halaman._ppjb-gaya')

<p class="ppjb-judul">PERJANJIAN PENGIKATAN JUAL BELI KAVLING (PPJB)</p>
<p class="ppjb-sub">PROYEK TECTONA RESIDEN</p>
<p class="c">Nomor : @if ($isi && filled($d['nomor'] ?? null)){{ $d['nomor'] }}@else TR/PPJB/2026/<x-isian lebar="18mm"/>@endif</p>

<p class="ppjb-bab">PEMBUKAAN</p>
<p>Pada hari ini <x-isian lebar="22mm" :nilai="$d['hari'] ?? null"/> tanggal <x-isian lebar="12mm" :nilai="$d['tgl'] ?? null"/> bulan <x-isian lebar="22mm" :nilai="$d['bulan'] ?? null"/> tahun <x-isian lebar="14mm" :nilai="$d['tahun'] ?? null"/> bertempat di <x-isian lebar="40mm" :nilai="$d['tempat'] ?? null"/>, telah dibuat dan ditandatangani Perjanjian Pengikatan Jual Beli Kavling ("PPJB") oleh dan antara:</p>

<p class="b ppjb-pihak">PIHAK PERTAMA (PENJUAL)</p>
@include('formulir.halaman._ppjb-identitas', ['p' => $d['penjual'] ?? []])
<p>Selanjutnya disebut <b>PIHAK PERTAMA</b>.</p>

<p class="b ppjb-pihak">PIHAK KEDUA (PEMBELI)</p>
@include('formulir.halaman._ppjb-identitas', ['p' => $d['pembeli'] ?? []])
<p>Selanjutnya disebut <b>PIHAK KEDUA</b>.</p>

<p>PIHAK PERTAMA dan PIHAK KEDUA selanjutnya secara bersama-sama disebut <b>Para Pihak</b>.</p>
<p class="j">Perjanjian ini dibuat berdasarkan asas itikad baik, keterbukaan, kejujuran, musyawarah, serta saling menghormati hak dan kewajiban masing-masing.</p>

<p class="ppjb-pasal">PASAL 1<br>OBJEK PERJANJIAN</p>
<p class="j">PIHAK PERTAMA sepakat menjual dan PIHAK KEDUA sepakat membeli satu bidang tanah kavling dengan data sebagai berikut:</p>
<table class="data ppjb-data">
    <tr><td class="lbl">Nama Proyek</td><td class="sep">:</td><td><b>TECTONA RESIDEN</b></td></tr>
    <tr><td class="lbl">Blok</td><td class="sep">:</td><td><x-isian lebar="40mm" :nilai="$d['blok'] ?? null"/></td></tr>
    <tr><td class="lbl">Nomor</td><td class="sep">:</td><td><x-isian lebar="40mm" :nilai="$d['nomor_kavling'] ?? null"/></td></tr>
    <tr><td class="lbl">Luas</td><td class="sep">:</td><td><x-isian lebar="40mm" :nilai="$d['luas'] ?? null"/> m²</td></tr>
    <tr><td class="lbl">Lokasi</td><td class="sep">:</td><td><x-isian penuh :nilai="$d['lokasi'] ?? null"/></td></tr>
</table>
<p class="j">Sebagaimana ditunjukkan dalam Siteplan Tectona Residen yang menjadi bagian tidak terpisahkan dari perjanjian ini.</p>

<p class="ppjb-pasal">PASAL 2<br>STATUS TANAH</p>
<ol>
    <li>PIHAK KEDUA telah memperoleh penjelasan mengenai status tanah yang saat ini masih dalam proses penyelesaian administrasi melalui Program TORA dan/atau proses administrasi pertanahan lainnya sesuai ketentuan yang berlaku.</li>
    <li>PIHAK KEDUA memahami bahwa penyelesaian proses tersebut melibatkan instansi pemerintah sehingga waktu penyelesaiannya dapat dipengaruhi oleh proses administrasi di luar kendali PIHAK PERTAMA.</li>
    <li>PIHAK PERTAMA berkewajiban mengupayakan penyelesaian proses administrasi tersebut dengan itikad baik hingga memungkinkan dilaksanakannya Akta Jual Beli (AJB).</li>
</ol>

<p class="ppjb-pasal">PASAL 3<br>HARGA DAN PEMBAYARAN</p>
<p>Harga jual kavling disepakati sebesar:</p>
<p>Rp <x-isian lebar="60mm" :nilai="$d['harga'] ?? null"/></p>
<p>(@if (filled($d['terbilang'] ?? null)){{ $d['terbilang'] }}@else Terbilang <x-isian lebar="110mm"/>@endif)</p>
<p style="margin-top:10pt">Pembayaran dilakukan dengan ketentuan:</p>
<ul>
    <li>Reservasi Rp500.000,00</li>
    <li>Booking Fee Rp2.000.000,00</li>
    <li>DP sesuai kesepakatan</li>
    <li>Sisa pembayaran secara tunai atau angsuran paling lama 18 (delapan belas) bulan.</li>
</ul>

<p class="ppjb-pasal">PASAL 4<br>HAK DAN KEWAJIBAN PIHAK PERTAMA</p>
<p>PIHAK PERTAMA berkewajiban:</p>
<ol type="a">
    <li>Menjelaskan kondisi dan status tanah secara benar.</li>
    <li>Menyerahkan kavling sesuai Siteplan.</li>
    <li>Mengupayakan penyelesaian legalitas hingga AJB dapat dilaksanakan sesuai ketentuan yang berlaku.</li>
    <li>Memberikan kwitansi atas setiap pembayaran yang diterima.</li>
</ol>

<p class="ppjb-pasal">PASAL 5<br>HAK DAN KEWAJIBAN PIHAK KEDUA</p>
<p>PIHAK KEDUA berkewajiban:</p>
<ol type="a">
    <li>Membayar sesuai jadwal yang disepakati.</li>
    <li>Menyerahkan dokumen administrasi yang diperlukan.</li>
    <li>Memberikan data yang benar.</li>
    <li>Menjaga komunikasi selama proses transaksi berlangsung.</li>
</ol>

<p class="ppjb-pasal">PASAL 6<br>KETERLAMBATAN PEMBAYARAN</p>
<ol>
    <li>Apabila terjadi keterlambatan pembayaran, PIHAK PERTAMA akan terlebih dahulu menyampaikan pengingat kepada PIHAK KEDUA.</li>
    <li>Apabila keterlambatan berlanjut, Para Pihak sepakat mengutamakan musyawarah untuk mencari solusi terbaik, termasuk kemungkinan penjadwalan ulang pembayaran yang disepakati secara tertulis.</li>
    <li>Dalam hal PIHAK KEDUA tidak menunjukkan itikad baik untuk menyelesaikan kewajibannya setelah dilakukan upaya komunikasi dan musyawarah, PIHAK PERTAMA dapat mengakhiri perjanjian sesuai ketentuan Pasal 7.</li>
</ol>

<p class="ppjb-pasal">PASAL 7<br>PEMBATALAN</p>
<ol>
    <li>Pembatalan dilakukan berdasarkan kesepakatan Para Pihak atau karena salah satu pihak tidak memenuhi kewajibannya sebagaimana diatur dalam perjanjian ini.</li>
    <li>Dalam hal pembatalan dilakukan oleh PIHAK KEDUA sebelum PPJB ditandatangani, ketentuan mengenai Booking Fee mengikuti Form Booking yang telah disepakati.</li>
    <li>Dalam hal pembatalan terjadi setelah PPJB ditandatangani, penyelesaian keuangan dilakukan secara proporsional dengan memperhitungkan pembayaran yang telah diterima serta biaya nyata yang telah dikeluarkan dan dapat dipertanggungjawabkan oleh PIHAK PERTAMA.</li>
</ol>

<p class="ppjb-pasal">PASAL 8<br>SERAH TERIMA DAN AJB</p>
<ol>
    <li>Serah terima kavling dilakukan sesuai kesepakatan Para Pihak.</li>
    <li>AJB akan dilaksanakan setelah:
        <ul>
            <li>pembayaran lunas;</li>
            <li>persyaratan administrasi terpenuhi; dan</li>
            <li>proses legalitas tanah memungkinkan untuk dilaksanakan sesuai ketentuan peraturan perundang-undangan.</li>
        </ul>
    </li>
</ol>

<p class="ppjb-pasal">PASAL 9<br>KEADAAN KAHAR (FORCE MAJEURE)</p>
<p class="j">Yang dimaksud keadaan kahar adalah keadaan di luar kemampuan Para Pihak, termasuk namun tidak terbatas pada bencana alam, kebakaran, perang, kebijakan pemerintah, perubahan peraturan, atau keadaan lain yang secara langsung mempengaruhi pelaksanaan perjanjian ini.</p>
<p class="j">Dalam hal terjadi keadaan kahar, Para Pihak sepakat menyelesaikannya melalui musyawarah.</p>

<p class="ppjb-pasal">PASAL 10<br>PENYELESAIAN PERSELISIHAN</p>
<p class="j">Apabila terjadi perselisihan, Para Pihak sepakat menyelesaikannya terlebih dahulu melalui musyawarah untuk mufakat.</p>
<p class="j">Apabila musyawarah tidak mencapai kesepakatan, Para Pihak dapat menempuh mediasi atau penyelesaian melalui Pengadilan Negeri yang berwenang sesuai domisili objek perjanjian, kecuali Para Pihak kemudian menyepakati forum penyelesaian sengketa lain secara tertulis.</p>

<p class="ppjb-pasal">PASAL 11<br>KETENTUAN PENUTUP</p>
<ol>
    <li>Hal-hal yang belum diatur dalam perjanjian ini akan disepakati kemudian dalam bentuk addendum yang ditandatangani oleh Para Pihak.</li>
    <li>Perjanjian ini dibuat dalam keadaan sadar, tanpa paksaan, kekhilafan, maupun penipuan dari pihak mana pun.</li>
    <li>Perjanjian ini mulai berlaku sejak tanggal ditandatangani.</li>
</ol>

<div style="page-break-inside: avoid">
    <table class="data" style="width:auto;margin-top:10pt">
        <tr><td class="lbl">Dibuat di</td><td class="sep">:</td><td><x-isian lebar="55mm" :nilai="$d['tempat'] ?? null"/></td></tr>
        <tr><td class="lbl">Tanggal</td><td class="sep">:</td><td><x-isian lebar="55mm" :nilai="$d['tanggal'] ?? null"/></td></tr>
    </table>
    <table class="ttd ppjb-ttd">
        <tr>
            <td><b>PIHAK PERTAMA</b><div class="ruang"></div><b>(@include('formulir.halaman._nama-ttd', ['nama' => $d['penjual']['nama'] ?? null]))</b></td>
            <td><b>SAKSI 1</b><div class="ruang"></div><b>(@include('formulir.halaman._nama-ttd', ['nama' => null]))</b></td>
            <td><b>SAKSI II</b><div class="ruang"></div><b>(@include('formulir.halaman._nama-ttd', ['nama' => null]))</b></td>
            <td><b>PIHAK KEDUA</b><div class="ruang"></div><b>(@include('formulir.halaman._nama-ttd', ['nama' => $d['pembeli']['nama'] ?? null]))</b></td>
        </tr>
    </table>
</div>
