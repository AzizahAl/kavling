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
 * Komisi agen (arahan sementara):
 * - nominal tetap per transaksi (Pengaturan "komisi_nominal", bisa diganti per agen), bukan persen harga,
 * - menjadi hak saat titik di Pengaturan "komisi_hak_saat" tercapai (sementara: booking terbayar),
 * - transaksi batal: mengikuti Pengaturan "komisi_saat_batal" (sementara: tetap jadi hak bila titiknya sudah tercapai),
 * - transaksi yang belum menerima uang (menunggu) tidak dihitung,
 * - dibayar manual oleh admin (bisa bertahap); setiap pembayaran tercatat sebagai kas keluar pos Marketing.
 */
class KomisiService
{
    public const TITIK = ['booking' => 2, 'dp' => 3, 'lunas' => 4];

    public function __construct(private KasService $kas) {}

    public function nominal(Agen $agen): float
    {
        return $agen->nominalKomisi();
    }

    /** Apakah komisi transaksi ini sudah menjadi hak agen (dibaca dari uang yang benar-benar masuk). */
    public function sudahHak(TransaksiPenjualan $t): bool
    {
        if ($t->isBatal() && Pengaturan::get('komisi_saat_batal', 'tetap') === 'gugur') {
            return false;
        }
        $titik = Pengaturan::get('komisi_hak_saat', 'booking');

        return $titik === 'ppjb'
            ? (bool) $t->checklist?->ppjbDitandatangani()
            : $t->tingkatTercapai() >= (self::TITIK[$titik] ?? 2);
    }

    /** Rincian per transaksi agen yang sudah menghasilkan penerimaan (batal ikut bila komisinya tetap hak). */
    public function rincian(Agen $agen): Collection
    {
        $nominal = $this->nominal($agen);
        $label = Pengaturan::PILIHAN['komisi_hak_saat'][Pengaturan::get('komisi_hak_saat', 'booking')] ?? '';

        return $agen->transaksis()->where('status', '!=', 'menunggu')->with(['kavling', 'konsumen', 'checklist', 'pembayarans'])->latest('tanggal')->get()
            ->map(function (TransaksiPenjualan $t) use ($nominal, $label) {
                $hak = $this->sudahHak($t);

                return (object) [
                    'transaksi' => $t,
                    'terjual'   => $hak,              // nama lama dipertahankan: "sudah jadi hak"
                    'komisi'    => $nominal,
                    'hak'       => $hak ? $nominal : 0,
                    'keterangan' => $hak ? 'Hak agen' : ($t->isBatal() ? 'Batal sebelum jadi hak' : 'Menunggu: ' . mb_strtolower($label)),
                ];
            })
            ->filter(fn ($r) => ! $r->transaksi->isBatal() || $r->hak > 0)
            ->values();
    }

    /** Ringkasan angka satu agen (semua dihitung, tidak ada yang diketik manual). */
    public function ringkasan(Agen $agen, ?Collection $rincian = null): array
    {
        $rincian ??= $this->rincian($agen);
        $berjalan = $rincian->filter(fn ($r) => ! $r->transaksi->isBatal());
        $dibayar = (float) $agen->komisiPembayarans()->sum('nominal');
        $hak = (float) $rincian->sum('hak');

        return [
            'nominal'          => $this->nominal($agen),
            'transaksi'        => $berjalan->count(),
            'terjual'          => $rincian->where('terjual', true)->count(),
            'nilai_penjualan'  => (float) $berjalan->sum(fn ($r) => $r->transaksi->nilai_jual),
            'komisi_potensi'   => (float) $rincian->sum('komisi'),
            'komisi_hak'       => $hak,
            'dibayar'          => $dibayar,
            'sisa'             => $hak - $dibayar,
        ];
    }

    public function bayar(Agen $agen, array $data, ?int $userId): KomisiPembayaran
    {
        return DB::transaction(function () use ($agen, $data, $userId) {
            Agen::lockForUpdate()->find($agen->id);
            $r = $this->ringkasan($agen);

            if ((float) $data['nominal'] > $r['sisa']) {
                throw ValidationException::withMessages(['nominal' => 'Pembayaran melebihi sisa komisi yang sudah menjadi hak agen (' . rupiah(max(0, $r['sisa'])) . ').']);
            }

            $p = $agen->komisiPembayarans()->create(collect($data)->only(['transaksi_id', 'tanggal', 'nominal', 'metode', 'catatan'])->all() + ['dibuat_oleh' => $userId]);

            KasTransaksi::create([
                'tanggal'              => $p->tanggal,
                'kode'                 => $this->kas->kodeBerikut('keluar', $p->tanggal),
                'kategori'             => 'Komisi Agen',
                'pos'                  => 'marketing',
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
