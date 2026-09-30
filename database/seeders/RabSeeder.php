<?php

namespace Database\Seeders;

use App\Models\Rab;
use Illuminate\Database\Seeder;

class RabSeeder extends Seeder
{
    /** Sheet RAB_MASTER. Anggaran kosong di Excel dibiarkan kosong (Belum Dianggarkan). */
    public function run(): void
    {
        $data = [
            ['Tanah', 'Pembayaran/pembebasan tanah', null, 0],
            ['Legalitas', 'AJB / TORA / administrasi', null, 0],
            ['Pematangan Lahan', 'Pembersihan & pematangan', null, 0],
            ['Infrastruktur', 'Jalan dalam 5,3 m', null, 0],
            ['Infrastruktur', 'Drainase', null, 0],
            ['Fasilitas', 'Gerbang / signage', null, 0],
            ['Marketing', 'Banner sponsor Rancabuaya & Sukarame', 500000, 500000],
            ['Operasional', 'Operasional proyek', null, 0],
            ['Cadangan', 'Dana cadangan', null, 0],
        ];

        foreach ($data as [$kategori, $uraian, $anggaran, $realisasi]) {
            Rab::firstOrCreate(['kategori' => $kategori, 'uraian' => $uraian], [
                'anggaran'         => $anggaran,
                'realisasi'        => $realisasi,
                'status_realisasi' => $realisasi > 0 ? 'sudah_direalisasikan' : 'belum_direalisasikan',
            ]);
        }
    }
}
