<?php

namespace Database\Seeders;

use App\Models\Rab;
use Illuminate\Database\Seeder;

class RabSeeder extends Seeder
{
    /** Sheet RAB_MASTER. Anggaran kosong di Excel dibiarkan kosong (Belum Dianggarkan). Realisasi dihitung dari kas keluar. */
    public function run(): void
    {
        $data = [
            ['Tanah', 'tanah', 'Pembayaran/pembebasan tanah', null],
            ['Legalitas', 'legal_infra', 'AJB / TORA / administrasi', null],
            ['Pematangan Lahan', 'legal_infra', 'Pembersihan & pematangan', null],
            ['Infrastruktur', 'legal_infra', 'Jalan dalam 5,3 m', null],
            ['Infrastruktur', 'legal_infra', 'Drainase', null],
            ['Fasilitas', 'legal_infra', 'Gerbang / signage', null],
            ['Marketing', 'marketing', 'Banner sponsor Rancabuaya & Sukarame', 500000],
            ['Operasional', 'operasional', 'Operasional proyek', null],
            ['Cadangan', 'cadangan', 'Dana cadangan', null],
        ];

        foreach ($data as [$kategori, $pos, $uraian, $anggaran]) {
            Rab::firstOrCreate(['kategori' => $kategori, 'uraian' => $uraian], ['pos' => $pos, 'anggaran' => $anggaran]);
        }
    }
}
