<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/** Nomor berurutan per tahun: PREFIX-2026-0001. Panggil di dalam DB::transaction. */
class Penomoran
{
    public static function berikut(string $tabel, string $kolom, string $prefix, ?int $tahun = null, int $digit = 4): string
    {
        $tahun ??= (int) now()->year;
        $awal = "{$prefix}-{$tahun}-";

        $terakhir = DB::table($tabel)
            ->where($kolom, 'like', $awal . '%')
            ->lockForUpdate()
            ->pluck($kolom)
            ->map(fn ($k) => (int) substr($k, strlen($awal)))
            ->max() ?? 0;

        return $awal . str_pad((string) ($terakhir + 1), $digit, '0', STR_PAD_LEFT);
    }
}
