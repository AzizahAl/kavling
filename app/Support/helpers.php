<?php

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

if (! function_exists('rupiah')) {
    /** 1500000 => "Rp1.500.000" (negatif => "-Rp1.500.000") */
    function rupiah($nilai, bool $prefix = true): string
    {
        $n = (float) ($nilai ?? 0);
        $teks = number_format(abs(round($n)), 0, ',', '.');

        return ($n < 0 ? '-' : '') . ($prefix ? 'Rp' : '') . $teks;
    }
}

if (! function_exists('rupiah_singkat')) {
    /** 1250000000 => "Rp1,25 M", 53900000 => "Rp53,9 jt" */
    function rupiah_singkat($nilai): string
    {
        $n = (float) ($nilai ?? 0);
        $abs = abs($n);
        $minus = $n < 0 ? '-' : '';

        $fmt = fn ($x) => rtrim(rtrim(number_format($x, 2, ',', '.'), '0'), ',');

        return match (true) {
            $abs >= 1_000_000_000 => $minus . 'Rp' . $fmt($abs / 1_000_000_000) . ' M',
            $abs >= 1_000_000     => $minus . 'Rp' . $fmt($abs / 1_000_000) . ' jt',
            default               => rupiah($n),
        };
    }
}

if (! function_exists('angka')) {
    function angka($nilai, int $desimal = 0): string
    {
        return number_format((float) ($nilai ?? 0), $desimal, ',', '.');
    }
}

if (! function_exists('tanggal')) {
    /** Tanggal Indonesia. Format bawaan "1 Okt 2026"; pakai 'd F Y' untuk "1 Oktober 2026". */
    function tanggal($tgl, string $format = 'j M Y', string $kosong = '—'): string
    {
        if (blank($tgl)) {
            return $kosong;
        }
        $c = $tgl instanceof CarbonInterface ? $tgl : Carbon::parse($tgl);

        return $c->locale('id')->translatedFormat($format);
    }
}

if (! function_exists('persen')) {
    /** 0.25 => "25%", 12.5 (sudah persen) => pakai persen($x, false) */
    function persen($nilai, bool $pecahan = true, int $desimal = 0): string
    {
        $n = (float) ($nilai ?? 0) * ($pecahan ? 100 : 1);

        return rtrim(rtrim(number_format($n, $desimal, ',', '.'), '0'), ',') . '%';
    }
}
