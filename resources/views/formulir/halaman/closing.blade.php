{{-- MARKETING TOOLKIT — Sales Closing Sheet --}}
@include('formulir.halaman._toolkit-gaya')

<p class="tk-judul">SALES CLOSING SHEET</p>
<p class="tk-sub" style="margin-top:0">Keberatan Konsumen</p>
<table class="grid">
    <thead><tr><th style="width:22%">Keberatan</th><th>Jawaban yang diberikan</th><th style="width:30%">Tindak lanjut</th></tr></thead>
    <tbody>
        @foreach (['Harga', 'Legalitas', 'Cicilan', 'Lokasi', 'Ukuran unit', 'Lainnya'] as $k)
            <tr><td style="height:34pt">{{ $k }}</td><td></td><td></td></tr>
        @endforeach
    </tbody>
</table>

<p class="tk-sub" style="margin-top:16pt">Checklist Sebelum Closing</p>
<ul class="centang">
    @foreach (['Kebutuhan konsumen jelas', 'Unit sesuai kebutuhan', 'Harga terbaru sudah dikonfirmasi', 'Status legalitas dijelaskan', 'Cara pembayaran jelas', 'Konsumen memahami reservasi/booking', 'Tidak ada janji di luar kewenangan sales', 'Next step sudah ditentukan'] as $item)
        <li><span class="kotak"></span>{{ $item }}</li>
    @endforeach
</ul>

<p class="tk-sub" style="margin-top:16pt">Kalimat Closing Sederhana</p>
<p class="j">“Kalau unit dan skemanya sudah sesuai kebutuhan Bapak/Ibu, langkah berikutnya kita amankan unitnya melalui proses resmi. Saya bantu cek status unit dan siapkan formulirnya.”</p>
