<?php

namespace App\Http\Controllers;

use App\Models\TransaksiPenjualan;
use App\Services\DokumenService;
use App\Support\Formulir;
use Barryvdh\DomPDF\Facade\Pdf;

class DokumenController extends Controller
{
    public function __construct(private DokumenService $dok) {}

    /** Formulir resmi terisi dari transaksi: reservasi, booking, spk, ppjb. */
    public function lihat(TransaksiPenjualan $transaksi, string $jenis)
    {
        if ($r = $this->tolakSpk($transaksi, $jenis)) {
            return $r;
        }

        return view('formulir.cetak', $this->dataCetak($transaksi, $jenis) + [
            'pdf'     => false,
            'kembali' => route('transaksi-penjualan.show', $transaksi),
            'unduh'   => route('dokumen.unduh', [$transaksi, $jenis]),
        ]);
    }

    public function unduh(TransaksiPenjualan $transaksi, string $jenis)
    {
        if ($r = $this->tolakSpk($transaksi, $jenis)) {
            return $r;
        }
        $data = $this->dataCetak($transaksi, $jenis);
        $nama = strtoupper($jenis) . '-' . $transaksi->kode_transaksi . '-' . str($transaksi->konsumen->nama_lengkap)->slug();

        return Pdf::loadView('formulir.cetak', $data + ['pdf' => true])->setPaper($data['kertas'], 'portrait')->download($nama . '.pdf');
    }

    private function dataCetak(TransaksiPenjualan $t, string $jenis): array
    {
        abort_unless(in_array($jenis, Formulir::TRANSAKSI, true), 404);
        $isian = $this->dok->dataFormulir($t, $jenis);
        $tata = Formulir::tata($jenis);
        $tata['judul'] .= ' · ' . $t->konsumen->nama_lengkap;

        return $tata + ['d' => $isian['d'], 'peringatan' => $isian['peringatan'], 'isi' => true];
    }

    /** SPK hanya untuk transaksi yang booking-nya sudah terbayar. */
    private function tolakSpk(TransaksiPenjualan $t, string $jenis)
    {
        if ($jenis === 'spk' && ($alasan = $t->alasanSpkBelumBisa())) {
            return redirect()->route('transaksi-penjualan.show', $t)->with('warning', 'SPK belum bisa dibuat. ' . $alasan);
        }

        return null;
    }
}
