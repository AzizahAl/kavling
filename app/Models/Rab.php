<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** RAB. Realisasi = jumlah kas keluar yang ditautkan ke item ini (lihat scopeDenganRealisasi). */
class Rab extends Model
{
    protected $table = 'rabs';

    protected $fillable = ['kategori', 'pos', 'uraian', 'anggaran', 'catatan'];

    protected $casts = ['anggaran' => 'decimal:2'];

    public function kasKeluar(): HasMany
    {
        return $this->hasMany(KasTransaksi::class)->where('jenis', 'keluar')->latest('tanggal');
    }

    public function scopeDenganRealisasi(Builder $q): Builder
    {
        return $q->withSum('kasKeluar as realisasi', 'nominal');
    }

    public function getRealisasiNilaiAttribute(): float
    {
        return (float) ($this->attributes['realisasi'] ?? $this->kasKeluar()->sum('nominal'));
    }

    public function getSelisihAttribute(): ?float
    {
        return $this->anggaran === null ? null : (float) $this->anggaran - $this->realisasi_nilai;
    }

    /** Sama dengan rumus Excel RAB_MASTER kolom Status. */
    public function getStatusAttribute(): string
    {
        if ($this->anggaran === null) {
            return 'belum_dianggarkan';
        }
        $r = $this->realisasi_nilai;
        $a = (float) $this->anggaran;

        return match (true) {
            $r > $a  => 'melebihi',
            $r == $a => 'sesuai',
            default  => 'berjalan',
        };
    }
}
