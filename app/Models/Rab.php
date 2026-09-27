<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rab extends Model
{
    protected $table = 'rabs';

    protected $fillable = [
        'kategori',
        'uraian',
        'anggaran',
        'realisasi',
        'status_realisasi',
        'catatan',
    ];

    protected $casts = [
        'anggaran'  => 'decimal:2',
        'realisasi' => 'decimal:2',
    ];

    // Selisih = Anggaran - Realisasi
    public function getSelisihAttribute()
    {
        if (is_null($this->anggaran)) {
            return null;
        }
        return $this->anggaran - $this->realisasi;
    }

    // Status keuangan otomatis (badge di tabel)
    public function getStatusKeuanganAttribute(): string
    {
        if (is_null($this->anggaran) || $this->anggaran == 0) {
            return 'belum_dianggarkan';
        }

        if ($this->realisasi == $this->anggaran) {
            return 'sesuai';
        }

        return $this->realisasi < $this->anggaran ? 'kurang' : 'lebih';
    }

    public function getStatusKeuanganLabelAttribute(): string
    {
        return match ($this->status_keuangan) {
            'sesuai'            => 'Sesuai',
            'kurang'            => 'Kurang',
            'lebih'             => 'Lebih',
            default             => 'Belum Dianggarkan',
        };
    }
}