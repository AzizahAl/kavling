<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    public const SUMBER = [
        'internet'           => 'Internet',
        'flyer'              => 'Flyer',
        'penawaran_langsung' => 'Penawaran Langsung',
        'kenalan'            => 'Kenalan',
    ];

    public const TAHAP = ['lead' => 'Lead', 'prospek' => 'Prospek', 'closing' => 'Closing'];

    protected $fillable = [
        'kode', 'nama', 'no_hp', 'domisili', 'sumber', 'tahap',
        'tanggal_lead', 'tanggal_prospek', 'tanggal_closing',
        'agen_id', 'diinput_oleh', 'kavling_minat_id', 'transaksi_id', 'catatan',
    ];

    protected $casts = [
        'tanggal_lead'    => 'date',
        'tanggal_prospek' => 'date',
        'tanggal_closing' => 'date',
    ];

    public function agen(): BelongsTo { return $this->belongsTo(Agen::class); }
    public function penginput(): BelongsTo { return $this->belongsTo(User::class, 'diinput_oleh'); }
    public function kavlingMinat(): BelongsTo { return $this->belongsTo(Kavling::class, 'kavling_minat_id'); }
    public function transaksi(): BelongsTo { return $this->belongsTo(TransaksiPenjualan::class, 'transaksi_id'); }
    public function riwayats(): HasMany { return $this->hasMany(LeadRiwayat::class)->latest('id'); }

    public function getLabelSumberAttribute(): string
    {
        return self::SUMBER[$this->sumber] ?? $this->sumber;
    }
}
