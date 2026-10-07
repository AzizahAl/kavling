<?php

namespace Database\Seeders;

use App\Models\SkemaHarga;
use Illuminate\Database\Seeder;

class SkemaHargaSeeder extends Seeder
{
    /** Tahap 1–5: Rp500.000 → Rp700.000/m², naik Rp50.000 tiap 3 unit terjual (baseline Excel). */
    public function run(): void
    {
        if (SkemaHarga::exists()) {
            return;
        }

        foreach ([[0, 2, 500000], [3, 5, 550000], [6, 8, 600000], [9, 11, 650000], [12, 14, 700000]] as $i => [$mulai, $sampai, $harga]) {
            SkemaHarga::create([
                'nama_tahap' => 'Tahap ' . ($i + 1), 'unit_mulai' => $mulai, 'unit_sampai' => $sampai, 'harga_per_m2' => $harga,
            ]);
        }
    }
}
