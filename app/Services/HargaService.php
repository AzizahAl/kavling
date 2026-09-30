<?php

namespace App\Services;

use App\Models\Kavling;
use App\Models\SkemaHarga;

/**
 * Skema harga bertahap (sesuai rumus Excel SKEMA_HARGA!E4):
 *   harga aktif = harga awal + INT(unit terjual / unit per kenaikan) × kenaikan, maksimal di tahap terakhir.
 * "Terjual" = kavling yang PPJB-nya sudah ditandatangani.
 * Kavling yang sudah bertransaksi memakai harga terkunci di transaksinya, tidak ikut berubah.
 */
class HargaService
{
    public function jumlahTerjual(): int
    {
        return Kavling::where('status', 'terjual')->count();
    }

    /** Daftar tahap hasil hitung dari Pengaturan: [nomor, nama, unit_mulai, unit_sampai, harga]. */
    public function daftarTahap(): array
    {
        $awal    = (int) Pengaturan::get('harga_awal_m2', 0);
        $naik    = (int) Pengaturan::get('kenaikan_harga_m2', 0);
        $perUnit = max(1, (int) Pengaturan::get('unit_per_kenaikan', 1));
        $jumlah  = max(1, (int) Pengaturan::get('jumlah_tahap', 1));
        $total   = (int) Pengaturan::get('jumlah_kavling', 0);

        $tahap = [];
        for ($i = 1; $i <= $jumlah; $i++) {
            $mulai = ($i - 1) * $perUnit;
            $sampai = $i === $jumlah ? max($mulai, $total) : $i * $perUnit - 1;
            $tahap[] = [
                'nomor'       => $i,
                'nama_tahap'  => "Tahap {$i}",
                'unit_mulai'  => $mulai,
                'unit_sampai' => $sampai,
                'harga_per_m2' => $awal + ($i - 1) * $naik,
            ];
        }

        return $tahap;
    }

    public function nomorTahapAktif(?int $terjual = null): int
    {
        $terjual ??= $this->jumlahTerjual();
        $perUnit = max(1, (int) Pengaturan::get('unit_per_kenaikan', 1));
        $jumlah  = max(1, (int) Pengaturan::get('jumlah_tahap', 1));

        return min($jumlah, intdiv($terjual, $perUnit) + 1);
    }

    public function tahapAktif(): ?SkemaHarga
    {
        $tahap = SkemaHarga::orderBy('unit_mulai')->get();

        return $tahap->get($this->nomorTahapAktif() - 1) ?? $tahap->last();
    }

    public function hargaAktif(): int
    {
        return (int) ($this->daftarTahap()[$this->nomorTahapAktif() - 1]['harga_per_m2'] ?? 0);
    }

    /** Samakan isi tabel skema_hargas dengan Pengaturan, lalu perbarui harga kavling tersedia. */
    public function sinkronSkema(): void
    {
        $lama = SkemaHarga::orderBy('unit_mulai')->get()->values();

        foreach ($this->daftarTahap() as $i => $t) {
            $data = collect($t)->except('nomor')->all();
            isset($lama[$i]) ? $lama[$i]->update($data) : SkemaHarga::create($data);
        }

        // Tahap berlebih dihapus (FK di kavling/transaksi otomatis jadi null)
        $lama->slice(count($this->daftarTahap()))->each->delete();

        $this->sinkronHargaKavling();
    }

    /** Kavling yang masih tersedia selalu memakai harga tahap aktif. */
    public function sinkronHargaKavling(): void
    {
        $tahap = $this->tahapAktif();
        $harga = $this->hargaAktif();

        Kavling::where('status', 'tersedia')->get()->each(function (Kavling $k) use ($tahap, $harga) {
            $k->skema_harga_id = $tahap?->id;
            $k->harga_per_m2   = $harga;
            $k->harga_jual     = $k->luas > 0 ? round($k->luas * $harga) : null;
            $k->saveQuietly();
        });
    }
}
