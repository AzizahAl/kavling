<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kavling extends Model
{
    use HasFactory;

    protected $table = 'kavlings';

    protected $fillable = [
        'kode_kavling',
        'blok',
        'no',
        'tipe',
        'ukuran',
        'luas',
        'harga_per_m2',
        'harga_jual',
        'status',
    ];

    public function transaksiPenjualans()
    {
        return $this->hasMany(TransaksiPenjualan::class);
    }
}