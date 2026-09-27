<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiPenjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_transaksi', 'tanggal', 'status',
        'konsumen_id', 'kavling_id', 'agen_id',
        'jenis_pembayaran', 'nilai_jual', 'tenor',
        'nominal_dp', 'total_bayar', 'sisa_pembayaran', 'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nilai_jual' => 'decimal:2',
        'nominal_dp' => 'decimal:2',
        'total_bayar' => 'decimal:2',
        'sisa_pembayaran' => 'decimal:2',
    ];

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class);
    }

    public function kavling()
    {
        return $this->belongsTo(Kavling::class);
    }

    public function agen()
    {
        return $this->belongsTo(Agen::class);
    }

    public static function generateKodeTransaksi(): string
    {
        $tahun = now()->year;
        $last = self::where('kode_transaksi', 'like', "TRX-{$tahun}-%")
            ->orderByDesc('id')
            ->first();

        $urut = 1;
        if ($last) {
            $urut = (int) substr($last->kode_transaksi, -4) + 1;
        }

        return sprintf('TRX-%d-%04d', $tahun, $urut);
    }
}