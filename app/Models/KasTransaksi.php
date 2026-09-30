<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KasTransaksi extends Model
{
    protected $table = 'kas_transaksis';

    protected $fillable = [
        'tanggal', 'kode', 'kategori', 'jenis', 'asal', 'pembayaran_id', 'transaksi_id',
        'uraian', 'nominal', 'sumber', 'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(Pembayaran::class);
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'transaksi_id');
    }

    /** Baris otomatis (dari pembayaran/refund) hanya boleh diubah lewat sumbernya. */
    public function isOtomatis(): bool
    {
        return $this->asal !== 'manual';
    }
}
