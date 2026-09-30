<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadRiwayat extends Model
{
    protected $fillable = ['lead_id', 'dari', 'ke', 'tanggal', 'catatan', 'user_id'];

    protected $casts = ['tanggal' => 'date'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
