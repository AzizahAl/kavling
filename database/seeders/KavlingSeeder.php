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
            $data[] = ['A', $n, 'Prima', null];
        }
        foreach (range(1, 6) as $n) {
            $data[] = ['B', $n, 'Standard', null];
        }
        $data[] = ['B', 7, 'Standard Hook', 'Hook – luas menunggu angka final'];

        foreach ($data as [$blok, $n, $tipe, $catatan]) {
            Kavling::firstOrCreate(
                ['kode_kavling' => sprintf('TR-%s%02d', $blok, $n)],
                [
                    'blok' => $blok, 'no' => $blok . $n, 'tipe' => $tipe, 'ukuran' => Kavling::TIPE[$tipe]['ukuran'],
                    'luas' => Kavling::TIPE[$tipe]['luas'], 'status' => 'tersedia', 'catatan' => $catatan,
                ]
            );
        }

        $harga->sinkronHargaKavling();
    }
}
