<?php

namespace App\Http\Controllers;

use App\Models\ChecklistLegal;
use App\Models\Kavling;
use App\Models\TransaksiPenjualan;
use App\Services\DokumenService;
use App\Services\TransaksiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DokumenController extends Controller
{
    public function __construct(private DokumenService $dok) {}

    /** Checklist legal per kavling (sheet CHECKLIST_LEGAL). */
    public function legal(Request $request)
    {
        $kavlings = Kavling::with(['transaksiAktif.konsumen', 'transaksiAktif.checklist'])
            ->orderBy('blok')->orderByRaw('CAST(SUBSTRING(`no`, 2) AS UNSIGNED)')->get();

        $ringkas = collect(ChecklistLegal::ITEM)->map(fn ($label, $item) => $kavlings
            ->filter(fn ($k) => $k->transaksiAktif?->checklist?->{$item . '_status'} === 'selesai')->count());

        return view('dokumen.legal', compact('kavlings', 'ringkas'));
    }

    public function updateLegal(Request $request, ChecklistLegal $checklist, TransaksiService $svc)
    {
        $aturan = ['catatan' => ['nullable', 'string', 'max:1000']];
        foreach (array_keys(ChecklistLegal::ITEM) as $item) {
            $aturan["{$item}_status"] = ['required', Rule::in(['belum', 'proses', 'selesai'])];
            $aturan["{$item}_tanggal"] = ['nullable', 'date', 'before_or_equal:today', "required_if:{$item}_status,selesai"];
        }
        $data = $request->validate($aturan, ['*.required_if' => 'Tanggal wajib diisi bila status Selesai.']);

        $t = $checklist->transaksi;
        if ($t->isBatal()) {
            return back()->with('error', 'Transaksi sudah dibatalkan; checklist tidak bisa diubah.');
        }

        DB::transaction(function () use ($checklist, $data, $svc, $t) {
            $checklist->update($data);
            // PPJB selesai = kavling terjual → status kavling & tahap harga diperbarui
            $svc->sinkronKavling($t->kavling);
        });

        $pesan = "Checklist legal {$t->kavling->kode_kavling} diperbarui.";
        if ($data['ppjb_status'] === 'selesai') {
            $pesan .= ' Kavling kini berstatus Terjual.';
        }

        return back()->with('success', $pesan);
    }

    public function lihat(TransaksiPenjualan $transaksi, string $jenis)
    {
        return view('dokumen.perjanjian', $this->dok->dataPerjanjian($transaksi, $this->jenis($jenis)) + ['pdf' => false]);
    }

    public function unduh(TransaksiPenjualan $transaksi, string $jenis)
    {
        $data = $this->dok->dataPerjanjian($transaksi, $this->jenis($jenis));

        return Pdf::loadView('dokumen.perjanjian', $data + ['pdf' => true])->setPaper('a4', 'portrait')
            ->download($data['jenis'] . '-' . str_replace('/', '-', $data['nomor']) . '-' . str($transaksi->konsumen->nama_lengkap)->slug() . '.pdf');
    }

    private function jenis(string $jenis): string
    {
        abort_unless(in_array($jenis, ['spk', 'ppjb']), 404);

        return strtoupper($jenis);
    }
}
