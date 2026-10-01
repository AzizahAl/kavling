<?php

namespace App\Services;

use App\Models\StatusRiwayat;

/** Pencatat riwayat perubahan status (pembayaran, dokumen, kavling, pembatalan). Hanya menambah. */
class RiwayatService
{
    public function catat(string $jenis, ?int $transaksiId, ?int $kavlingId, ?string $dari, ?string $ke, ?string $catatan = null, ?string $item = null): void
    {
        if ($jenis !== 'pembatalan' && $dari === $ke) {
            return;
        }

        StatusRiwayat::create([
            'transaksi_id' => $transaksiId,
            'kavling_id'   => $kavlingId,
            'jenis'        => $jenis,
            'item'         => $item,
            'dari'         => $dari,
            'ke'           => $ke,
            'catatan'      => $catatan,
            'user_id'      => auth()->id(),
        ]);
    }
}
