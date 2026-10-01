<?php

namespace App\Http\Controllers;

use App\Models\Kavling;
use App\Models\TransaksiPenjualan;
use App\Services\HargaService;

/** Tahap harga dibentuk dari Pengaturan Proyek; halaman ini untuk memantau. */
class SkemaHargaController extends Controller
{
    public function index(HargaService $harga)
    {
        $terjual = $harga->jumlahTerjual();
        $aktif = $harga->nomorTahapAktif($terjual);
        $perUnit = max(1, (int) \App\Services\Pengaturan::get('unit_per_kenaikan', 1));

        // Transaksi yang terkunci di tiap tahap
        $perTahap = TransaksiPenjualan::berjalan()->selectRaw('harga_per_m2, COUNT(*) n')->groupBy('harga_per_m2')->pluck('n', 'harga_per_m2')
            ->mapWithKeys(fn ($n, $h) => [(int) $h => $n]);

        return view('skema-harga.index', [
            'tahap'       => $harga->daftarTahap(),
            'aktif'       => $aktif,
            'terjual'     => $terjual,
            'hargaAktif'  => $harga->hargaAktif(),
            'totalKavling' => Kavling::count(),
            'menujuNaik'  => $aktif < count($harga->daftarTahap()) ? $aktif * $perUnit - $terjual : null,
            'perTahap'    => $perTahap,
        ]);
    }
}
