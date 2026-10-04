{{-- MARKETING TOOLKIT — Form Kunjungan / Survey Lokasi --}}
@include('formulir.halaman._toolkit-gaya')

<p class="tk-judul">FORM KUNJUNGAN / SURVEY LOKASI</p>
<table class="data tk-form tk-form-lbl-pendek">
    @foreach (['Tanggal kunjungan', 'Nama konsumen', 'No. WhatsApp', 'Sales', 'Unit yang dilihat'] as $label)
        <tr><td class="lbl">{{ $label }}</td><td><x-isian penuh/></td></tr>
    @endforeach
</table>

<p class="tk-sub" style="margin-top:16pt">Hal yang Ditunjukkan</p>
<ul class="centang">
    @foreach (['Gerbang masuk', 'Jalan internal', 'Posisi unit', 'Area taman', 'Area putar balik', 'Lingkungan sekitar', 'Kondisi aktual lahan', 'Penjelasan status legalitas'] as $item)
        <li><span class="kotak"></span>{{ $item }}</li>
    @endforeach
</ul>

<p class="tk-sub" style="margin-top:16pt">Kesan &amp; Objek yang Diminati</p>
@for ($i = 0; $i < 7; $i++)<div class="tk-baris-tulis"></div>@endfor
