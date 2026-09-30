<?php

namespace App\Http\Controllers;

use App\Models\TransaksiPenjualan;
use App\Services\AngsuranService;
use Illuminate\Http\Request;

/** Jadwal angsuran & piutang seluruh transaksi aktif yang belum lunas. */
class AngsuranController extends Controller
{
    public function index(Request $request, AngsuranService $angsuran)
    {
        $baris = TransaksiPenjualan::aktif()->where('status', '!=', 'lunas')
            ->with(['konsumen', 'kavling', 'agen', 'pembayarans', 'jadwalAngsurans'])
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis_pembayaran', $request->jenis))
            ->get()
            ->map(function ($t) use ($angsuran) {
                $r = $angsuran->ringkasan($t);

                return (object) [
                    't'          => $t,
                    'sisa'       => $t->sisa(),
                    'tunggakan'  => $r['tunggakan'],
                    'telat'      => $r['jumlah_telat'],
                    'hari_telat' => $r['hari_telat_maks'],
                    'berikut'    => $r['berikutnya'],
                    'lunas_ke'   => $r['cicilan_lunas'],
                    'jumlah'     => $r['jadwal']->count(),
                ];
            });

        $filter = match ($request->status) {
            'terlambat' => $baris->where('telat', '>', 0),
            'minggu'    => $baris->filter(fn ($b) => $b->berikut && $b->berikut->jatuh_tempo->between(today(), today()->addDays(7))),
            default     => $baris,
        };
        $filter = $filter->when($request->filled('cari'), fn ($c) => $c->filter(fn ($b) => str_contains(
            mb_strtolower("{$b->t->kode_transaksi} {$b->t->konsumen->nama_lengkap} {$b->t->kavling->kode_kavling}"), mb_strtolower($request->cari))));

        return view('angsuran.index', [
            'baris' => $filter->sortByDesc('hari_telat')->values(),
            'stats' => [
                'piutang'   => $baris->sum('sisa'),
                'tunggakan' => $baris->sum('tunggakan'),
                'terlambat' => $baris->where('telat', '>', 0)->count(),
                'minggu'    => $baris->filter(fn ($b) => $b->berikut && $b->berikut->jatuh_tempo->between(today(), today()->addDays(7)))->sum(fn ($b) => $b->berikut->sisa),
            ],
        ]);
    }
}
