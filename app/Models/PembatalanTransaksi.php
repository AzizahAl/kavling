<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Rincian pembatalan per jenis uang: reservasi (penuh), booking (dipotong), DP+angsuran (potongan diisi admin). */
class PembatalanTransaksi extends Model
{
    protected $fillable = [
        'transaksi_id', 'tanggal',
        'reservasi_dibayar', 'reservasi_refund',
        'booking_dibayar', 'booking_potongan', 'booking_refund',
        'pokok_dibayar', 'pokok_potongan', 'pokok_refund',
        'total_refund', 'dasar_ketentuan', 'alasan', 'kedaluwarsa', 'kas_transaksi_id', 'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date', 'kedaluwarsa' => 'boolean',
        'reservasi_dibayar' => 'decimal:2', 'reservasi_refund' => 'decimal:2',
        'booking_dibayar' => 'decimal:2', 'booking_potongan' => 'decimal:2', 'booking_refund' => 'decimal:2',
        'pokok_dibayar' => 'decimal:2', 'pokok_potongan' => 'decimal:2', 'pokok_refund' => 'decimal:2', 'total_refund' => 'decimal:2',
    ];

    public function transaksi(): BelongsTo { return $this->belongsTo(TransaksiPenjualan::class, 'transaksi_id'); }
    public function kas(): BelongsTo { return $this->belongsTo(KasTransaksi::class, 'kas_transaksi_id'); }
    public function pembuat(): BelongsTo { return $this->belongsTo(User::class, 'dibuat_oleh'); }
    public function alokasis(): HasMany { return $this->hasMany(AlokasiKas::class, 'pembatalan_id'); }
}
