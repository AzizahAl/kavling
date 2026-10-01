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

    /** Status PEMBAYARAN transaksi (status dokumen ada di ChecklistLegal, status unit di Kavling). */
    public const STATUS = ['menunggu', 'reservasi', 'booking', 'dp', 'angsuran', 'lunas', 'batal'];
    public const LABEL_STATUS = [
        'menunggu'  => 'Reservasi – Menunggu Pembayaran',
        'reservasi' => 'Reservasi – Terbayar',
        'booking'   => 'Booking',
        'dp'        => 'DP',
        'angsuran'  => 'Angsuran',
        'lunas'     => 'Lunas',
        'batal'     => 'Batal',
    ];
    /** Tingkat kemajuan pembayaran, dipakai untuk titik "komisi jadi hak" & syarat SPK. */
    public const TINGKAT = ['menunggu' => 0, 'reservasi' => 1, 'booking' => 2, 'dp' => 3, 'angsuran' => 3, 'lunas' => 4];
    public const JENIS_POKOK = ['dp', 'angsuran', 'pelunasan'];

    protected $fillable = [
        'kode_transaksi', 'tanggal', 'status',
        'konsumen_id', 'kavling_id', 'agen_id', 'skema_harga_id',
        'luas', 'harga_per_m2', 'nilai_jual', 'biaya_reservasi', 'biaya_booking',
        'jenis_pembayaran', 'tenor', 'nominal_dp', 'catatan', 'batas_tahan',
        'tanggal_batal', 'alasan_batal', 'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal'         => 'date',
        'tanggal_batal'   => 'date',
        'batas_tahan'     => 'datetime',
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

    public function lead(): HasOne
    {
        return $this->hasOne(Lead::class, 'transaksi_id');
    }

    public function riwayats(): HasMany
    {
        return $this->hasMany(StatusRiwayat::class, 'transaksi_id')->orderBy('waktu')->orderBy('id');
    }

    public function pembatalan(): HasOne
    {
        return $this->hasOne(PembatalanTransaksi::class, 'transaksi_id');
    }

    public function kasRefunds(): HasMany
    {
        return $this->hasMany(KasTransaksi::class, 'transaksi_id')->where('asal', 'refund');
    }

    /** Belum batal (termasuk yang masih menunggu pembayaran; kavlingnya sedang ditahan). */
    public function scopeAktif(Builder $q): Builder
    {
        return $q->where('status', '!=', 'batal');
    }

    /** Sudah menghasilkan penerimaan & belum batal: dasar angka penjualan, pendapatan, komisi. */
    public function scopeBerjalan(Builder $q): Builder
    {
        return $q->whereNotIn('status', ['batal', 'menunggu']);
    }

    public function scopeMenunggu(Builder $q): Builder
    {
        return $q->where('status', 'menunggu');
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
    public function isMenunggu(): bool { return $this->status === 'menunggu'; }

    public function getLabelStatusAttribute(): string
    {
        return self::LABEL_STATUS[$this->status] ?? $this->status;
    }

    /**
     * Tingkat yang sudah tercapai dari uang yang benar-benar masuk (tetap terbaca walau transaksi kemudian batal).
     * 0 belum bayar · 1 reservasi · 2 booking terbayar · 3 DP/angsuran · 4 lunas
     */
    public function tingkatTercapai(): int
    {
        $ada = fn (string $j) => $this->terbayarJenis($j) > 0;

        return match (true) {
            $this->nilai_jual > 0 && $this->pokokTerbayar() >= (float) $this->nilai_jual => 4,
            $ada('dp') || $ada('angsuran') || $ada('pelunasan') => 3,
            $this->bookingTerbayar() => 2,
            $ada('reservasi') => 1,
            default => 0,
        };
    }

    /** Booking dianggap terbayar bila uang booking mencapai biaya booking transaksi ini. */
    public function bookingTerbayar(): bool
    {
        $biaya = (float) $this->biaya_booking;
        $bayar = $this->terbayarJenis('booking');

        return $biaya > 0 ? $bayar >= $biaya : $bayar > 0;
    }

    /** SPK dibuat setelah booking terbayar. Null = boleh; selain itu alasan mengapa belum bisa. */
    public function alasanSpkBelumBisa(): ?string
    {
        if ($this->isBatal()) {
            return 'Transaksi sudah dibatalkan.';
        }

        return $this->bookingTerbayar() || $this->tingkatTercapai() >= 3 ? null : 'SPK dibuat setelah booking fee terbayar.';
    }
    public function isAngsuran(): bool { return $this->jenis_pembayaran === 'angsuran'; }
}
