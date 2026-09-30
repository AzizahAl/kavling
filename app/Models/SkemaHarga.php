<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkemaHarga extends Model
{
    protected $fillable = [
        'nama_tahap',
        'unit_mulai',
        'unit_sampai',
        'harga_per_m2',
    ];

    // Tahap yang sedang berlaku berdasarkan jumlah kavling terjual
    public static function aktif(): ?self
    {
        $terjual = Kavling::where('status', 'terjual')->count();

        return self::where('unit_mulai', '<=', $terjual)
                ->orderByDesc('unit_mulai')
                ->first()
            ?? self::orderBy('unit_mulai')->first();
    }
}