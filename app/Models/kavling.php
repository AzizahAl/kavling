<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kavling extends Model
{
    protected $fillable = [
        'kode_kavling',
        'blok',
        'no',
        'tipe',
        'skema_harga_id',
        'ukuran',
        'luas',
        'harga_per_m2',
        'harga_jual',
        'status',
    ];

    public function tahap(): BelongsTo
    {
        return $this->belongsTo(SkemaHarga::class, 'skema_harga_id');
    }

    // Relasi yang tadi hilang (penyebab error 500)
    public function transaksiPenjualans(): HasMany
    {
        return $this->hasMany(TransaksiPenjualan::class, 'kavling_id');
    }

    // Update semua kavling yang BELUM terjual mengikuti tahap yang sedang berlaku.
    // Kavling yang sudah terjual dikunci di harga saat terjual.
    public static function syncHargaTahap(): void
    {
        $aktif = SkemaHarga::aktif();
        if (!$aktif) {
            return;
        }

        static::where('status', '!=', 'terjual')->get()->each(function ($k) use ($aktif) {
            $k->skema_harga_id = $aktif->id;
            $k->harga_per_m2   = $aktif->harga_per_m2;
            if ($k->luas > 0) {
                $k->harga_jual = round($k->luas * $aktif->harga_per_m2);
            }
            $k->save();
        });
    }
}