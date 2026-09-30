<?php

namespace App\Services;

use App\Models\AlokasiKas;
use App\Models\KasTransaksi;
use App\Models\Rab;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Alokasi kas otomatis (sheet CASHFLOW_ALOKASI):
 * setiap uang masuk dari konsumen dibagi 50% tanah, 25% legal+infra, 10% marketing, 10% cadangan, 5% operasional
 * (persen dari Pengaturan pada saat uang diterima). Refund pembatalan mengurangi alokasi dengan persen yang sama.
 *
 * Laba = uang masuk dari konsumen − semua pengeluaran terealisasi.
 * Laba layak dibagi (pengelola : pemilik lahan) hanya bila:
 *  (a) tanah lunas: alokasi tanah ≥ total kewajiban tanah (Pengaturan), dan
 *  (b) legal & infrastruktur tercukupi: alokasi legal+infra ≥ total anggaran RAB kategori terkait.
 */
class AlokasiService
{
    public const POS_PENGATURAN = [
        'tanah'       => 'alokasi_tanah',
        'legal_infra' => 'alokasi_legal_infra',
        'marketing'   => 'alokasi_marketing',
        'cadangan'    => 'alokasi_cadangan',
        'operasional' => 'alokasi_operasional',
    ];

    /** Buat ulang alokasi untuk satu baris kas (dipanggil setiap kas pembayaran/refund dibuat atau diubah). */
    public function alokasikan(KasTransaksi $kas): void
    {
        $tanda = match ($kas->asal) {
            'pembayaran' => 1,
            'refund'     => -1,
            default      => 0,
        };

        // Persen yang dipakai:
        // - pembayaran yang diubah: tetap persen saat pertama diterima,
        // - refund: proporsi alokasi seluruh pembayaran transaksinya (pengurangan seimbang dengan pemasukan),
        // - selain itu: persen di Pengaturan saat ini.
        $lama = $kas->alokasis()->pluck('persen', 'pos')->map(fn ($p) => (float) $p);
        $kas->alokasis()->delete();
        if ($tanda === 0) {
            return;
        }

        $persen = collect(self::POS_PENGATURAN)->map(fn ($kunci) => (float) Pengaturan::get($kunci, 0));
        if ($tanda === 1 && $lama->isNotEmpty()) {
            $persen = $persen->map(fn ($p, $pos) => $lama[$pos] ?? $p);
        } elseif ($tanda === -1 && $kas->transaksi_id) {
            $asal = AlokasiKas::whereHas('kas', fn ($q) => $q->where('transaksi_id', $kas->transaksi_id)->where('asal', 'pembayaran'))
                ->selectRaw('pos, SUM(nominal) n')->groupBy('pos')->pluck('n', 'pos');
            if ($asal->sum() > 0) {
                $persen = $persen->map(fn ($p, $pos) => round((float) ($asal[$pos] ?? 0) / $asal->sum() * 100, 2));
            }
        }
        $total = (int) round((float) $kas->nominal);
        $sisa = $total;
        $pos = $persen->keys();

        foreach ($pos as $i => $p) {
            $nominal = $i === $pos->count() - 1 ? $sisa : (int) round($total * $persen[$p] / 100);
            $sisa -= $nominal;
            $kas->alokasis()->create(['pos' => $p, 'persen' => $persen[$p], 'nominal' => $tanda * $nominal]);
        }
    }

    /** Per pos: dialokasikan, terpakai (kas keluar pos itu, selain refund), saldo. */
    public function posisiPos(): Collection
    {
        $alokasi = AlokasiKas::selectRaw('pos, SUM(nominal) n')->groupBy('pos')->pluck('n', 'pos');
        $terpakai = KasTransaksi::where('jenis', 'keluar')->where('asal', '!=', 'refund')->whereNotNull('pos')
            ->selectRaw('pos, SUM(nominal) n')->groupBy('pos')->pluck('n', 'pos');

        return collect(\App\Models\AlokasiKas::POS)->map(fn ($label, $pos) => (object) [
            'pos'       => $pos,
            'label'     => $label,
            'persen'    => (float) Pengaturan::get(self::POS_PENGATURAN[$pos], 0),
            'alokasi'   => (float) ($alokasi[$pos] ?? 0),
            'terpakai'  => (float) ($terpakai[$pos] ?? 0),
            'saldo'     => (float) ($alokasi[$pos] ?? 0) - (float) ($terpakai[$pos] ?? 0),
        ]);
    }

