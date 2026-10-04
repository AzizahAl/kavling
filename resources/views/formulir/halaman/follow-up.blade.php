{{-- MARKETING TOOLKIT — Follow-up Sheet --}}
@include('formulir.halaman._toolkit-gaya')

<p class="tk-judul">FOLLOW-UP SHEET</p>
<table class="grid tk-follow">
    <thead><tr><th style="width:13%">Tanggal</th><th style="width:20%">Nama</th><th style="width:10%">Unit</th><th style="width:21%">Status</th><th>Next action</th><th style="width:14%">Tanggal follow-up</th></tr></thead>
    <tbody>
        @for ($i = 0; $i < 47; $i++)
            <tr><td></td><td></td><td></td><td class="c">Prospek / Hot / Cold</td><td></td><td></td></tr>
        @endfor
    </tbody>
</table>

<p class="tk-sub">Kode Follow-up</p>
<ul>
    <li>Hot = siap memilih/bertransaksi dalam waktu dekat.</li>
    <li>Warm = tertarik tetapi masih membandingkan/menunggu.</li>
    <li>Cold = belum siap, tetap follow-up secara wajar.</li>
    <li>Selalu catat alasan keberatan konsumen agar strategi closing berikutnya lebih tepat.</li>
</ul>
