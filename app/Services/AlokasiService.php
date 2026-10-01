<?php

namespace App\Services;

use App\Models\AlokasiKas;
use App\Models\PembatalanTransaksi;
use App\Models\KasTransaksi;
use App\Models\Rab;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Alokasi kas otomatis (sheet CASHFLOW_ALOKASI):
 * setiap uang masuk dari konsumen dibagi 50% tanah, 25% legal+infra, 10% marketing, 10% cadangan, 5% operasional
 * (persen dari Pengaturan pada saat uang diterima). Refund pembatalan mengurangi alokasi dengan persen yang sama.
 *
 * Pembatalan: lihat alokasikanPembatalan (reservasi dibalik, potongan booking ke Marketing).
 * Kelayakan bagi laba: lihat kelayakanLaba (tanah lunas dari modul Kewajiban Tanah).
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

    /**
     * Alokasi koreksi saat transaksi dibatalkan (menempel ke data pembatalan, bukan ke baris kas refund):
     *  - reservasi & bagian booking/DP/angsuran yang dikembalikan: alokasi asalnya dibalik sesuai proporsi,
     *  - potongan booking: alokasi umum booking dibalik, lalu potongan dicatat 100% ke pos Marketing,
     *  - potongan DP/angsuran: tetap pada alokasi semula.
     * Jumlah seluruh baris = − total yang dikembalikan, sehingga saldo pos selalu cocok dengan kas.
     */
    public function alokasikanPembatalan(PembatalanTransaksi $p): void
    {
        $p->alokasis()->delete();

        // Alokasi asal per jenis pembayaran untuk transaksi ini
        $asal = DB::table('alokasi_kas as a')
            ->join('kas_transaksis as k', 'k.id', '=', 'a.kas_transaksi_id')
            ->join('pembayarans as b', 'b.id', '=', 'k.pembayaran_id')
            ->where('k.transaksi_id', $p->transaksi_id)->where('k.asal', 'pembayaran')
            ->selectRaw("CASE WHEN b.jenis IN ('dp','angsuran','pelunasan') THEN 'pokok' ELSE b.jenis END kelompok, a.pos, SUM(a.nominal) n")
            ->groupBy('kelompok', 'a.pos')->get()
            ->groupBy('kelompok')->map(fn ($g) => $g->pluck('n', 'pos')->map(fn ($n) => (float) $n));

        $hasil = collect(array_keys(\App\Models\AlokasiKas::POS))->mapWithKeys(fn ($pos) => [$pos => 0.0]);
        $balik = function (string $kelompok, float $porsi) use ($asal, &$hasil) {
            $alok = $asal[$kelompok] ?? collect();
            $total = $alok->sum();
            if ($total <= 0 || $porsi <= 0) {
                return;
            }
            foreach ($alok as $pos => $n) {
                $hasil[$pos] -= $n * min(1, $porsi / $total);
            }
        };

        $balik('reservasi', (float) $p->reservasi_refund);
        $balik('booking', (float) $p->booking_dibayar);                 // balik seluruh alokasi umum booking…
        $hasil['marketing'] += (float) $p->booking_potongan;             // …potongan masuk pos Marketing
        $balik('pokok', (float) $p->pokok_refund);

        // Pembulatan ke rupiah utuh; selisih dimasukkan ke pos terbesar agar total tepat
        $bulat = $hasil->map(fn ($n) => round($n));
        $selisih = -round((float) $p->total_refund) - $bulat->sum();
        if ($selisih != 0) {
            $pos = $bulat->map(fn ($n) => abs($n))->sortDesc()->keys()->first();
            $bulat[$pos] += $selisih;
        }

        foreach ($bulat as $pos => $nominal) {
            if ($nominal != 0) {
                $p->alokasis()->create(['pos' => $pos, 'persen' => 0, 'nominal' => $nominal]);
            }
        }
    }

    /**
     * Laba = uang masuk dari konsumen − semua pengeluaran terealisasi (termasuk pembayaran tanah & refund).
     * Laba layak dibagi (pengelola : pemilik lahan) hanya bila:
     *  (a) tanah lunas: total kesepakatan kewajiban tanah sudah ditetapkan DAN sisa kewajiban = 0,
     *  (b) legal & infrastruktur tercukupi: alokasi legal+infra ≥ total anggaran RAB kategori terkait.
     */
    public function kelayakanLaba(?Collection $pos = null): array
    {
        $pos ??= $this->posisiPos();

        $masuk = (float) KasTransaksi::where('jenis', 'masuk')->where('asal', 'pembayaran')->sum('nominal');
        $keluar = (float) KasTransaksi::where('jenis', 'keluar')->sum('nominal');
        $laba = $masuk - $keluar;

        $tanah = app(KewajibanTanahService::class)->ringkasan();

        $kategoriLegal = Pengaturan::get('kategori_rab_legal_infra', []);
        $targetLegal = (float) Rab::whereIn('kategori', $kategoriLegal)->sum('anggaran');
        $alokasiLegal = $pos['legal_infra']->alokasi;

        $legalCukup = $targetLegal > 0 && $alokasiLegal >= $targetLegal;
        $layak = $tanah['lunas'] && $legalCukup && $laba > 0;

        $pPengelola = (float) Pengaturan::get('laba_pengelola_persen', 80);
        $pPemilik = (float) Pengaturan::get('laba_pemilik_persen', 20);

        return [
            'uang_masuk'     => $masuk,
            'pengeluaran'    => $keluar,
            'laba'           => $laba,
            'laba_tersedia'  => $layak ? $laba : 0,
            'tanah'          => ['target' => $tanah['total'], 'alokasi' => $tanah['terbayar'], 'sisa' => $tanah['sisa'], 'terpenuhi' => $tanah['lunas'],
                                 'catatan' => $tanah['total'] === null ? 'Total kesepakatan kewajiban tanah belum ditetapkan.' : null],
            'legal'          => ['target' => $targetLegal, 'alokasi' => $alokasiLegal, 'terpenuhi' => $legalCukup, 'kategori' => $kategoriLegal,
                                 'catatan' => $targetLegal <= 0 ? 'Anggaran RAB kategori ' . implode(', ', $kategoriLegal) . ' belum diisi.' : null],
            'layak'          => $layak,
            'persen_pengelola' => $pPengelola,
            'persen_pemilik' => $pPemilik,
            'bagian_pengelola' => $layak ? round($laba * $pPengelola / 100) : 0,
            'bagian_pemilik' => $layak ? round($laba * $pPemilik / 100) : 0,
        ];
    }

    /** Rekap alokasi per transaksi penjualan (seperti tabel Excel CASHFLOW_ALOKASI), termasuk koreksi pembatalan. */
    public function perTransaksi(): Collection
    {
        return DB::table('alokasi_kas as a')
            ->leftJoin('kas_transaksis as k', 'k.id', '=', 'a.kas_transaksi_id')
            ->leftJoin('pembatalan_transaksis as pb', 'pb.id', '=', 'a.pembatalan_id')
            ->join('transaksi_penjualans as t', 't.id', '=', DB::raw('COALESCE(k.transaksi_id, pb.transaksi_id)'))
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
