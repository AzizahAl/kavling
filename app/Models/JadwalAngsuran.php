<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Jadwal cicilan. Status (lunas/sebagian/terlambat) dihitung AngsuranService dari pembayaran. */
class JadwalAngsuran extends Model
{
    protected $fillable = ['transaksi_id', 'ke', 'jatuh_tempo', 'nominal'];

    protected $casts = [
        'jatuh_tempo' => 'date',
        'nominal'     => 'decimal:2',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'transaksi_id');
    }
}
