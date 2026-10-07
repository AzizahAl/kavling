<?php

namespace App\Services;

use App\Models\KasTransaksi;
use App\Models\Rab;

/**
 * Kewajiban tanah kepada pemilik lahan, dibaca dari RAB kategori "Tanah":
 *  - total kewajiban = jumlah anggaran RAB Tanah (kosong bila belum dianggarkan),
 *  - terbayar = pengeluaran Kas Proyek berkategori Tanah (realisasi RAB Tanah yang dicentang),
 *  - tanah lunas hanya bila total sudah ditetapkan DAN sisa kewajiban = 0.
 */
class KewajibanTanahService
{
    public function ringkasan(): array
    {
        $anggaran = Rab::where('kategori', 'Tanah')->whereNotNull('anggaran');
        $total = $anggaran->exists() ? (float) $anggaran->sum('anggaran') : null;
        $terbayar = (float) KasTransaksi::where('jenis', 'keluar')->where('kategori', 'Tanah')->sum('nominal');
        $sisa = $total === null ? null : max(0, $total - $terbayar);

        return [
            'total'    => $total,
            'terbayar' => $terbayar,
            'sisa'     => $sisa,
            'persen'   => $total ? min(100, $terbayar / $total * 100) : 0,
            'lunas'    => $total !== null && $total > 0 && $sisa <= 0,
        ];
    }
}
