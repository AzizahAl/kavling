{{-- MARKETING TOOLKIT — Checklist PPJBS / PPJB --}}
@include('formulir.halaman._toolkit-gaya')

<p class="tk-judul">CHECKLIST PPJBS / PPJB</p>
<p>Halaman ini adalah checklist persiapan, bukan pengganti naskah perjanjian final.</p>
<ul class="centang" style="margin-top:8pt">
    @foreach ([
        'Identitas para pihak lengkap dan sesuai KTP',
        'Data unit sesuai master/siteplan',
        'Harga total dan metode pembayaran jelas',
        'Jadwal pembayaran jelas',
        'Ketentuan keterlambatan jelas',
        'Ketentuan pembatalan/pengalihan jelas',
        'Status dan dokumen legalitas dilampirkan/diterangkan',
        'Kewajiban pajak/biaya administrasi dijelaskan',
        'Ketentuan pembangunan/serah terima dijelaskan',
        'Dokumen diperiksa pihak yang berwenang/notaris bila diperlukan',
        'Para pihak menerima salinan dokumen',
    ] as $item)
        <li><span class="kotak"></span>{{ $item }}</li>
    @endforeach
</ul>

<p class="tk-sub" style="margin-top:16pt">Catatan Legal</p>
<p class="j">Untuk keamanan transaksi, gunakan naskah PPJBS/PPJB final yang telah disusun dan ditinjau oleh pihak berwenang. Jangan mengubah klausul material secara sepihak melalui formulir marketing.</p>
