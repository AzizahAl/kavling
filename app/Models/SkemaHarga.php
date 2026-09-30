<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel tahap harga. Isinya dibentuk otomatis dari Pengaturan Proyek
 * (harga awal, kenaikan, unit per kenaikan, jumlah tahap) oleh HargaService.
 */
class SkemaHarga extends Model
{
    protected $fillable = ['nama_tahap', 'unit_mulai', 'unit_sampai', 'harga_per_m2'];

    protected $casts = ['harga_per_m2' => 'decimal:2'];

    public static function aktif(): ?self
    {
        return app(\App\Services\HargaService::class)->tahapAktif();
    }
}
