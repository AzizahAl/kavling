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
}