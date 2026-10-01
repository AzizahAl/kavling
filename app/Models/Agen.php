<?php

namespace App\Models;

use App\Services\Pengaturan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Angka performa agen dihitung KomisiService/LeadService, tidak disimpan di tabel ini. */
class Agen extends Model
{
    // komisi_persen = data lama (aturan persen, tidak dipakai lagi); komisi_nominal kosong = nominal bawaan di Pengaturan
    protected $fillable = ['kode_agen', 'nama_agen', 'no_hp', 'email', 'komisi_persen', 'komisi_nominal', 'aktif', 'catatan'];

    protected $casts = [
        'komisi_persen' => 'decimal:2',
        'komisi_nominal' => 'decimal:2',
        'aktif'         => 'boolean',
    ];

    public function leads(): HasMany { return $this->hasMany(Lead::class); }
    public function transaksis(): HasMany { return $this->hasMany(TransaksiPenjualan::class); }
    public function komisiPembayarans(): HasMany { return $this->hasMany(KomisiPembayaran::class)->latest('tanggal'); }
    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne { return $this->hasOne(User::class); }

    /** Nominal komisi per transaksi yang berlaku: milik agen, atau bawaan dari Pengaturan. */
    public function nominalKomisi(): float
    {
        return $this->komisi_nominal !== null ? (float) $this->komisi_nominal : (float) Pengaturan::get('komisi_nominal', 0);
    }
}
