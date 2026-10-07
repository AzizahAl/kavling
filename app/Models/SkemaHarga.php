<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel tahap harga, satu-satunya sumber tahap. Dikelola langsung di halaman Skema Harga;
 * tahap aktif & harga dihitung oleh HargaService.
 */
class SkemaHarga extends Model
{
    protected $fillable = ['nama_tahap', 'unit_mulai', 'unit_sampai', 'harga_per_m2'];

    protected $casts = ['unit_mulai' => 'integer', 'unit_sampai' => 'integer', 'harga_per_m2' => 'decimal:2'];

    public static function aktif(): ?self
    {
        return app(\App\Services\HargaService::class)->tahapAktif();
    }
}
