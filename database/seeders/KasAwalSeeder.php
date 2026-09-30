<?php

namespace Database\Seeders;

use App\Models\KasTransaksi;
use Illuminate\Database\Seeder;

class KasAwalSeeder extends Seeder
{
    /** Sheet KAS_PROYEK baris pertama. */
    public function run(): void
    {
        KasTransaksi::firstOrCreate(['kode' => 'EXP-2026-0001'], [
            'tanggal'  => '2026-07-24',
            'kategori' => 'Marketing',
            'jenis'    => 'keluar',
            'asal'     => 'manual',
            'uraian'   => 'Banner sponsor Rancabuaya & Sukarame',
            'nominal'  => 500000,
            'catatan'  => 'Banner Rp300rb + sponsor Rancabuaya Rp100rb + Sukarame Rp100rb',
        ]);
    }
}