    public function kelayakanLaba(?Collection $pos = null): array
    {
        $pos ??= $this->posisiPos();

        $masuk = (float) KasTransaksi::where('jenis', 'masuk')->where('asal', 'pembayaran')->sum('nominal');
        $keluar = (float) KasTransaksi::where('jenis', 'keluar')->sum('nominal');
        $laba = $masuk - $keluar;

        $targetTanah = Pengaturan::get('target_kewajiban_tanah');
        $alokasiTanah = $pos['tanah']->alokasi;

        $kategoriLegal = Pengaturan::get('kategori_rab_legal_infra', []);
        $targetLegal = (float) Rab::whereIn('kategori', $kategoriLegal)->sum('anggaran');
        $alokasiLegal = $pos['legal_infra']->alokasi;

        $tanahLunas = $targetTanah !== null && $targetTanah > 0 && $alokasiTanah >= $targetTanah;
        $legalCukup = $targetLegal > 0 && $alokasiLegal >= $targetLegal;
        $layak = $tanahLunas && $legalCukup && $laba > 0;

        $pPengelola = (float) Pengaturan::get('laba_pengelola_persen', 80);
        $pPemilik = (float) Pengaturan::get('laba_pemilik_persen', 20);

        return [
            'uang_masuk'     => $masuk,
            'pengeluaran'    => $keluar,
            'laba'           => $laba,
            'tanah'          => ['target' => $targetTanah, 'alokasi' => $alokasiTanah, 'terpenuhi' => $tanahLunas,
                                 'catatan' => $targetTanah === null ? 'Total kewajiban tanah belum diisi di Pengaturan Proyek.' : null],
            'legal'          => ['target' => $targetLegal, 'alokasi' => $alokasiLegal, 'terpenuhi' => $legalCukup, 'kategori' => $kategoriLegal,
                                 'catatan' => $targetLegal <= 0 ? 'Anggaran RAB kategori ' . implode(', ', $kategoriLegal) . ' belum diisi.' : null],
            'layak'          => $layak,
            'persen_pengelola' => $pPengelola,
            'persen_pemilik' => $pPemilik,
            'bagian_pengelola' => $layak ? round($laba * $pPengelola / 100) : 0,
            'bagian_pemilik' => $layak ? round($laba * $pPemilik / 100) : 0,
        ];
    }

    /** Rekap alokasi per transaksi penjualan (seperti tabel Excel CASHFLOW_ALOKASI). */
    public function perTransaksi(): Collection
    {
        return DB::table('alokasi_kas as a')
            ->join('kas_transaksis as k', 'k.id', '=', 'a.kas_transaksi_id')
            ->join('transaksi_penjualans as t', 't.id', '=', 'k.transaksi_id')
            ->join('konsumens as c', 'c.id', '=', 't.konsumen_id')
            ->join('kavlings as v', 'v.id', '=', 't.kavling_id')
            ->selectRaw("t.id, t.kode_transaksi, t.status, c.nama_lengkap, v.kode_kavling,
                SUM(a.nominal) total,
                SUM(CASE WHEN a.pos='tanah' THEN a.nominal ELSE 0 END) tanah,
                SUM(CASE WHEN a.pos='legal_infra' THEN a.nominal ELSE 0 END) legal_infra,
                SUM(CASE WHEN a.pos='marketing' THEN a.nominal ELSE 0 END) marketing,
                SUM(CASE WHEN a.pos='cadangan' THEN a.nominal ELSE 0 END) cadangan,
                SUM(CASE WHEN a.pos='operasional' THEN a.nominal ELSE 0 END) operasional")
            ->groupBy('t.id', 't.kode_transaksi', 't.status', 'c.nama_lengkap', 'v.kode_kavling')
            ->orderBy('t.kode_transaksi')
            ->get();
    }
}
