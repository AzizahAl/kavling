<?php

namespace Database\Seeders;

use App\Services\HargaService;
use Illuminate\Database\Seeder;

class SkemaHargaSeeder extends Seeder
{
    /** Tahap 1–5: Rp500.000 → Rp700.000/m², naik Rp50.000 tiap 3 unit terjual (dari Pengaturan). */
    public function run(HargaService $harga): void
    {
        $harga->sinkronSkema();
    }
}
