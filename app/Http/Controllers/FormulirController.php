<?php

namespace App\Http\Controllers;

use App\Support\Formulir;
use Barryvdh\DomPDF\Facade\Pdf;

/** Formulir kosong & materi marketing, dibuka dari Pengaturan Proyek › Formulir (khusus admin). */
class FormulirController extends Controller
{
    public function lihat(string $jenis)
    {
        return view('formulir.cetak', Formulir::tata($jenis) + [
            'pdf'     => false,
            'kembali' => route('proyek.index', ['bagian' => 'formulir']),
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
