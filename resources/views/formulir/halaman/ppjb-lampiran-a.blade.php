{{-- LAMPIRAN A — Siteplan dan Data Kavling --}}
@include('formulir.halaman._ppjb-gaya')
@include('formulir.halaman._lampiran-kepala', ['judulLampiran' => 'SITEPLAN DAN DATA KAVLING', 'lampiran' => 'LAMPIRAN A'])

<p class="lamp-sub">DATA KAVLING</p>
<table class="data ppjb-data">
    <tr><td class="lbl">Nama Proyek</td><td class="sep">:</td><td><b>TECTONA RESIDEN</b></td></tr>
    <tr><td class="lbl">Lokasi</td><td class="sep">:</td><td>Jln. Panjaitan Kp. Rancabuaya, Desa. Purbayani. Kec. Caringin. Kab. Garut</td></tr>
    <tr><td class="lbl">Blok</td><td class="sep">:</td><td><x-isian lebar="60mm" :nilai="$d['blok'] ?? null"/></td></tr>
    <tr><td class="lbl">Nomor</td><td class="sep">:</td><td><x-isian lebar="60mm" :nilai="$d['nomor_kavling'] ?? null"/></td></tr>
    <tr><td class="lbl">Luas</td><td class="sep">:</td><td><x-isian lebar="60mm" :nilai="$d['luas'] ?? null"/> m²</td></tr>
    <tr><td class="lbl">Harga</td><td class="sep">:</td><td>Rp <x-isian lebar="55mm" :nilai="$d['harga'] ?? null"/></td></tr>
    <tr><td class="lbl">Batas-batas</td><td class="sep">:</td><td></td></tr>
    @foreach (['utara' => 'Utara', 'selatan' => 'Selatan', 'timur' => 'Timur', 'barat' => 'Barat'] as $arah => $label)
        <tr><td class="lbl" style="padding-left:8mm">{{ $label }}</td><td class="sep">:</td><td><x-isian lebar="60mm" :nilai="$d['batas'][$arah] ?? null"/></td></tr>
    @endforeach
</table>

<p class="b c" style="margin:8pt 0 4pt">Siteplan Kavling</p>
<div class="c"><img src="{{ $gambar('siteplan.jpg') }}" alt="Siteplan Tectona Residen" style="height:60mm;width:auto;border:1px solid #999"></div>

<p class="j" style="margin-top:8pt">Dengan menandatangani lampiran ini, Para Pihak menyatakan telah mengetahui dan menyetujui letak serta ukuran kavling yang diperjualbelikan.</p>
<p>Garut, <x-isian lebar="45mm" :nilai="$d['tanggal'] ?? null"/></p>
<table class="ttd lamp-ttd">
    <tr>
        <td><b>PIHAK PERTAMA</b><div class="ruang"></div><b>(@include('formulir.halaman._nama-ttd', ['nama' => $d['penjual']['nama'] ?? null]))</b></td>
        <td><b>PIHAK KEDUA</b><div class="ruang"></div><b>(@include('formulir.halaman._nama-ttd', ['nama' => $d['pembeli']['nama'] ?? null]))</b></td>
    </tr>
</table>
