<?php

namespace App\Services;

use App\Models\Agen;
use App\Models\KasTransaksi;
use App\Models\KomisiPembayaran;
use App\Models\TransaksiPenjualan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Komisi agen:
 * - dihitung dari harga jual kavling × persen komisi agen (atau bawaan di Pengaturan),
 * - menjadi hak agen saat kavling berstatus Terjual (PPJB ditandatangani),
 * - dibayar manual oleh admin (bisa bertahap); setiap pembayaran tercatat sebagai kas keluar.
 */
class KomisiService
{
    public function __construct(private KasService $kas) {}

    /** Rincian per transaksi aktif milik agen. */
    public function rincian(Agen $agen): Collection
    {
        $persen = $agen->persenKomisi();

        return $agen->transaksis()->aktif()->with(['kavling', 'konsumen', 'checklist'])->latest('tanggal')->get()
            ->map(function (TransaksiPenjualan $t) use ($persen) {
                $hak = (bool) $t->checklist?->ppjbDitandatangani();
                $komisi = $persen === null ? null : round((float) $t->nilai_jual * $persen / 100);

                return (object) [
                    'transaksi' => $t,
                    'terjual'   => $hak,
                    'komisi'    => $komisi,
                    'hak'       => $hak ? $komisi : 0,
                ];
            });
    }

    /** Ringkasan angka satu agen (semua dihitung, tidak ada yang diketik manual). */
    public function ringkasan(Agen $agen, ?Collection $rincian = null): array
    {
        $rincian ??= $this->rincian($agen);
        $persen = $agen->persenKomisi();
        $dibayar = (float) $agen->komisiPembayarans()->sum('nominal');
        $hak = $persen === null ? null : (float) $rincian->sum('hak');

        return [
            'persen'           => $persen,
            'transaksi'        => $rincian->count(),
            'terjual'          => $rincian->where('terjual', true)->count(),
            'nilai_penjualan'  => (float) $rincian->sum(fn ($r) => $r->transaksi->nilai_jual),
            'komisi_potensi'   => $persen === null ? null : (float) $rincian->sum('komisi'),
            'komisi_hak'       => $hak,
            'dibayar'          => $dibayar,
            'sisa'             => $hak === null ? null : $hak - $dibayar,
        ];
    }

    public function bayar(Agen $agen, array $data, ?int $userId): KomisiPembayaran
    {
        return DB::transaction(function () use ($agen, $data, $userId) {
            Agen::lockForUpdate()->find($agen->id);
            $r = $this->ringkasan($agen);

            if ($r['persen'] === null) {
                throw ValidationException::withMessages(['nominal' => 'Persen komisi agen ini belum diatur (di data agen atau di Pengaturan Proyek).']);
            }
            if ((float) $data['nominal'] > $r['sisa']) {
                throw ValidationException::withMessages(['nominal' => 'Pembayaran melebihi sisa komisi yang sudah menjadi hak agen (' . rupiah(max(0, $r['sisa'])) . ').']);
            }

            $p = $agen->komisiPembayarans()->create(collect($data)->only(['transaksi_id', 'tanggal', 'nominal', 'metode', 'catatan'])->all() + ['dibuat_oleh' => $userId]);

            KasTransaksi::create([
                'tanggal'              => $p->tanggal,
                'kode'                 => $this->kas->kodeBerikut('keluar', $p->tanggal),
                'kategori'             => 'Komisi Agen',
                'jenis'                => 'keluar',
                'asal'                 => 'komisi',
                'komisi_pembayaran_id' => $p->id,
                'transaksi_id'         => $p->transaksi_id,
                'uraian'               => "Komisi {$agen->nama_agen} ({$agen->kode_agen})",
                'nominal'              => $p->nominal,
                'sumber'               => $agen->kode_agen,
                'catatan'              => $p->catatan,
            ]);

            return $p;
        });
    }

    public function hapusBayar(KomisiPembayaran $p): void
    {
        DB::transaction(function () use ($p) {
            $p->kas()->delete();
            $p->delete();
        });
    }
}
