<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * RAB per periode (bulan). Anggaran, realisasi & tanggal pengeluaran diisi di form; selisih & status dihitung.
 * Baris final dicentang → realisasi tercatat sebagai satu pengeluaran Kas Proyek pada tanggal pengeluaran (kas_transaksi_id), lalu baris dikunci.
 */
class Rab extends Model
{
    protected $table = 'rabs';

    /** Kategori RAB dan pos alokasi kas yang menanggungnya. */
    public const KATEGORI = [
        'Tanah'            => 'tanah',
        'Legalitas'        => 'legal_infra',
        'Pematangan Lahan' => 'legal_infra',
        'Infrastruktur'    => 'legal_infra',
        'Fasilitas'        => 'legal_infra',
        'Marketing'        => 'marketing',
        'Operasional'      => 'operasional',
        'Cadangan'         => 'cadangan',
    ];

    protected $fillable = ['periode', 'kategori', 'pos', 'uraian', 'anggaran', 'realisasi', 'tanggal_pengeluaran', 'catatan', 'kas_transaksi_id', 'dicentang_oleh', 'dicentang_pada'];

    protected $casts = ['anggaran' => 'decimal:2', 'realisasi' => 'decimal:2', 'tanggal_pengeluaran' => 'date', 'dicentang_pada' => 'datetime'];

    public function kas(): BelongsTo { return $this->belongsTo(KasTransaksi::class, 'kas_transaksi_id'); }
    public function pencentang(): BelongsTo { return $this->belongsTo(User::class, 'dicentang_oleh'); }

    /** Semua kas keluar yang tertaut (termasuk data lama sebelum ada centang). */
    public function kasKeluar(): HasMany
    {
        return $this->hasMany(KasTransaksi::class)->where('jenis', 'keluar')->latest('tanggal');
    }

    public function isDicentang(): bool
    {
        return $this->kas_transaksi_id !== null;
    }

    public function getRealisasiNilaiAttribute(): float
    {
        return (float) ($this->realisasi ?? 0);
    }

    /** Anggaran − realisasi; null bila anggaran kosong. */
    public function getSelisihAttribute(): ?float
    {
        return $this->anggaran === null ? null : (float) $this->anggaran - $this->realisasi_nilai;
    }

    public function getStatusAttribute(): string
    {
        return self::hitungStatus($this->anggaran, $this->realisasi);
    }

    public static function hitungStatus($anggaran, $realisasi): string
    {
        return match (true) {
            $anggaran === null || $anggaran === ''   => 'belum_dianggarkan',
            $realisasi === null || $realisasi === '' => 'belum_direalisasikan',
            (float) $realisasi > (float) $anggaran   => 'melebihi',
            default                                  => 'sesuai',
        };
    }
}
