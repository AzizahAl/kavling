<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kavling extends Model
{
    /** Status UNIT kavling (ketersediaan), terpisah dari status pembayaran & dokumen. */
    public const STATUS = ['tersedia', 'reservasi', 'booking', 'dp', 'lunas', 'terjual'];
    public const LABEL_STATUS = [
        'tersedia' => 'Tersedia', 'reservasi' => 'Reservasi', 'booking' => 'Booking',
        'dp' => 'DP / Angsuran', 'lunas' => 'Lunas', 'terjual' => 'Terjual',
    ];
    public const TIPE = ['Prima', 'Standard', 'Standard Hook'];

    protected $fillable = [
        'kode_kavling', 'blok', 'no', 'tipe', 'skema_harga_id', 'ukuran',
        'luas', 'harga_per_m2', 'harga_jual', 'status', 'catatan',
        'batas_utara', 'batas_selatan', 'batas_timur', 'batas_barat',
    ];

    protected $casts = [
        'luas'         => 'decimal:2',
        'harga_per_m2' => 'decimal:2',
        'harga_jual'   => 'decimal:2',
    ];

    public function tahap(): BelongsTo
    {
        return $this->belongsTo(SkemaHarga::class, 'skema_harga_id');
    }

    public function transaksiPenjualans(): HasMany
    {
        return $this->hasMany(TransaksiPenjualan::class, 'kavling_id');
    }

    /** Transaksi yang sedang berjalan (bukan batal). Maksimal satu, dijaga oleh database. */
    public function transaksiAktif(): HasOne
    {
        return $this->hasOne(TransaksiPenjualan::class, 'kavling_id')->where('status', '!=', 'batal');
    }

    public function getLabelStatusAttribute(): string
    {
        return self::LABEL_STATUS[$this->status] ?? $this->status;
    }
}
