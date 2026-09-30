<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pembayaran extends Model
{
    public const JENIS = [
        'reservasi' => 'Reservasi',
        'booking'   => 'Booking Fee',
        'dp'        => 'Down Payment (DP)',
        'angsuran'  => 'Angsuran',
        'pelunasan' => 'Pelunasan',
    ];

    public const METODE = ['transfer' => 'Transfer', 'tunai' => 'Tunai'];

    protected $fillable = [
        'kode', 'transaksi_id', 'tanggal', 'jenis', 'nominal', 'metode', 'no_bukti', 'catatan', 'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'transaksi_id');
    }

    public function kas(): HasOne
    {
        return $this->hasOne(KasTransaksi::class, 'pembayaran_id');
    }

    public function getLabelJenisAttribute(): string
    {
        return self::JENIS[$this->jenis] ?? $this->jenis;
    }
}
