{{-- LAMPIRAN C — Pernyataan Penjelasan Status Tanah --}}
@include('formulir.halaman._ppjb-gaya')
@include('formulir.halaman._lampiran-kepala', ['judulLampiran' => 'PERNYATAAN PENJELASAN STATUS TANAH', 'lampiran' => 'LAMPIRAN C'])

<p class="lamp-sub">PERNYATAAN</p>
<p>Saya yang bertanda tangan di bawah ini:</p>
<table class="data ppjb-data">
    <tr><td class="lbl">Nama</td><td class="sep">:</td><td><x-isian penuh :nilai="$d['pembeli']['nama'] ?? null"/></td></tr>
    <tr><td class="lbl">NIK</td><td class="sep">:</td><td><x-isian penuh :nilai="$d['pembeli']['nik'] ?? null"/></td></tr>
    <tr><td class="lbl">Alamat</td><td class="sep">:</td><td><x-isian penuh :nilai="$d['pembeli']['alamat'] ?? null"/></td></tr>
</table>
<p class="j" style="margin-top:8pt">Dengan ini menyatakan bahwa sebelum melakukan transaksi pembelian kavling di <b>TECTONA RESIDEN</b>, saya telah memperoleh penjelasan dari PIHAK PERTAMA mengenai:</p>
<ul>
    <li>Status tanah.</li>
    <li>Program TORA.</li>
    <li>Tahapan pelepasan hak.</li>
    <li>Proses menuju AJB.</li>
    <li>Hak dan kewajiban para pihak.</li>
    <li>Tahapan administrasi yang akan dilalui.</li>
</ul>
<p class="j">Saya memahami bahwa proses administrasi tersebut melibatkan instansi pemerintah sehingga penyelesaiannya mengikuti ketentuan dan waktu yang berlaku.</p>
<p class="j">Demikian pernyataan ini dibuat dengan sebenarnya tanpa adanya paksaan dari pihak mana pun.</p>

<table style="width:auto;margin-top:12pt">
    <tr><td>Garut, <x-isian lebar="45mm" :nilai="$d['tanggal'] ?? null"/></td></tr>
    <tr><td class="c" style="padding-top:4pt">Yang Menyatakan</td></tr>
    <tr><td style="height:62pt"></td></tr>
    <tr><td class="c">(@include('formulir.halaman._nama-ttd', ['nama' => $d['pembeli']['nama'] ?? null]))</td></tr>
</table>
