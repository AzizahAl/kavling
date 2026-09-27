<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Konsumen extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_konsumen',
        'nama_lengkap',
        'label',
        'nik',
        'no_hp',
        'email',
        'alamat',
        'kavling_id',
        'agen_id',
        'status_transaksi',
        'tanggal_transaksi',
        'nominal_reservasi',
        'nominal_booking',
        'down_payment',
        'skema_bayar',
        'jumlah_angsuran',
        'status_reservasi',
        'status_booking',
        'status_ppjb',
        'status_ajb',
        'catatan',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'nominal_reservasi' => 'decimal:2',
        'nominal_booking'   => 'decimal:2',
        'down_payment'      => 'decimal:2',
        'jumlah_angsuran'   => 'integer',
    ];

    public function kavling()
    {
        return $this->belongsTo(Kavling::class);
    }

    public function agen()
    {
        return $this->belongsTo(Agen::class);
    }

    public function riwayatPembayarans()
    {
        return $this->hasMany(RiwayatPembayaran::class);
    }
}