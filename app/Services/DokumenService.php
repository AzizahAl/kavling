<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Support\Terbilang;

/** Data cetak dokumen (kwitansi, SPK, PPJB) dari satu sumber: transaksi & pembayaran. */
class DokumenService
{
    public function __construct(private AngsuranService $angsuran) {}

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
