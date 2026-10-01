<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riwayat perubahan status pembayaran, dokumen, kavling, dan pembatalan.
 * Hanya ditambah (tidak ada tombol ubah/hapus di antarmuka).
 */
class StatusRiwayat extends Model
{
    public const UPDATED_AT = null;
    public const CREATED_AT = 'waktu';

    public const JENIS = ['pembayaran' => 'Pembayaran', 'dokumen' => 'Dokumen', 'kavling' => 'Kavling', 'pembatalan' => 'Pembatalan'];

    protected $fillable = ['transaksi_id', 'kavling_id', 'jenis', 'item', 'dari', 'ke', 'catatan', 'user_id'];

    protected $casts = ['waktu' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function transaksi(): BelongsTo { return $this->belongsTo(TransaksiPenjualan::class, 'transaksi_id'); }
    public function kavling(): BelongsTo { return $this->belongsTo(Kavling::class); }

    /** Label status sesuai jenisnya (status pembayaran & kavling memakai kata yang berbeda). */
    public static function labelStatus(string $jenis, ?string $status, ?string $item = null): string
    {
        if ($status === null) {
            return '—';
        }

        return match ($jenis) {
            'pembayaran' => TransaksiPenjualan::LABEL_STATUS[$status] ?? $status,
            'kavling'    => Kavling::LABEL_STATUS[$status] ?? $status,
            default      => \App\Support\Status::label($status),
        };
    }

    public function getJudulAttribute(): string
    {
        return match ($this->jenis) {
            'pembayaran' => 'Status pembayaran',
            'kavling'    => 'Status kavling',
            'dokumen'    => 'Dokumen ' . (ChecklistLegal::ITEM[$this->item] ?? strtoupper((string) $this->item)),
            'pembatalan' => 'Pembatalan',
            default      => $this->jenis,
        };
    }
}
