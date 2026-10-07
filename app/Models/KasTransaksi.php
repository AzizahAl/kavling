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
        'rab'        => 'Realisasi RAB',
    ];

    /** Menu asal baris otomatis: baris ini hanya bisa diubah dari sana. */
    public const MENU_ASAL = [
        'pembayaran' => 'Transaksi Penjualan',
        'refund'     => 'Transaksi Penjualan',
        'komisi'     => 'Agen & Marketing',
        'rab'        => 'RAB & Realisasi',
    ];

    public const IKON_KATEGORI = [
        'Penjualan' => 'cart', 'Refund Pembatalan' => 'undo', 'Komisi Agen' => 'coins',
        'Tanah' => 'landmark', 'Legalitas' => 'scale', 'Pematangan Lahan' => 'map-pin', 'Infrastruktur' => 'building',
        'Fasilitas' => 'home', 'Marketing' => 'trending-up', 'Operasional' => 'cog', 'Cadangan' => 'shield',
    ];

    /** Pilihan kategori input manual: kategori RAB; kas masuk ditambah Penjualan. */
    public static function kategoriManual(string $jenis): array
    {
        $rab = array_keys(Rab::KATEGORI);

        return $jenis === 'masuk' ? ['Penjualan', ...$rab] : $rab;
    }

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

    public function ikonKategori(): string
    {
        return self::IKON_KATEGORI[$this->kategori] ?? 'tag';
    }

    /** Isi kolom Sumber/Transaksi: kode asal untuk baris otomatis, isian bebas untuk baris manual. */
    public function sumberTampil(): ?string
    {
        return match ($this->asal) {
            'rab'   => 'RAB' . ($this->rab?->periode ? ' · ' . tanggal($this->rab->periode . '-01', 'M Y') : ''),
            'tanah' => 'Kewajiban Tanah' . ($this->sumber ? ' · ' . $this->sumber : ''),
            default => $this->sumber,
        };
    }

    public function linkSumber(): ?string
    {
        return match ($this->asal) {
            'pembayaran', 'refund' => $this->transaksi_id ? route('transaksi-penjualan.show', $this->transaksi_id) : null,
            'komisi' => ($a = KomisiPembayaran::find($this->komisi_pembayaran_id)?->agen_id) ? route('agen.show', $a) : null,
            'rab'    => route('rab.index'),
            default => null,
        };
    }
}
