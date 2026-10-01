<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Pembayaran kewajiban tanah ke pemilik lahan. Setiap baris juga tercatat sebagai kas keluar (pos Tanah). */
class PembayaranTanah extends Model
{
    protected $fillable = ['tanggal', 'nominal', 'keterangan', 'metode', 'dibuat_oleh'];

    protected $casts = ['tanggal' => 'date', 'nominal' => 'decimal:2'];

    public function kas(): HasOne { return $this->hasOne(KasTransaksi::class, 'pembayaran_tanah_id'); }
    public function pembuat(): BelongsTo { return $this->belongsTo(User::class, 'dibuat_oleh'); }
}
