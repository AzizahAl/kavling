<?php

namespace App\Services;

use App\Models\Agen;
use Illuminate\Support\Collection;

/**
 * Angka kinerja agen untuk menu Data Agen & Marketing dan halaman detail agen (semua dihitung, tidak diketik):
 *  - Lead & Prospek dari modul lead,
 *  - Closing & Nilai Penjualan dari transaksi agen yang tidak batal,
 *  - Komisi (nominal per transaksi), Komisi Terhitung, Dibayar & Sisa dari KomisiService.
 */
class AgenService
{
    public function __construct(private KomisiService $komisi, private LeadService $lead) {}

    /** @param Collection|null $totalLead hasil LeadService::totalPerAgen() agar tidak dihitung ulang per agen */
    public function angka(Agen $agen, ?Collection $totalLead = null, ?Collection $rincian = null): array
    {
        $totalLead ??= $this->lead->totalPerAgen();
        $t = $totalLead[$agen->id] ?? null;
        $transaksi = $agen->transaksis()->where('status', '!=', 'batal');

        return [
            'lead'            => (int) ($t->lead ?? 0),
            'prospek'         => (int) ($t->prospek ?? 0),
            'closing'         => (clone $transaksi)->count(),
            'nilai_penjualan' => (float) (clone $transaksi)->sum('nilai_jual'),
        ] + $this->komisi->ringkasan($agen, $rincian);
    }
}
