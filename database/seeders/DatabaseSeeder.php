<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Data awal sesuai Excel "TR-DMS Master 2026":
 * pengaturan proyek, 5 tahap harga, 14 kavling, RAB & kas awal, dan satu akun admin.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PengaturanSeeder::class,
            SkemaHargaSeeder::class,
            KavlingSeeder::class,
            RabSeeder::class,
            KasAwalSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
