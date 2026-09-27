<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agen extends Model
{
    protected $fillable = [
        'kode_agen',
        'nama_agen',
        'no_hp',
        'lead',
        'prospek',
        'closing',
        'nilai_penjualan',
        'komisi_persen',
        'komisi_terhitung',
        'dibayar',
        'sisa_komisi',
    ];
}