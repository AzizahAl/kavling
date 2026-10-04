<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\TransaksiPenjualan;
use App\Support\Terbilang;

/** Data cetak dokumen (kwitansi, SPK, PPJB) dari satu sumber: transaksi & pembayaran. */
class DokumenService
{
    public function __construct(private AngsuranService $angsuran) {}

    /** Nomor dokumen mengikuti Excel: TR/PPJB/2026/0001 dari TRX-2026-0001. */
    public function nomor(TransaksiPenjualan $t, string $jenis): string
    {
        $simpan = $t->checklist?->{'nomor_' . strtolower($jenis)};
        if ($simpan) {
            return $simpan;
        }
        [, $tahun, $urut] = array_pad(explode('-', $t->kode_transaksi), 3, '');

        return sprintf('%s/%s/%s/%s', Pengaturan::get('prefix_kavling', 'TR'), strtoupper($jenis), $tahun, $urut);
    }

    /**
     * Isian formulir resmi dari transaksi (Form Reservasi, Form Booking, SPK, PPJB + Lampiran).
     * Data yang belum ada dibiarkan kosong (titik-titik) agar bisa diisi tangan.
     *
     * @return array{d: array, peringatan: array}
     */
    public function dataFormulir(TransaksiPenjualan $t, string $jenis): array
    {
        $t->loadMissing('konsumen', 'kavling', 'pembayarans', 'checklist', 'agen', 'jadwalAngsurans');
        $k = $t->konsumen;
        $kav = $t->kavling;
        $pengelola = Pengaturan::get('nama_pengelola');
        $bayarPertama = fn (string $j) => $t->pembayarans->firstWhere('jenis', $j);
        $rp = fn ($n) => rupiah($n, false);

        $d = [
            'nama'          => mb_strtoupper($k->nama_lengkap),
            'nik'           => $k->nik,
            'alamat'        => $k->alamat,
            'hp'            => $k->no_hp,
            'blok'          => $kav->blok,
            'nomor_kavling' => (string) (int) substr($kav->no, strlen($kav->blok)),
            'unit'          => $kav->no,
            'tipe'          => $kav->tipe,
            'ukuran'        => $kav->ukuran,
            'luas'          => self::luas($t->luas),
            'ttd_pemesan'   => mb_strtoupper($k->nama_lengkap),
            'ttd_marketing' => $t->agen?->nama_agen,
            'ttd_pengelola' => $pengelola,
            'pengelola'     => $pengelola,
        ];
        $peringatan = [];
        $kurang = fn (array $kunci) => collect($kunci)->filter(fn ($key) => blank(Pengaturan::get($key)))
            ->map(fn ($key) => Pengaturan::DEFINISI[$key][1])->values();

        switch ($jenis) {
            case 'reservasi':
                $p = $bayarPertama('reservasi');
                $tgl = $p?->tanggal ?? $t->tanggal;
                $uang = $t->terbayarJenis('reservasi') ?: (float) $t->biaya_reservasi;
                $d += [
                    'nomor'     => $this->nomor($t, 'RSV'),
                    'tanggal'   => tanggal($tgl, 'j F Y'),
                    'berlaku'   => tanggal($tgl->copy()->addDays((int) Pengaturan::get('masa_reservasi_hari', 14)), 'j F Y'),
                    'harga'     => rupiah($t->nilai_jual),
                    'uang'      => $rp($uang),
                    'terbilang' => ucwords(Terbilang::rupiah((int) round($uang))),
                    'metode'    => $p?->metode,
                    'penyetor'  => $p?->nama_penyetor,
                    'bank'      => $p?->bank_penyetor,
                    'rekening'  => $p?->rekening_penyetor,
                ];
                if (! $p) {
                    $peringatan[] = 'Uang reservasi belum dicatat: tanggal dihitung dari tanggal transaksi dan metode pembayaran belum dipilih.';
                }
                break;

            case 'booking':
                $p = $bayarPertama('booking');
                $pDp = $bayarPertama('dp');
                $d += [
                    'nomor'           => $this->nomor($t, 'BKG'),
                    'tanggal_booking' => $p ? tanggal($p->tanggal, 'j F Y') : null,
                    'harga_rp'        => rupiah($t->nilai_jual),
                    'booking_rp'      => rupiah($t->biaya_booking),
                    'skema'           => [
                        'booking'  => ['nominal' => $rp($t->biaya_booking), 'jadwal' => $p ? tanggal($p->tanggal, 'j F Y') : ''],
                        'dp'       => ['nominal' => $t->nominal_dp > 0 ? $rp($t->nominal_dp) : '', 'jadwal' => $pDp ? tanggal($pDp->tanggal, 'j F Y') : ''],
                        'angsuran' => $t->isAngsuran() ? $this->skemaAngsuran($t) : ['nominal' => '', 'jadwal' => ''],
                        'lainnya'  => $t->isAngsuran() ? ['nominal' => '', 'jadwal' => ''] : ['nominal' => $rp($t->pokokDiangsur()), 'jadwal' => 'Pelunasan (cash)'],
                    ],
                ];
                break;

            case 'spk':
                $tgl = $t->checklist?->spk_tanggal;
                $d += [
                    'nomor'      => $this->nomor($t, 'SPK'),
                    'hari'       => $tgl ? tanggal($tgl, 'l') : null,
                    'tanggal'    => $tgl ? tanggal($tgl, 'j F Y') : null,
                    'harga_m2'   => $rp($t->harga_per_m2),
                    'harga'      => $rp($t->nilai_jual),
                    'cara_bayar' => $t->isAngsuran() ? 'Angsuran ' . $t->tenor . ' bulan' : 'Cash',
                ];
                if (! $tgl) {
                    $peringatan[] = 'Tanggal SPK belum diisi di Checklist Legal; hari & tanggal dibiarkan kosong untuk diisi tangan.';
                }
                if (! $pengelola) {
                    $peringatan[] = 'Lengkapi di Pengaturan Proyek: Nama Pengelola / Penjual.';
                }
                break;

            case 'ppjb':
                $tgl = $t->checklist?->ppjb_tanggal;
                $d += [
                    'nomor'      => $this->nomor($t, 'PPJB'),
                    'hari'       => $tgl ? tanggal($tgl, 'l') : null,
                    'tgl'        => $tgl?->day,
                    'bulan'      => $tgl ? tanggal($tgl, 'F') : null,
                    'tahun'      => $tgl?->year,
                    'tanggal'    => $tgl ? tanggal($tgl, 'j F Y') : null,
                    'tempat'     => Pengaturan::get('kota_dokumen'),
                    'penjual'    => [
                        'nama' => $pengelola, 'nik' => Pengaturan::get('nik_pengelola'),
                        'alamat' => Pengaturan::get('alamat_pengelola'), 'hp' => Pengaturan::get('hp_pengelola'),
                    ],
                    'pembeli'    => ['nama' => $d['nama'], 'nik' => $k->nik, 'alamat' => $k->alamat, 'hp' => $k->no_hp],
                    'lokasi'     => Pengaturan::get('alamat_proyek'),
                    'harga'      => $rp($t->nilai_jual),
                    'terbilang'  => ucwords(Terbilang::rupiah((int) round($t->nilai_jual))),
                    'dp'         => $rp($t->nominal_dp),
                    'sisa_bayar' => $rp($t->pokokDiangsur()),
                    'jadwal'     => $t->jadwalAngsurans->mapWithKeys(fn ($j) => [$j->ke => [
                        'tanggal' => tanggal($j->jatuh_tempo, 'j F Y'), 'nominal' => rupiah($j->nominal),
                    ]])->all(),
                    'batas'      => ['utara' => $kav->batas_utara, 'selatan' => $kav->batas_selatan, 'timur' => $kav->batas_timur, 'barat' => $kav->batas_barat],
                ];
                if (($belum = $kurang(['nama_pengelola', 'nik_pengelola', 'alamat_pengelola', 'hp_pengelola', 'kota_dokumen', 'alamat_proyek']))->isNotEmpty()) {
                    $peringatan[] = 'Lengkapi di Pengaturan Proyek: ' . $belum->implode(', ') . '.';
                }
                if (! $tgl) {
                    $peringatan[] = 'Tanggal PPJB belum diisi di Checklist Legal; hari & tanggal dibiarkan kosong untuk diisi tangan.';
                }
                if (collect($d['batas'])->filter()->isEmpty()) {
                    $peringatan[] = "Batas-batas kavling {$kav->kode_kavling} (Lampiran A) belum diisi di Master Kavling.";
                }
                break;
        }

        return ['d' => $d, 'peringatan' => $peringatan];
    }

