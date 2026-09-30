@php
    $pdf = $pdf ?? false;
    $ppjb = $jenis === 'PPJB';
    $judul = $ppjb ? 'PERJANJIAN PENGIKATAN JUAL BELI (PPJB)' : 'SURAT PEMESANAN KAVLING (SPK)';
    $kosong = '………………………………';
    $refundDp = \App\Services\Pengaturan::get('refund_dp_persen');
    $refundAng = \App\Services\Pengaturan::get('refund_angsuran_persen');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $jenis }} {{ $nomor }} - {{ $k->nama_lengkap }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #e5e7eb; font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; }
        .toolbar { position: sticky; top: 0; z-index: 10; display: flex; gap: 8px; flex-wrap: wrap; align-items: center; padding: 10px 16px; background: #1a5a3f; font-family: Arial, sans-serif; }
        .toolbar .judul { color: #fff; font-size: 14px; font-weight: 600; margin-right: auto; }
        .btn { display: inline-flex; padding: 8px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,.35); background: transparent; color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; }
        .btn:hover { background: rgba(255,255,255,.1); }
        .btn-utama { background: #fff; color: #1a5a3f; border-color: #fff; }
        .peringatan { max-width: 210mm; margin: 12px auto 0; padding: 10px 14px; background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; font-family: Arial, sans-serif; font-size: 13px; color: #92400e; }
        .paper { width: 210mm; min-height: 297mm; margin: 20px auto; padding: 20mm 22mm; background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.2); }
        h1 { font-size: 14pt; text-align: center; margin: 0; text-decoration: underline; }
        .nomor { text-align: center; margin: 2pt 0 16pt; }
        h2 { font-size: 12pt; text-align: center; margin: 14pt 0 4pt; }
        p { margin: 0 0 6pt; text-align: justify; }
        table { width: 100%; border-collapse: collapse; }
        .data td { padding: 1pt 4pt; vertical-align: top; }
        .data td:first-child { width: 36%; }
        .data td:nth-child(2) { width: 3%; }
        .grid td, .grid th { border: 1px solid #000; padding: 3pt 6pt; font-size: 11pt; }
        .grid th { background: #eee; }
        .r { text-align: right; }
        .c { text-align: center; }
        ol { margin: 0 0 6pt; padding-left: 18pt; }
        li { margin-bottom: 3pt; text-align: justify; }
        .ttd td { width: 50%; text-align: center; vertical-align: top; padding-top: 8pt; }
        .ruang { height: 70pt; }
        .materai { display: inline-block; border: 1px dashed #999; color: #999; font-size: 9pt; padding: 10pt 6pt; margin-top: 6pt; }
        @media (max-width: 800px) { .paper { width: 100%; min-height: 0; margin: 0; padding: 16px; box-shadow: none; } }
        @media print { @page { size: A4; margin: 0; } body { background: #fff; } .no-print { display: none !important; } .paper { margin: 0; box-shadow: none; } }
        @if ($pdf)
        @page { size: A4 portrait; margin: 18mm 20mm; }
        body { background: #fff; } .paper { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
        @endif
    </style>
</head>
<body>
@unless ($pdf)
    <div class="toolbar no-print">
        <span class="judul">{{ $jenis }} {{ $nomor }}</span>
        <a href="{{ route('transaksi-penjualan.show', $t) }}" class="btn">&larr; Kembali</a>
        <button type="button" onclick="window.print()" class="btn">Cetak</button>
        <a href="{{ route('dokumen.unduh', [$t, strtolower($jenis)]) }}" class="btn btn-utama">Unduh PDF</a>
    </div>
    @if ($kurang->isNotEmpty())
        <div class="peringatan no-print">Lengkapi di Pengaturan Proyek agar dokumen tidak berisi titik-titik: {{ $kurang->implode(', ') }}.</div>
    @endif
@endunless

<div class="paper">
    <h1>{{ $judul }}</h1>
    <p class="nomor">Nomor: {{ $nomor }}</p>

    <p>Pada hari ini, {{ tanggal($tanggal, 'l') }}, tanggal {{ tanggal($tanggal, 'j F Y') }}, bertempat di {{ $kota ?: $kosong }}, yang bertanda tangan di bawah ini:</p>

    <table class="data">
        <tr><td>1. Nama</td><td>:</td><td><b>{{ $pengelola ?: $kosong }}</b></td></tr>
        <tr><td>&nbsp;&nbsp;&nbsp; Selaku</td><td>:</td><td>Pengelola / Penjual kavling proyek {{ $proyek }}</td></tr>
    </table>
    <p>selanjutnya disebut <b>PIHAK PERTAMA</b>.</p>

    <table class="data">
        <tr><td>2. Nama</td><td>:</td><td><b>{{ mb_strtoupper($k->nama_lengkap) }}</b></td></tr>
        <tr><td>&nbsp;&nbsp;&nbsp; NIK</td><td>:</td><td>{{ $k->nik }}</td></tr>
        <tr><td>&nbsp;&nbsp;&nbsp; Alamat</td><td>:</td><td>{{ $k->alamat }}</td></tr>
        <tr><td>&nbsp;&nbsp;&nbsp; No. HP</td><td>:</td><td>{{ $k->no_hp }}</td></tr>
        @if ($k->pekerjaan)<tr><td>&nbsp;&nbsp;&nbsp; Pekerjaan</td><td>:</td><td>{{ $k->pekerjaan }}</td></tr>@endif
    </table>
    <p>selanjutnya disebut <b>PIHAK KEDUA</b>.</p>

    <p>Kedua belah pihak sepakat {{ $ppjb ? 'mengikatkan diri dalam perjanjian pengikatan jual beli' : 'atas pemesanan kavling' }} dengan ketentuan sebagai berikut:</p>

    <h2>Pasal 1<br>Objek</h2>
    <table class="data">
        <tr><td>Proyek</td><td>:</td><td>{{ $proyek }}{{ $alamatProyek ? ', ' . $alamatProyek : '' }}</td></tr>
        <tr><td>Kode Kavling</td><td>:</td><td><b>{{ $t->kavling->kode_kavling }}</b> ({{ $t->kavling->tipe }})</td></tr>
        <tr><td>Ukuran</td><td>:</td><td>{{ $t->kavling->ukuran ?: '-' }}</td></tr>
        <tr><td>Luas</td><td>:</td><td>{{ angka($t->luas) }} m²</td></tr>
        <tr><td>Status Legal Lahan</td><td>:</td><td>{{ $statusLegal ?: '-' }}</td></tr>
    </table>

    <h2>Pasal 2<br>Harga</h2>
    <p>Harga kavling disepakati sebesar <b>{{ rupiah($t->nilai_jual) }}</b> ({{ $terbilangHarga }}), yaitu {{ angka($t->luas) }} m² × {{ rupiah($t->harga_per_m2) }} per m². Harga ini tetap dan tidak berubah meskipun tahap harga proyek naik.</p>
    <p>Biaya reservasi {{ rupiah($t->biaya_reservasi) }} dan booking fee {{ rupiah($t->biaya_booking) }} berada di luar harga kavling dan tidak mengurangi harga tersebut.</p>

    <h2>Pasal 3<br>Cara Pembayaran</h2>
    <table class="data">
        <tr><td>Metode</td><td>:</td><td>{{ $t->isAngsuran() ? 'Angsuran ' . $t->tenor . ' bulan' : 'Cash' }}</td></tr>
        <tr><td>Down Payment (DP)</td><td>:</td><td>{{ rupiah($t->nominal_dp) }}</td></tr>
        @if ($t->isAngsuran())
            <tr><td>Sisa yang diangsur</td><td>:</td><td>{{ rupiah($t->pokokDiangsur()) }}</td></tr>
            <tr><td>Angsuran per bulan</td><td>:</td><td>{{ rupiah($cicilan) }}{{ $jadwal->last() && (float) $jadwal->last()->nominal !== (float) $cicilan ? ' (cicilan terakhir ' . rupiah($jadwal->last()->nominal) . ')' : '' }}</td></tr>
            <tr><td>Jatuh tempo</td><td>:</td><td>setiap tanggal {{ $t->tanggal->day }}, mulai {{ tanggal($jadwal->first()?->jatuh_tempo, 'j F Y') }}</td></tr>
        @endif
        @if ($ppjb)
            <tr><td>Telah dibayar (harga kavling)</td><td>:</td><td>{{ rupiah($dibayar) }}</td></tr>
            <tr><td>Sisa pembayaran</td><td>:</td><td><b>{{ rupiah($sisa) }}</b></td></tr>
        @endif
    </table>

    @if ($ppjb && $jadwal->isNotEmpty())
        <p style="margin-top:6pt">Jadwal angsuran:</p>
        <table class="grid">
            <thead><tr><th class="c">Ke</th><th>Jatuh Tempo</th><th class="r">Nominal</th></tr></thead>
            <tbody>@foreach ($jadwal as $j)<tr><td class="c">{{ $j->ke }}</td><td>{{ tanggal($j->jatuh_tempo, 'j F Y') }}</td><td class="r">{{ rupiah($j->nominal) }}</td></tr>@endforeach</tbody>
        </table>
    @endif

    <h2>Pasal 4<br>Pembatalan</h2>
    <ol>
        <li>Apabila PIHAK KEDUA membatalkan, biaya reservasi dikembalikan sebesar {{ rupiah(\App\Services\Pengaturan::get('refund_reservasi')) }} dan booking fee dikembalikan sebesar {{ rupiah(\App\Services\Pengaturan::get('refund_booking')) }}.</li>
        <li>Pengembalian DP: {{ $refundDp === null ? $kosong : persen($refundDp, false, 2) . ' dari DP yang telah dibayar' }}. Pengembalian angsuran: {{ $refundAng === null ? $kosong : persen($refundAng, false, 2) . ' dari angsuran yang telah dibayar' }}.</li>
        <li>Setelah pembatalan, kavling kembali menjadi milik PIHAK PERTAMA dan dapat dijual kepada pihak lain.</li>
    </ol>

    @if ($ppjb)
        <h2>Pasal 5<br>Legalitas dan Akta Jual Beli</h2>
        <ol>
            <li>PIHAK PERTAMA menyatakan status lahan saat ini adalah {{ $statusLegal ?: $kosong }} dan akan mengurus proses legalitas sampai dapat dibuatkan Akta Jual Beli (AJB).</li>
            <li>AJB dilaksanakan setelah PIHAK KEDUA melunasi seluruh harga kavling dan dokumen legal telah siap.</li>
        </ol>
        <h2>Pasal 6<br>Lain-lain</h2>
    @else
        <h2>Pasal 5<br>Lain-lain</h2>
    @endif
    <ol>
        <li>Hal-hal yang belum diatur akan dimusyawarahkan oleh kedua belah pihak.</li>
        <li>{{ $ppjb ? 'Perjanjian' : 'Surat pemesanan' }} ini dibuat rangkap dua, masing-masing mempunyai kekuatan hukum yang sama.</li>
    </ol>

    <p style="margin-top:14pt">{{ $kota ?: $kosong }}, {{ tanggal($tanggal, 'j F Y') }}</p>
    <table class="ttd">
        <tr>
            <td><b>PIHAK PERTAMA</b><div class="ruang"></div>( {{ $pengelola ?: $kosong }} )</td>
            <td><b>PIHAK KEDUA</b><div class="ruang">@if ($ppjb)<span class="materai">Materai</span>@endif</div>( {{ mb_strtoupper($k->nama_lengkap) }} )</td>
        </tr>
    </table>
    @if ($t->agen)
        <div class="c" style="margin-top:14pt">Mengetahui,<br>Agen Marketing<div style="height:50pt"></div>( {{ $t->agen->nama_agen }} )</div>
    @endif
</div>
</body>
</html>
