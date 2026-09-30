<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rab extends Model
{
    protected $table = 'rabs'; // ganti kalau nama tabel Anda berbeda

    protected $fillable = [
        'kategori',
        'uraian',
        'anggaran',
        'realisasi',
        'status_realisasi',
        'catatan',
    ];

    protected $casts = [
        'anggaran'  => 'integer',
        'realisasi' => 'integer',
    ];

    // Selisih = anggaran - realisasi
    public function getSelisihAttribute()
    {
        if (is_null($this->anggaran)) {
            return null;
        }
        return (int) $this->anggaran - (int) $this->realisasi;
    }

    // belum / sesuai / kurang / lebih
    public function getStatusKeuanganAttribute()
    {
        $anggaran  = (int) $this->anggaran;
        $realisasi = (int) $this->realisasi;

        if ($realisasi <= 0) {
            return 'belum';   // realisasi belum diisi
        }
        if ($realisasi === $anggaran) {
            return 'sesuai';  // Realisasi = Anggaran
        }
        if ($realisasi < $anggaran) {
            return 'kurang';  // Realisasi < Anggaran
        }
        return 'lebih';       // Realisasi > Anggaran
    }

    public function getStatusKeuanganLabelAttribute()
    {
        return match ($this->status_keuangan) {
            'sesuai' => 'Sesuai Anggaran',
            'kurang' => 'Di Bawah Anggaran',
            'lebih'  => 'Melebihi Anggaran',
            default  => 'Belum Dianggarkan',
        };
    }
}