<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistLegal extends Model
{
    public const ITEM = [
        'reservasi' => 'Reservasi',
        'spk'       => 'Booking / SPK',
        'ppjb'      => 'PPJB',
        'ajb'       => 'AJB / Legal',
    ];

    protected $fillable = [
        'transaksi_id',
        'reservasi_status', 'reservasi_tanggal', 'spk_status', 'spk_tanggal',
        'ppjb_status', 'ppjb_tanggal', 'ajb_status', 'ajb_tanggal',
        'nomor_spk', 'nomor_ppjb', 'catatan',
    ];

    protected $casts = [
        'reservasi_tanggal' => 'date',
        'spk_tanggal'       => 'date',
        'ppjb_tanggal'      => 'date',
        'ajb_tanggal'       => 'date',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'transaksi_id');
    }

    public function ppjbDitandatangani(): bool
    {
        return $this->ppjb_status === 'selesai';
    }
}
