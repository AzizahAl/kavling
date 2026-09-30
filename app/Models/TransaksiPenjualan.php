<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Satu-satunya sumber data penjualan.
 * Total bayar & sisa TIDAK disimpan: selalu dihitung dari tabel pembayarans.
 *  - nilai_jual           = harga kavling terkunci (luas × harga/m² saat transaksi)
 *  - biaya reservasi/booking = di luar harga kavling
 *  - pokok terbayar       = DP + angsuran + pelunasan
 *  - sisa                 = nilai_jual − pokok terbayar
 */
class TransaksiPenjualan extends Model
{
    use HasFactory;

    public const STATUS = ['reservasi', 'booking', 'dp', 'angsuran', 'lunas', 'batal'];
    public const JENIS_POKOK = ['dp', 'angsuran', 'pelunasan'];

    protected $fillable = [
        'kode_transaksi', 'tanggal', 'status',
        'konsumen_id', 'kavling_id', 'agen_id', 'skema_harga_id',
        'luas', 'harga_per_m2', 'nilai_jual', 'biaya_reservasi', 'biaya_booking',
        'jenis_pembayaran', 'tenor', 'nominal_dp', 'catatan',
        'tanggal_batal', 'alasan_batal', 'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal'         => 'date',
        'tanggal_batal'   => 'date',
        'luas'            => 'decimal:2',
        'harga_per_m2'    => 'decimal:2',
        'nilai_jual'      => 'decimal:2',
        'biaya_reservasi' => 'decimal:2',
        'biaya_booking'   => 'decimal:2',
        'nominal_dp'      => 'decimal:2',
        'tenor'           => 'integer',
    ];

    public function konsumen(): BelongsTo { return $this->belongsTo(Konsumen::class); }
    public function kavling(): BelongsTo { return $this->belongsTo(Kavling::class); }
    public function agen(): BelongsTo { return $this->belongsTo(Agen::class); }
    public function tahap(): BelongsTo { return $this->belongsTo(SkemaHarga::class, 'skema_harga_id'); }
    public function pembuat(): BelongsTo { return $this->belongsTo(User::class, 'dibuat_oleh'); }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'transaksi_id')->orderBy('tanggal')->orderBy('id');
    }

    public function jadwalAngsurans(): HasMany
    {
        return $this->hasMany(JadwalAngsuran::class, 'transaksi_id')->orderBy('ke');
    }

    public function checklist(): HasOne
    {
        return $this->hasOne(ChecklistLegal::class, 'transaksi_id');
    }

    public function kasRefunds(): HasMany
    {
        return $this->hasMany(KasTransaksi::class, 'transaksi_id')->where('asal', 'refund');
    }

    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('status', '!=', 'batal');
    }

    /** Tambahkan kolom hitung total_masuk & pokok_terbayar lewat subquery (untuk daftar/tabel). */
    public function scopeDenganRingkasan(Builder $q): Builder
    {
        return $q->withSum('pembayarans as total_masuk', 'nominal')
            ->withSum(['pembayarans as pokok_terbayar' => fn ($p) => $p->whereIn('jenis', self::JENIS_POKOK)], 'nominal');
    }

    // ---------- Angka turunan (satu sumber: pembayarans) ----------

    public function totalMasuk(): float
    {
        return (float) ($this->attributes['total_masuk'] ?? $this->pembayarans->sum('nominal'));
    }

    public function pokokTerbayar(): float
    {
        return (float) ($this->attributes['pokok_terbayar']
            ?? $this->pembayarans->whereIn('jenis', self::JENIS_POKOK)->sum('nominal'));
    }

    public function terbayarJenis(string $jenis): float
    {
        return (float) $this->pembayarans->where('jenis', $jenis)->sum('nominal');
    }

    public function sisa(): float
    {
        return max(0, (float) $this->nilai_jual - $this->pokokTerbayar());
    }

    public function persenLunas(): float
    {
        return $this->nilai_jual > 0 ? min(100, $this->pokokTerbayar() / (float) $this->nilai_jual * 100) : 0;
    }

    /** Sisa harga setelah DP yang dibagi ke cicilan. */
    public function pokokDiangsur(): float
    {
        return max(0, (float) $this->nilai_jual - (float) $this->nominal_dp);
    }

    public function isBatal(): bool { return $this->status === 'batal'; }
    public function isAngsuran(): bool { return $this->jenis_pembayaran === 'angsuran'; }
}
