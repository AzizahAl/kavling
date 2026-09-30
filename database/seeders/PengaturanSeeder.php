<?php

namespace Database\Seeders;

use App\Models\Pengaturan as PengaturanModel;
use App\Services\Pengaturan;
use Illuminate\Database\Seeder;

class PengaturanSeeder extends Seeder
{
    /** Tulis nilai baseline Excel (sheet PENGATURAN) tanpa menimpa nilai yang sudah diubah admin. */
    public function run(): void
    {
        foreach (Pengaturan::DEFINISI as $kunci => [, , , $bawaan]) {
            PengaturanModel::firstOrCreate(['kunci' => $kunci], ['nilai' => $bawaan]);
        }
        Pengaturan::lupakan();
    }
}
