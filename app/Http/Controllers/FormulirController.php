<?php

namespace App\Http\Controllers;

use App\Support\Formulir;
use Barryvdh\DomPDF\Facade\Pdf;

/** Formulir kosong (untuk diisi tangan) dan Marketing Toolkit; bisa dibuka admin & agen. */
class FormulirController extends Controller
{
    public function index()
    {
        $grup = collect(Formulir::DAFTAR)->groupBy(fn ($f) => $f[3], preserveKeys: true);

        return view('formulir.index', compact('grup'));
    }

    public function lihat(string $jenis)
    {
        return view('formulir.cetak', Formulir::tata($jenis) + [
            'pdf'     => false,
            'kembali' => route('formulir.index'),
            'unduh'   => route('formulir.unduh', $jenis),
        ]);
    }

    public function unduh(string $jenis)
    {
        $tata = Formulir::tata($jenis);

        return Pdf::loadView('formulir.cetak', $tata + ['pdf' => true])
            ->setPaper($tata['kertas'], 'portrait')
            ->download(str($tata['judul'])->slug() . '.pdf');
    }
}
