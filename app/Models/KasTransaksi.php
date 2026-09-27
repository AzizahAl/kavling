<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasTransaksi extends Model
{
    protected $table = 'kas_transaksis';

    protected $fillable = [
        'tanggal',
        'kode',
        'kategori',
        'jenis',
        'uraian',
        'nominal',
        'sumber',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];
}