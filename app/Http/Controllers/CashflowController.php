<?php

namespace App\Http\Controllers;

use App\Services\AlokasiService;

class CashflowController extends Controller
{
    public function index(AlokasiService $alokasi)
    {
        $pos = $alokasi->posisiPos();

        return view('cashflow.index', [
            'pos'          => $pos,
            'laba'         => $alokasi->kelayakanLaba($pos),
            'perTransaksi' => $alokasi->perTransaksi(),
        ]);
    }
}
