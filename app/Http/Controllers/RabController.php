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

        $totalAnggaran = $rabs->sum('anggaran');
        $totalRealisasi = $rabs->sum('realisasi');
        $totalSelisih = $totalAnggaran - $totalRealisasi;

        $jumlahSesuai = $rabs->filter(fn ($r) => $r->status_keuangan === 'sesuai')->count();
        $jumlahBelum = $rabs->count() - $jumlahSesuai;

        $kategoriList = Rab::select('kategori')->distinct()->pluck('kategori');

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
            'kategori'          => 'required|string|max:100',
            'uraian'            => 'required|string|max:255',
            'anggaran'          => 'required|numeric|min:0',
            'status_realisasi'  => 'required|in:belum_direalisasikan,sudah_direalisasikan',
            'catatan'           => 'nullable|string',
        ]);

        Rab::create($data);

        return back()->with('success', 'RAB berhasil ditambahkan.');
    }

    public function update(Request $request, Rab $rab)
    {
        $data = $request->validate([
            'kategori'          => 'required|string|max:100',
            'uraian'            => 'required|string|max:255',
            'anggaran'          => 'required|numeric|min:0',
            'realisasi'         => 'nullable|numeric|min:0',
            'status_realisasi'  => 'required|in:belum_direalisasikan,sudah_direalisasikan',
            'catatan'           => 'nullable|string',
        ]);

        $rab->update($data);

        return back()->with('success', 'RAB berhasil diperbarui.');
    }

    public function destroy(Rab $rab)
    {
        $rab->delete();

        return back()->with('success', 'RAB berhasil dihapus.');
    }

    // Tombol centang hijau di kolom Aksi: tandai realisasi = anggaran
    public function verifikasi(Rab $rab)
    {
        $rab->update([
            'realisasi'        => $rab->anggaran,
            'status_realisasi' => 'sudah_direalisasikan',
        ]);

        return back()->with('success', 'RAB berhasil diverifikasi.');
    }
}