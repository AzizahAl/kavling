<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KomisiPembayaran extends Model
{
    protected $fillable = ['agen_id', 'transaksi_id', 'tanggal', 'nominal', 'metode', 'catatan', 'dibuat_oleh'];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function agen(): BelongsTo { return $this->belongsTo(Agen::class); }
    public function transaksi(): BelongsTo { return $this->belongsTo(TransaksiPenjualan::class, 'transaksi_id'); }
    public function kas(): HasOne { return $this->hasOne(KasTransaksi::class, 'komisi_pembayaran_id'); }
}
