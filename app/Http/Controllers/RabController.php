<?php

namespace App\Http\Controllers;

use App\Models\AlokasiKas;
use App\Models\Rab;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RabController extends Controller
{
    public function index(Request $request)
    {
        $semua = Rab::denganRealisasi()->with('kasKeluar')->orderBy('id')->get();

        $rabs = $semua
            ->when($request->filled('kategori'), fn ($c) => $c->where('kategori', $request->kategori))
            ->when($request->filled('status'), fn ($c) => $c->filter(fn ($r) => $r->status === $request->status))
            ->when($request->filled('cari'), fn ($c) => $c->filter(fn ($r) => str_contains(mb_strtolower("{$r->kategori} {$r->uraian}"), mb_strtolower($request->cari))));

        return view('rab.index', [
            'rabs'           => $rabs,
            'totalAnggaran'  => (float) $semua->sum('anggaran'),
            'totalRealisasi' => (float) $semua->sum('realisasi_nilai'),
            'jumlahStatus'   => $semua->countBy('status'),
            'kategoriList'   => $semua->pluck('kategori')->unique()->values(),
        ]);
    }

    public function store(Request $request)
    {
        $rab = Rab::create($this->validasi($request));

        return back()->with('success', "Item RAB \"{$rab->uraian}\" ditambahkan.");
    }

    public function update(Request $request, Rab $rab)
    {
        $rab->update($this->validasi($request));

        return back()->with('success', "Item RAB \"{$rab->uraian}\" diperbarui.");
    }

    public function destroy(Rab $rab)
    {
        if ($rab->kasKeluar()->exists()) {
            return back()->with('error', "\"{$rab->uraian}\" sudah memiliki realisasi di Kas Proyek sehingga tidak bisa dihapus.");
        }
        $rab->delete();

        return back()->with('success', 'Item RAB dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'kategori' => ['required', 'string', 'max:100'],
            'pos'      => ['required', Rule::in(array_keys(AlokasiKas::POS))],
            'uraian'   => ['required', 'string', 'max:255'],
            'anggaran' => ['nullable', 'numeric', 'min:0'],
            'catatan'  => ['nullable', 'string', 'max:1000'],
        ], [], ['pos' => 'pos alokasi']);
    }
}
