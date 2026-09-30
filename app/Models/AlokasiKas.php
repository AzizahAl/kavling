<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlokasiKas extends Model
{
    protected $table = 'alokasi_kas';

    public const POS = [
        'tanah'       => 'Tanah',
        'legal_infra' => 'Legal & Infrastruktur',
        'marketing'   => 'Marketing & Komisi',
        'cadangan'    => 'Cadangan',
        'operasional' => 'Operasional',
    ];

    protected $fillable = ['kas_transaksi_id', 'pos', 'persen', 'nominal'];

    protected $casts = ['persen' => 'decimal:2', 'nominal' => 'decimal:2'];

    public function kas(): BelongsTo
    {
        return $this->belongsTo(KasTransaksi::class, 'kas_transaksi_id');
    }
}
