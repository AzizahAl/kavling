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

    /** Data bersama SPK & PPJB (sheet PPJB_OTOMATIS). */
    public function dataPerjanjian(TransaksiPenjualan $t, string $jenis): array
    {
        $t->loadMissing('konsumen', 'kavling', 'pembayarans', 'checklist', 'agen');

        return [
            'jenis'        => $jenis,
            'nomor'        => $this->nomor($t, $jenis),
            't'            => $t,
            'k'            => $t->konsumen,
            'proyek'       => Pengaturan::get('nama_proyek', 'Tectona Residen'),
            'alamatProyek' => Pengaturan::get('alamat_proyek'),
            'pengelola'    => Pengaturan::get('nama_pengelola'),
            'kota'         => Pengaturan::get('kota_dokumen'),
            'statusLegal'  => Pengaturan::get('status_legal_lahan'),
            'tanggal'      => $jenis === 'PPJB' ? ($t->checklist?->ppjb_tanggal ?? today()) : ($t->checklist?->spk_tanggal ?? $t->tanggal),
            'dibayar'      => $t->pokokTerbayar(),
            'sisa'         => $t->sisa(),
            'cicilan'      => $this->angsuran->cicilanPerBulan($t),
            'jadwal'       => $t->isAngsuran() ? $t->jadwalAngsurans()->get() : collect(),
            'terbilangHarga' => Terbilang::rupiah((int) round($t->nilai_jual)),
            'kurang'       => collect(['nama_pengelola' => 'Nama Pengelola / Penjual', 'kota_dokumen' => 'Kota Penandatanganan', 'alamat_proyek' => 'Alamat Proyek'])
                                ->filter(fn ($l, $key) => blank(Pengaturan::get($key)))->values(),
        ];
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
            'metode'       => \App\Models\Pembayaran::METODE[$p->metode] . ($p->no_bukti ? ' · ' . $p->no_bukti : ''),
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
