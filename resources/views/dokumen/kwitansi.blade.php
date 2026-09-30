@php $pdf = $pdf ?? false; @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kwitansi {{ $k['noKwitansi'] }} - {{ $k['nama'] }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #e5e7eb;
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #000;
        }

        /* ===== Toolbar (hanya tampil di browser) ===== */
        .toolbar {
            position: sticky; top: 0; z-index: 10;
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
            padding: 10px 16px;
            background: #1a5a3f;
            font-family: Arial, Helvetica, sans-serif;
        }
        .toolbar .judul { color: #fff; font-size: 14px; font-weight: 600; margin-right: auto; }
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,.35);
            background: transparent; color: #fff; font-size: 13px; font-weight: 600;
            text-decoration: none; cursor: pointer;
        }
        .btn:hover { background: rgba(255,255,255,.1); }
        .btn-utama { background: #fff; color: #1a5a3f; border-color: #fff; }
        .btn-utama:hover { background: #eef8f3; }

        /* ===== Kertas A4 ===== */
        .paper {
            width: 210mm;
            min-height: 148mm;
            margin: 20px auto;
            padding: 15.9mm 17.7mm;
            background: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,.2);
        }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td { padding: 2pt 4pt; vertical-align: top; word-wrap: break-word; }
        .b { font-weight: bold; }
        .c { text-align: center; }
        .r { text-align: right; }

        .pita { background: #F4CCCC; text-align: center; font-weight: bold; padding: 3.5pt 4pt; }
        .garis { border-bottom: 1px solid #000; margin: 14pt 0 12pt; height: 1px; }
        .abu { background: #D9D9D9; }

        .item td { padding: 2pt 4pt; }
        .item .head td { border-top: 1px solid #000; border-bottom: 1px solid #000; font-weight: bold; }
        .item .total td.t { border-top: .75pt solid #000; border-bottom: 3pt solid #000; }

        .ringkas td { vertical-align: top; }

        @media (max-width: 800px) {
            .paper { width: 100%; margin: 0; box-shadow: none; padding: 16px; }
        }

        @media print {
            @page { size: A4; margin: 0; }
            body { background: #fff; }
            .no-print { display: none !important; }
            .paper { margin: 0; box-shadow: none; width: 210mm; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }

        @if($pdf)
        /* ===== Khusus mode PDF (Dompdf) ===== */
        @page { size: A4 portrait; margin: 15mm 17mm; }
        body { background: #fff; }
        .paper { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
        @endif
    </style>
</head>
<body>

    {{-- Toolbar (disembunyikan saat mode PDF) --}}
    @unless($pdf)
        <div class="toolbar no-print">
            <span class="judul">Kwitansi {{ $k['noKwitansi'] }}</span>
            <a href="{{ $k['kembali'] }}" class="btn">&larr; Kembali</a>
            <button type="button" onclick="window.print()" class="btn">Cetak</button>
            <a href="{{ route('pembayaran.kwitansi.unduh', $p) }}" class="btn btn-utama">Unduh PDF</a>
        </div>
    @endunless

    @php
        // lebar kolom dalam persen (basis 9900 twips = lebar area cetak)
        $w = fn ($t) => round($t / 9900 * 100, 3) . '%';

        $wLabel = $k['angsuran'] ? 2300 : 1900;
        $wNilai = 5000 - $wLabel;
    @endphp

    <div class="paper">

        {{-- Kop --}}
        <table>
            <colgroup>
                <col style="width: {{ $w(5300) }}"><col style="width: {{ $w(1500) }}"><col style="width: {{ $w(3100) }}">
            </colgroup>
            <tr>
                <td class="b" style="width: {{ $w(5300) }}">{{ $k['namaProyek'] }}</td>
                <td style="width: {{ $w(1500) }}">Tanggal</td>
                <td style="width: {{ $w(3100) }}">{{ $k['tanggal'] }}</td>
            </tr>
            <tr>
                <td>{{ $k['alamatProyek'] }}</td>
                <td>No Kwitansi</td>
                <td>{{ $k['noKwitansi'] }}</td>
            </tr>
        </table>

        <div style="height: 12pt"></div>

        {{-- Pita judul --}}
        <table>
            <tr><td class="pita">KWITANSI PEMBAYARAN</td></tr>
        </table>

        <div class="garis"></div>

        {{-- Telah terima dari --}}
        <table>
            <colgroup>
                <col style="width: {{ $w(1900) }}"><col style="width: {{ $w(300) }}"><col style="width: {{ $w(7700) }}">
            </colgroup>
            <tr>
                <td style="width: {{ $w(1900) }}">Telah terima dari</td>
                <td style="width: {{ $w(300) }}">:</td>
                <td style="width: {{ $w(7700) }}">{{ $k['nama'] }}</td>
            </tr>
            <tr>
                <td>Sejumlah uang</td>
                <td>:</td>
                <td class="abu">{{ $k['terbilang'] }}</td>
            </tr>
            <tr>
                <td>Metode</td>
                <td>:</td>
                <td>{{ $k['metode'] }}</td>
            </tr>
        </table>

        <div style="height: 14pt"></div>

        {{-- Tabel keterangan --}}
        <table class="item">
            <colgroup>
                <col style="width: {{ $w(600) }}"><col style="width: {{ $w(5500) }}">
                <col style="width: {{ $w(1900) }}"><col style="width: {{ $w(1900) }}">
            </colgroup>
            <tr class="head">
                <td style="width: {{ $w(600) }}">NO.</td>
                <td style="width: {{ $w(5500) }}">KETERANGAN</td>
                <td class="c" style="width: {{ $w(1900) }}">TGL MASUK</td>
                <td class="r" style="width: {{ $w(1900) }}">JUMLAH</td>
            </tr>
            <tr>
                <td>1</td>
                <td>{{ $k['keterangan'] }}</td>
                <td class="c">{{ $k['tglMasuk'] }}</td>
                <td class="r">{{ $k['nominal'] }}</td>
            </tr>
            <tr class="total">
                <td></td>
                <td></td>
                <td class="t c b">TOTAL :</td>
                <td class="t r">{{ $k['nominal'] }}</td>
            </tr>
        </table>

        <div style="height: 16pt"></div>

        {{-- Ringkasan tagihan (kiri) + tanda tangan (kanan) --}}
        <table class="ringkas">
            <colgroup>
                <col style="width: {{ $w($wLabel) }}"><col style="width: {{ $w(300) }}">
                <col style="width: {{ $w($wNilai) }}"><col style="width: {{ $w(400) }}">
                <col style="width: {{ $w(4200) }}">
            </colgroup>
            @foreach($k['baris'] as $i => [$label, $nilai, $tebal, $status])
                @php
                    $tinggi = ($i >= 2 && $i < $k['terakhir']) ? $k['tinggiTengah'] : 380;
                    $tPt    = $tinggi / 20;
                @endphp
                <tr style="height: {{ $tPt }}pt">
                    <td class="{{ $tebal ? 'b' : '' }}" style="height: {{ $tPt }}pt; @if($i === 0) width: {{ $w($wLabel) }} @endif">{{ $label }}</td>
                    <td style="@if($i === 0) width: {{ $w(300) }} @endif">:</td>
                    <td class="{{ $tebal ? 'b' : '' }}"
                        style="@if($i === 0) width: {{ $w($wNilai) }}; @endif @if($status) background: {{ $k['lunas'] ? '#D5E8D4' : '#F4CCCC' }} @endif">{{ $nilai }}</td>
                    <td style="@if($i === 0) width: {{ $w(400) }} @endif"></td>
                    <td class="c" style="@if($i === 0) width: {{ $w(4200) }} @endif">{{ $k['kanan'][$i] ?? '' }}</td>
                </tr>
            @endforeach
        </table>

    </div>

</body>
</html>