    /** Nominal cicilan per bulan + jadwalnya, untuk Skema Pembayaran di Form Booking. */
    private function skemaAngsuran(TransaksiPenjualan $t): array
    {
        $awal = $t->jadwalAngsurans->first()?->jatuh_tempo;

        return [
            'nominal' => rupiah($this->angsuran->cicilanPerBulan($t), false) . ' /bulan',
            'jadwal'  => $t->tenor . '× setiap tgl ' . ($awal?->day ?? $t->tanggal->day) . ($awal ? ', mulai ' . tanggal($awal, 'j F Y') : ''),
        ];
    }

    /** 98.00 => "98", 82.5 => "82,5" */
    private static function luas($luas): string
    {
        return rtrim(rtrim(number_format((float) $luas, 2, ',', '.'), '0'), ',');
    }

    public function kwitansi(Pembayaran $p): array
    {
        $p->loadMissing('transaksi.konsumen', 'transaksi.kavling', 'transaksi.pembayarans');
        $t = $p->transaksi;

        // Posisi pembayaran s/d kwitansi ini (urut tanggal, lalu id)
        $sampaiIni = $t->pembayarans->filter(fn ($x) => $x->tanggal->lt($p->tanggal) || ($x->tanggal->eq($p->tanggal) && $x->id <= $p->id));
        $pokokSampaiIni = (float) $sampaiIni->whereIn('jenis', \App\Models\TransaksiPenjualan::JENIS_POKOK)->sum('nominal');
        $sisa = max(0, (float) $t->nilai_jual - $pokokSampaiIni);
        $lunas = $sisa <= 0;
        $angsuran = $t->isAngsuran();

        $baris = [
            ['Harga Kavling', rupiah($t->nilai_jual, false), false, false],
            ['Total Pembayaran Harga', rupiah($pokokSampaiIni, false), false, false],
            ['Sisa Tagihan', rupiah($sisa, false), true, false],
            ['Pembayaran', $angsuran ? 'ANGSURAN' : 'CASH', false, false],
        ];
        if ($angsuran) {
            $baris[] = ['Tenor', $t->tenor . ' BULAN', false, false];
            $baris[] = ['Angsuran per Bulan', rupiah($this->angsuran->cicilanPerBulan($t), false), false, false];
        }
        $baris[] = ['Status', $lunas ? 'LUNAS' : 'BELUM LUNAS', false, true];
        $terakhir = count($baris) - 1;

        $keterangan = mb_strtoupper($p->label_jenis) . ' KAVLING ' . $t->kavling->kode_kavling;
        if (in_array($p->jenis, ['reservasi', 'booking'])) {
            $keterangan .= ' (DI LUAR HARGA KAVLING)';
        }

        return [
            'namaProyek'   => mb_strtoupper(Pengaturan::get('nama_proyek', 'Tectona Residen')),
            'alamatProyek' => Pengaturan::get('alamat_proyek') ?? '',
            'tanggal'      => tanggal($p->tanggal, 'j F Y'),
            'noKwitansi'   => $p->kode,
            'nama'         => mb_strtoupper($t->konsumen->nama_lengkap),
            'terbilang'    => ucwords(Terbilang::rupiah((int) round($p->nominal))),
            'keterangan'   => $keterangan,
            'tglMasuk'     => tanggal($p->tanggal, 'd-M-y'),
            'nominal'      => rupiah($p->nominal),
            'metode'       => $p->label_metode . ($p->no_bukti ? ' · ' . $p->no_bukti : ''),
            'baris'        => $baris,
            'kanan'        => [0 => (Pengaturan::get('kota_dokumen') ? Pengaturan::get('kota_dokumen') . ', ' : '') . tanggal($p->tanggal, 'j F Y'), 1 => 'Keuangan', $terakhir => auth()->user()->name ?? 'Admin'],
            'terakhir'     => $terakhir,
            'tinggiTengah' => count($baris) > 6 ? 460 : 640,
            'lunas'        => $lunas,
            'angsuran'     => $angsuran,
            'kembali'      => route('transaksi-penjualan.show', $t),
        ];
    }
}
