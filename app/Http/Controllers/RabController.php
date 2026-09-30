<?php

namespace App\Http\Controllers;

use App\Models\Rab;
use Illuminate\Http\Request;

class RabController extends Controller
{
    public function index(Request $request)
    {
        $query = Rab::query();

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('kategori', 'like', "%{$cari}%")
                  ->orWhere('uraian', 'like', "%{$cari}%");
            });
        }

        if ($request->filled('kategori') && $request->kategori !== 'semua') {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status_realisasi', $request->status);
        }

        $rabs = $query->latest()->get();

        $totalAnggaran  = $rabs->sum('anggaran');
        $totalRealisasi = $rabs->sum('realisasi');
        $totalSelisih   = $totalAnggaran - $totalRealisasi;
        $jumlahSesuai   = $rabs->filter(fn ($r) => $r->status_keuangan === 'sesuai')->count();
        $jumlahBelum    = $rabs->filter(fn ($r) => $r->status_keuangan === 'belum')->count();

        $kategoriList = Rab::select('kategori')->distinct()->orderBy('kategori')->pluck('kategori');

        return view('rab.index', compact(
            'rabs',
            'totalAnggaran',
            'totalRealisasi',
            'totalSelisih',
            'jumlahSesuai',
            'jumlahBelum',
            'kategoriList'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kategori'         => 'required|string|max:100',
            'uraian'           => 'required|string|max:255',
            'anggaran'         => 'required|numeric|min:0',
            'status_realisasi' => 'required|in:belum_direalisasikan,sudah_direalisasikan',
            'catatan'          => 'nullable|string',
        ]);

        $data['realisasi'] = 0;

        Rab::create($data);

        return redirect()->route('rab.index')->with('success', 'Data RAB berhasil ditambahkan.');
    }

    public function update(Request $request, Rab $rab)
    {
        $data = $request->validate([
            'kategori'         => 'required|string|max:100',
            'uraian'           => 'required|string|max:255',
            'anggaran'         => 'required|numeric|min:0',
            'realisasi'        => 'nullable|numeric|min:0',
            'status_realisasi' => 'required|in:belum_direalisasikan,sudah_direalisasikan',
            'catatan'          => 'nullable|string',
        ]);

        $data['realisasi'] = $data['realisasi'] ?? 0;

        $rab->update($data);

        return redirect()->route('rab.index')->with('success', 'Data RAB berhasil diperbarui.');
    }

    public function destroy(Rab $rab)
    {
        $rab->delete();

        return redirect()->route('rab.index')->with('success', 'Data RAB berhasil dihapus.');
    }

    public function verifikasi(Rab $rab)
    {
        $rab->update([
            'realisasi'        => $rab->anggaran,
            'status_realisasi' => 'sudah_direalisasikan',
        ]);

        return redirect()->route('rab.index')->with('success', 'RAB ditandai sesuai realisasi.');
    }
}