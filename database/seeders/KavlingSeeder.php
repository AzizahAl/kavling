<?php

namespace Database\Seeders;

use App\Models\Kavling;
use App\Services\HargaService;
use Illuminate\Database\Seeder;

class KavlingSeeder extends Seeder
{
    /** Sheet MASTER_KAVLING: Prima A1–A7 (7×14), Standard B1–B6 (7×10), B7 hook (luas belum final). */
    public function run(HargaService $harga): void
    {
        $data = [];
        foreach (range(1, 7) as $n) {
            $data[] = ['A', $n, 'Prima', '7 x 14', 98, null];
        }
        foreach (range(1, 6) as $n) {
            $data[] = ['B', $n, 'Standard', '7 x 10', 70, null];
        }
        $data[] = ['B', 7, 'Standard Hook', '7 x ±8.5 x ±8.3 x ±10', null, 'Hook – luas menunggu angka final'];

        foreach ($data as [$blok, $n, $tipe, $ukuran, $luas, $catatan]) {
            Kavling::firstOrCreate(
                ['kode_kavling' => sprintf('TR-%s%02d', $blok, $n)],
                [
                    'blok' => $blok, 'no' => $blok . $n, 'tipe' => $tipe, 'ukuran' => $ukuran,
                    'luas' => $luas, 'status' => 'tersedia', 'catatan' => $catatan,
                ]
            );
        }

        $harga->sinkronHargaKavling();
    }
}
