<?php

namespace App\Models;

use App\Services\Pengaturan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Angka performa agen dihitung KomisiService/LeadService, tidak disimpan di tabel ini. */
class Agen extends Model
{
    protected $fillable = ['kode_agen', 'nama_agen', 'no_hp', 'email', 'komisi_persen', 'aktif', 'catatan'];

    protected $casts = [
        'komisi_persen' => 'decimal:2',
        'aktif'         => 'boolean',
    ];

    public function leads(): HasMany { return $this->hasMany(Lead::class); }
    public function transaksis(): HasMany { return $this->hasMany(TransaksiPenjualan::class); }
    public function komisiPembayarans(): HasMany { return $this->hasMany(KomisiPembayaran::class)->latest('tanggal'); }

    /** Persen komisi yang berlaku: milik agen, atau bawaan dari Pengaturan bila kosong. */
    public function persenKomisi(): ?float
    {
        return $this->komisi_persen !== null ? (float) $this->komisi_persen : Pengaturan::get('komisi_default_persen');
    }
}
