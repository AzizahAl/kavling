<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/** Identitas konsumen. Data pembelian ada di TransaksiPenjualan. */
class Konsumen extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_konsumen', 'nama_lengkap', 'label', 'nik', 'no_hp', 'email', 'alamat', 'pekerjaan', 'catatan',
    ];

    public function transaksis(): HasMany
    {
        return $this->hasMany(TransaksiPenjualan::class)->latest('tanggal');
    }

    public function pembayarans(): HasManyThrough
    {
        return $this->hasManyThrough(Pembayaran::class, TransaksiPenjualan::class, 'konsumen_id', 'transaksi_id');
    }
}
