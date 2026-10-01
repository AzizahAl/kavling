<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KasTransaksi extends Model
{
    protected $table = 'kas_transaksis';

    public const ASAL = [
        'manual'     => 'Manual',
        'pembayaran' => 'Pembayaran Konsumen',
        'refund'     => 'Refund Pembatalan',
        'komisi'     => 'Komisi Agen',
        'tanah'      => 'Kewajiban Tanah',
    ];

    protected $fillable = [
        'tanggal', 'kode', 'kategori', 'pos', 'rab_id', 'jenis', 'asal',
        'pembayaran_id', 'transaksi_id', 'komisi_pembayaran_id', 'pembayaran_tanah_id',
        'uraian', 'nominal', 'sumber', 'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function pembayaran(): BelongsTo { return $this->belongsTo(Pembayaran::class); }
    public function transaksi(): BelongsTo { return $this->belongsTo(TransaksiPenjualan::class, 'transaksi_id'); }
    public function rab(): BelongsTo { return $this->belongsTo(Rab::class); }
    public function alokasis(): HasMany { return $this->hasMany(AlokasiKas::class, 'kas_transaksi_id'); }

    /** Baris otomatis (dari pembayaran/refund/komisi) hanya boleh diubah lewat sumbernya. */
    public function isOtomatis(): bool
    {
        return $this->asal !== 'manual';
    }

    public function linkSumber(): ?string
    {
        return match ($this->asal) {
            'pembayaran', 'refund' => $this->transaksi_id ? route('transaksi-penjualan.show', $this->transaksi_id) : null,
            'komisi' => ($a = KomisiPembayaran::find($this->komisi_pembayaran_id)?->agen_id) ? route('agen.show', $a) : null,
            'tanah'  => route('kewajiban-tanah.index'),
            default => null,
        };
    }
}
