{{--
    Kerangka cetak formulir resmi (tampilan layar, cetak browser, dan PDF dompdf).
    $halaman: daftar [view, gaya] — view di formulir/halaman. Semua dokumen dicetak seragam Times New Roman 12pt, paragraf rata kiri-kanan.
    $d: data isian (kosong = formulir kosong untuk diisi tangan).
--}}
@php
    $pdf = $pdf ?? false;
    $d = $d ?? [];
    $isi = $isi ?? false;
    $gambar = fn (string $f) => $pdf ? public_path('img/dokumen/' . $f) : asset('img/dokumen/' . $f);
    $ttd = fn ($nama) => filled($nama) ? $nama : null;
    $margin = ($margin ?? null) ?: ['a4' => '18mm 22mm', 'letter' => '15mm 17mm'][$kertas];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judul }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #e5e7eb; color: #000; }

        /* ---------- Toolbar (layar saja) ---------- */
        .toolbar { position: sticky; top: 0; z-index: 10; display: flex; gap: 8px; flex-wrap: wrap; align-items: center; padding: 10px 16px; background: #1a5a3f; font-family: Arial, sans-serif; }
        .toolbar .judul { color: #fff; font-size: 14px; font-weight: 600; margin-right: auto; }
        .btn { display: inline-flex; padding: 8px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,.35); background: transparent; color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; }
        .btn:hover { background: rgba(255,255,255,.1); }
        .btn-utama { background: #fff; color: #1a5a3f; border-color: #fff; }
        .peringatan { max-width: 210mm; margin: 12px auto 0; padding: 10px 14px; background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; font-family: Arial, sans-serif; font-size: 13px; color: #92400e; }

        /* ---------- Kertas ---------- */
        .paper { margin: 20px auto; padding: {{ $margin }}; background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.2); }
        .kertas-a4 .paper { width: 210mm; min-height: 297mm; }
        .kertas-letter .paper { width: 215.9mm; min-height: 279.4mm; }
        .pisah { page-break-before: always; }

        /* ---------- Gaya huruf per dokumen asli ---------- */
        .gaya-calibri { font-family: Calibri, Carlito, Arial, Helvetica, sans-serif; font-size: 11pt; line-height: 1.3; }
        .gaya-times { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.45; }
        .gaya-arial { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; line-height: 1.35; }
        .sym { font-family: 'DejaVu Sans', 'Segoe UI Symbol', sans-serif; }

        p { margin: 0 0 6pt; }
        .j { text-align: justify; }
        .c { text-align: center; }
        .r { text-align: right; }
        .b { font-weight: bold; }
        .u { text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }
        ol, ul { margin: 0 0 6pt; padding-left: 20pt; }
        li { margin-bottom: 2pt; }
        .gaya-times li { text-align: justify; }

        /* Isian titik-titik */
        .isian { display: inline-block; min-width: 25mm; border-bottom: 1px dotted #000; padding: 0 3pt; }
        .isian-penuh { display: block; width: 100%; min-width: 0; }
        .isian-teks { display: inline; min-width: 0; padding: 0 8pt 0 3pt; }

        /* Baris "Label : isian" */
        .data td { padding: 2pt 0; }
        .data .lbl { white-space: nowrap; padding-right: 4pt; }
        .data .sep { width: 10pt; text-align: center; }

        /* Tabel bergaris */
        .grid th, .grid td { border: 1px solid #000; padding: 3pt 6pt; }
        .grid th { font-weight: bold; text-align: center; vertical-align: middle; }
        .gaya-arial .grid th { background: #d9ead3; }
        .grid .kosong td { height: 18pt; }

        /* Kotak centang */
        .kotak { display: inline-block; width: 9pt; height: 9pt; border: 1px solid #000; vertical-align: -1pt; margin-right: 6pt; }
        .centang { list-style: none; padding-left: 0; }
        .centang li { margin-bottom: 4pt; }

        /* Tanda tangan */
        .ttd td { text-align: center; vertical-align: top; }
        .ttd .ruang { height: 62pt; }
        .ttd-nama { display: inline-block; min-width: 34mm; padding: 0 2pt; }
        .ttd-nama.kosong { border-bottom: 1px dotted #000; }

        /* Garis tulis bebas */
        .garis-tulis { border-bottom: 1px solid #000; height: 20pt; }

        @media (max-width: 860px) { .paper { width: 100% !important; min-height: 0 !important; margin: 0; padding: 16px; box-shadow: none; } }
        @media print {
            @page { size: {{ $kertas === 'a4' ? 'A4' : 'letter' }}; margin: 0; }
            body { background: #fff; }
            .no-print { display: none !important; }
            .paper { margin: 0; box-shadow: none; min-height: 0 !important; page-break-after: always; }
            .paper:last-child { page-break-after: auto; }
        }
        @if ($pdf)
        @page { size: {{ $kertas === 'a4' ? 'A4' : 'letter' }} portrait; margin: {{ $margin }}; }
        body { background: #fff; }
        .paper { width: auto !important; min-height: 0 !important; margin: 0; padding: 0; box-shadow: none; page-break-after: always; }
        .paper.akhir { page-break-after: auto; }
        @endif

        /* ---------- Seragam untuk semua dokumen cetak: Times New Roman 12pt, paragraf rata kiri-kanan ---------- */
        .paper, .paper * { font-family: 'Times New Roman', Times, serif !important; font-size: 12pt !important; }
        .paper .sym { font-family: 'DejaVu Sans', 'Segoe UI Symbol', sans-serif !important; }
        .paper { line-height: 1.45; }
        .paper p, .paper li { text-align: justify; }
        .paper td p, .paper th p { text-align: inherit; }
        .paper td li, .paper th li { text-align: justify; }
        .paper .c, .paper p.c, .paper .tk-judul, .paper .ppjb-judul, .paper .ppjb-sub, .paper .ppjb-bab, .paper .ppjb-pasal, .paper .lamp-kepala .judul { text-align: center; }
        .paper .r, .paper p.r { text-align: right; }
    </style>
</head>
<body class="kertas-{{ $kertas }}">
@unless ($pdf)
    <div class="toolbar no-print">
        <span class="judul">{{ $judul }}</span>
        <a href="{{ $kembali }}" class="btn">&larr; Kembali</a>
        <button type="button" onclick="window.print()" class="btn">Cetak</button>
        <a href="{{ $unduh }}" class="btn btn-utama">Unduh PDF</a>
    </div>
    @foreach ($peringatan ?? [] as $pesan)
        <div class="peringatan no-print">{{ $pesan }}</div>
    @endforeach
@endunless

@foreach ($halaman as [$view, $gaya])
    <div class="paper gaya-{{ $gaya }} {{ $loop->last ? 'akhir' : '' }}">
        @include('formulir.halaman.' . $view)
    </div>
@endforeach
</body>
</html>
