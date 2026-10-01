<?php

namespace App\Services;

use App\Models\KasTransaksi;
use App\Models\PembayaranTanah;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Kewajiban tanah kepada pemilik lahan:
 *  - total kesepakatan boleh kosong sampai hasil pengukuran resmi (diubah admin),
 *  - setiap pembayaran ke pemilik lahan tercatat juga sebagai kas keluar pos Tanah,
 *  - tanah lunas hanya bila total sudah ditetapkan DAN sisa kewajiban = 0.
 */
class KewajibanTanahService
{
    public function __construct(private KasService $kas) {}

    public function ringkasan(): array
    {
        $total = Pengaturan::get('total_kewajiban_tanah');
        $terbayar = (float) PembayaranTanah::sum('nominal');
        $sisa = $total === null ? null : max(0, (float) $total - $terbayar);

        return [
            'total'    => $total,
            'terbayar' => $terbayar,
            'sisa'     => $sisa,
            'persen'   => $total ? min(100, $terbayar / $total * 100) : 0,
            'lunas'    => $total !== null && $total > 0 && $sisa <= 0,
        ];
    }

    public function aturTotal(?int $total): void
    {
        if ($total !== null && $total < (float) PembayaranTanah::sum('nominal')) {
            throw ValidationException::withMessages(['total' => 'Total kesepakatan tidak boleh lebih kecil dari yang sudah dibayar (' . rupiah(PembayaranTanah::sum('nominal')) . ').']);
        }
        Pengaturan::simpan(['total_kewajiban_tanah' => $total]);
    }

    public function bayar(array $data): PembayaranTanah
    {
        return DB::transaction(function () use ($data) {
            $r = $this->ringkasan();
            if ($r['sisa'] !== null && (float) $data['nominal'] > $r['sisa']) {
                throw ValidationException::withMessages(['nominal' => 'Melebihi sisa kewajiban (' . rupiah($r['sisa']) . ').']);
            }

            $p = PembayaranTanah::create($data + ['dibuat_oleh' => auth()->id()]);
            KasTransaksi::create([
                'tanggal'             => $p->tanggal,
                'kode'                => $this->kas->kodeBerikut('keluar', $p->tanggal),
                'kategori'            => 'Tanah',
                'pos'                 => 'tanah',
                'jenis'               => 'keluar',
                'asal'                => 'tanah',
                'pembayaran_tanah_id' => $p->id,
                'uraian'              => 'Pembayaran tanah ke pemilik lahan – ' . $p->keterangan,
                'nominal'             => $p->nominal,
                'sumber'              => Pengaturan::get('nama_pemilik_lahan'),
            ]);

            return $p;
        });
    }

    public function hapus(PembayaranTanah $p): void
    {
        DB::transaction(function () use ($p) {
            $p->kas()->delete();
            $p->delete();
        });
    }
}
