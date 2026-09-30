<?php

namespace App\Support;

/** Angka → kalimat Bahasa Indonesia. 250000 => "Dua ratus lima puluh ribu rupiah" */
class Terbilang
{
    public static function rupiah(int $n): string
    {
        return $n === 0 ? 'Nol rupiah' : ucfirst(trim(self::angka($n))) . ' rupiah';
    }

    public static function angka(int $n): string
    {
        $dasar = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        $lanjut = fn (int $sisa) => $sisa ? ' ' . self::angka($sisa) : '';

        return match (true) {
            $n < 12             => $dasar[$n],
            $n < 20             => self::angka($n - 10) . ' belas',
            $n < 100            => self::angka(intdiv($n, 10)) . ' puluh' . $lanjut($n % 10),
            $n < 200            => 'seratus' . $lanjut($n - 100),
            $n < 1000           => self::angka(intdiv($n, 100)) . ' ratus' . $lanjut($n % 100),
            $n < 2000           => 'seribu' . $lanjut($n - 1000),
            $n < 1_000_000      => self::angka(intdiv($n, 1000)) . ' ribu' . $lanjut($n % 1000),
            $n < 1_000_000_000  => self::angka(intdiv($n, 1_000_000)) . ' juta' . $lanjut($n % 1_000_000),
            $n < 1_000_000_000_000 => self::angka(intdiv($n, 1_000_000_000)) . ' miliar' . $lanjut($n % 1_000_000_000),
            default             => self::angka(intdiv($n, 1_000_000_000_000)) . ' triliun' . $lanjut($n % 1_000_000_000_000),
        };
    }
}
