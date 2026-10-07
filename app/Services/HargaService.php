<?php

namespace App\Services;

use App\Models\Kavling;
use App\Models\SkemaHarga;
use Illuminate\Support\Collection;

/**
 * Satu-satunya tempat perhitungan tahap & harga, dipakai Master Kavling, Skema Harga, form transaksi, dan dashboard.
 * Tahap harga = baris tabel skema_hargas (dikelola di halaman Skema Harga).
 * Tahap aktif = tahap yang rentang unitnya memuat jumlah kavling yang sudah bertransaksi
 * (transaksi apa pun yang tidak batal, terjual atau belum); lewat semua rentang → tahap terakhir.
 * Kavling yang sudah bertransaksi memakai harga terkunci di transaksinya, tidak ikut berubah.
 */
class HargaService
{
    /** Jumlah kavling yang memiliki transaksi tidak batal: penentu tahap harga. */
    public function jumlahBertransaksi(): int
    {
        return Kavling::whereHas('transaksiAktif')->count();
    }

    /** @return Collection<int, SkemaHarga> urut dari unit terkecil */
    public function daftarTahap(): Collection
    {
        return SkemaHarga::orderBy('unit_mulai')->orderBy('id')->get()->values();
    }

    /** Posisi tahap aktif di daftarTahap() (mulai 0), null bila belum ada tahap. */
    private function indeksAktif(Collection $tahap, int $jumlah): ?int
    {
        if ($tahap->isEmpty()) {
            return null;
        }
        // Tahap terakhir yang sudah dimulai; menutup juga celah antar rentang & jumlah di atas tahap terakhir
        $indeks = $tahap->filter(fn ($t) => $t->unit_mulai <= $jumlah)->keys()->last();

        return $indeks ?? 0;
    }

    public function tahapAktif(?int $jumlah = null): ?SkemaHarga
    {
        $tahap = $this->daftarTahap();
        $i = $this->indeksAktif($tahap, $jumlah ?? $this->jumlahBertransaksi());

        return $i === null ? null : $tahap[$i];
    }

    /** Nomor urut tahap aktif (1, 2, …); 0 bila belum ada tahap. */
    public function nomorTahapAktif(?int $jumlah = null): int
    {
        $i = $this->indeksAktif($this->daftarTahap(), $jumlah ?? $this->jumlahBertransaksi());

        return $i === null ? 0 : $i + 1;
    }

    public function hargaAktif(): int
    {
        return (int) ($this->tahapAktif()?->harga_per_m2 ?? 0);
    }

    /** Sisa kavling bertransaksi sampai tahap berikutnya aktif; null bila sudah di tahap terakhir. */
    public function menujuNaik(?int $jumlah = null): ?int
    {
        $jumlah ??= $this->jumlahBertransaksi();
        $tahap = $this->daftarTahap();
        $i = $this->indeksAktif($tahap, $jumlah);
        $berikut = $i === null ? null : $tahap->get($i + 1);

        return $berikut ? max(0, $berikut->unit_mulai - $jumlah) : null;
    }

    /** Harga untuk luas tertentu pada tahap aktif. */
    public function hargaJual($luas, ?int $hargaM2 = null): ?float
    {
        $hargaM2 ??= $this->hargaAktif();

        return $luas > 0 ? round((float) $luas * $hargaM2) : null;
    }

    /** Kavling yang masih tersedia selalu memakai harga tahap aktif. */
    public function sinkronHargaKavling(): void
    {
        $tahap = $this->tahapAktif();
        $harga = (int) ($tahap?->harga_per_m2 ?? 0);

        Kavling::where('status', 'tersedia')->get()->each(function (Kavling $k) use ($tahap, $harga) {
            $k->skema_harga_id = $tahap?->id;
            $k->harga_per_m2   = $harga;
            $k->harga_jual     = $this->hargaJual($k->luas, $harga);
            $k->saveQuietly();
        });
    }
}
