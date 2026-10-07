<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistLegal extends Model
{
    /** Status DOKUMEN. Reservasi bukan dokumen (urusan pembayaran); kolomnya tetap disimpan untuk data lama. */
    public const ITEM = [
        'spk'  => 'SPK',
        'ppjb' => 'PPJB',
        'ajb'  => 'AJB',
    ];

    protected $fillable = [
        'transaksi_id',
        'reservasi_status', 'reservasi_tanggal', 'spk_status', 'spk_tanggal',
        'ppjb_status', 'ppjb_tanggal', 'ajb_status', 'ajb_tanggal',
        'nomor_spk', 'nomor_ppjb', 'catatan',
    ];

    protected $casts = [
        'reservasi_tanggal' => 'date',
        'spk_tanggal'       => 'date',
        'ppjb_tanggal'      => 'date',
        'ajb_tanggal'       => 'date',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'transaksi_id');
    }

    /** Isian popup Ubah Dokumen di Data Konsumen. */
    public function dataForm(): array
    {
        $t = $this->transaksi;
        $alasanSpk = $t->alasanSpkBelumBisa();
        $data = ['id' => $this->id, 'kavling' => $t->kavling->kode_kavling, 'catatan' => $this->catatan, 'spk_alasan' => $alasanSpk,
            'url_spk' => $alasanSpk ? null : route('dokumen.lihat', [$t, 'spk']), 'url_ppjb' => route('dokumen.lihat', [$t, 'ppjb'])];
        foreach (array_keys(self::ITEM) as $item) {
            $data[$item . '_status'] = $this->{$item . '_status'} ?? 'belum';
            $data[$item . '_tanggal'] = $this->{$item . '_tanggal'}?->toDateString();
        }

        return $data;
    }

    public function ppjbDitandatangani(): bool
    {
        return $this->ppjb_status === 'selesai';
    }
}
