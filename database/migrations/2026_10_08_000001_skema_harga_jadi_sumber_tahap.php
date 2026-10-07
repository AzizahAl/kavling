<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Tabel skema_hargas menjadi satu-satunya sumber tahap harga (dikelola di halaman Skema Harga).
 *  - Tahap yang sudah ada dibiarkan apa adanya.
 *  - Bila tabel masih kosong, tahap dibentuk sekali dari isian lama di Pengaturan.
 *  - Isian harga lama di Pengaturan (harga awal, kenaikan, unit per kenaikan, jumlah tahap) dihapus.
 */
return new class extends Migration
{
    private const KUNCI = ['harga_awal_m2', 'kenaikan_harga_m2', 'unit_per_kenaikan', 'jumlah_tahap'];

    public function up(): void
    {
        $nilai = DB::table('pengaturans')->whereIn('kunci', [...self::KUNCI, 'jumlah_kavling'])->pluck('nilai', 'kunci');

        if (DB::table('skema_hargas')->doesntExist() && DB::table('pengaturans')->exists()) {
            $awal    = (int) ($nilai['harga_awal_m2'] ?? 500000);
            $naik    = (int) ($nilai['kenaikan_harga_m2'] ?? 50000);
            $perUnit = max(1, (int) ($nilai['unit_per_kenaikan'] ?? 3));
            $jumlah  = max(1, (int) ($nilai['jumlah_tahap'] ?? 5));
            $total   = (int) ($nilai['jumlah_kavling'] ?? 14);

            for ($i = 1; $i <= $jumlah; $i++) {
                $mulai = ($i - 1) * $perUnit;
                DB::table('skema_hargas')->insert([
                    'nama_tahap'   => "Tahap {$i}",
                    'unit_mulai'   => $mulai,
                    'unit_sampai'  => $i === $jumlah ? max($mulai, $total) : $i * $perUnit - 1,
                    'harga_per_m2' => $awal + ($i - 1) * $naik,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        DB::table('pengaturans')->whereIn('kunci', self::KUNCI)->delete();
        Cache::forget('pengaturan.semua');
    }

    public function down(): void
    {
        // Isian lama tidak dipakai lagi; tahap tetap di skema_hargas.
    }
};
