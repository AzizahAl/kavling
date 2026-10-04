{{-- MARKETING TOOLKIT — Form Data Calon Konsumen --}}
@include('formulir.halaman._toolkit-gaya')

<p class="tk-judul">FORM DATA CALON KONSUMEN</p>
<table class="data tk-form">
    @foreach (['Nama lengkap', 'No. WhatsApp', 'Alamat', 'Pekerjaan', 'Unit yang diminati', 'Budget', 'Cara pembayaran yang diminati', 'Sumber informasi'] as $label)
        <tr><td class="lbl">{{ $label }}</td><td><x-isian penuh/></td></tr>
    @endforeach
</table>

<p class="tk-sub" style="margin-top:16pt">Kebutuhan Konsumen</p>
<ul class="centang">
    <li><span class="kotak"></span>Rumah tinggal</li>
    <li><span class="kotak"></span>Investasi</li>
    <li><span class="kotak"></span>Rumah keluarga</li>
    <li><span class="kotak"></span>Lainnya: <x-isian lebar="120mm"/></li>
</ul>

<p class="tk-sub" style="margin-top:16pt">Catatan Sales</p>
@for ($i = 0; $i < 6; $i++)<div class="tk-baris-tulis"></div>@endfor
