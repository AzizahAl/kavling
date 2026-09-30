<?php

namespace App\Http\Controllers;

use App\Models\Kavling;
use App\Models\SkemaHarga;
use Illuminate\Http\Request;

class SkemaHargaController extends Controller
{
    public function index()
    {
        $tahaps = SkemaHarga::orderBy('unit_mulai')->get();

        $totalTerjual = Kavling::where('status', 'terjual')->count();
        $tahapAktif   = SkemaHarga::aktif();
        $hargaAktif   = $tahapAktif->harga_per_m2 ?? 0;

        $tahaps = $tahaps->map(function ($tahap) use ($totalTerjual, $tahapAktif) {
            if ($tahapAktif && $tahap->id === $tahapAktif->id) {
                $tahap->status_label = 'Aktif';
            } elseif ($tahap->unit_mulai > $totalTerjual) {
                $tahap->status_label = 'Menunggu';
            } else {
                $tahap->status_label = 'Selesai';
            }
            return $tahap;
        });

        $stats = [
            'total_tahap'     => $tahaps->count(),
            'harga_awal'      => optional($tahaps->sortBy('unit_mulai')->first())->harga_per_m2 ?? 0,
            'harga_tertinggi' => $tahaps->max('harga_per_m2') ?? 0,
            'total_kavling'   => optional($tahaps->sortByDesc('unit_sampai')->first())->unit_sampai ?? 0,
            'harga_aktif'     => $hargaAktif,
        ];

        $tahapTerakhir   = $tahaps->sortByDesc('unit_sampai')->first();
        $nextTahapNumber = $tahaps->count() + 1;
        $nextUnitMulai   = $tahapTerakhir ? $tahapTerakhir->unit_sampai + 1 : 0;

        return view('skema-harga.index', compact('tahaps', 'stats', 'nextTahapNumber', 'nextUnitMulai'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_tahap'   => 'required|string|max:100',
            'unit_mulai'   => 'required|integer|min:0',
            'unit_sampai'  => 'required|integer|gte:unit_mulai',
            'harga_per_m2' => 'required|numeric|min:0',
        ]);

        $bentrok = SkemaHarga::where(function ($q) use ($validated) {
            $q->whereBetween('unit_mulai', [$validated['unit_mulai'], $validated['unit_sampai']])
              ->orWhereBetween('unit_sampai', [$validated['unit_mulai'], $validated['unit_sampai']])
              ->orWhere(function ($q2) use ($validated) {
                  $q2->where('unit_mulai', '<=', $validated['unit_mulai'])
                     ->where('unit_sampai', '>=', $validated['unit_sampai']);
              });
        })->exists();

        if ($bentrok) {
            return back()
                ->withErrors(['unit_mulai' => 'Rentang unit bertabrakan dengan tahap harga lainnya.'])
                ->withInput();
        }

        SkemaHarga::create($validated);
        Kavling::syncHargaTahap();

        return redirect()->route('skema-harga.index')->with('success', 'Tahap harga baru berhasil ditambahkan.');
    }

    public function update(Request $request, SkemaHarga $skemaHarga)
    {
        $validated = $request->validate([
            'nama_tahap'   => 'required|string|max:100',
            'unit_mulai'   => 'required|integer|min:0',
            'unit_sampai'  => 'required|integer|gte:unit_mulai',
            'harga_per_m2' => 'required|numeric|min:0',
        ]);

        $bentrok = SkemaHarga::where('id', '!=', $skemaHarga->id)
            ->where(function ($q) use ($validated) {
                $q->whereBetween('unit_mulai', [$validated['unit_mulai'], $validated['unit_sampai']])
                  ->orWhereBetween('unit_sampai', [$validated['unit_mulai'], $validated['unit_sampai']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('unit_mulai', '<=', $validated['unit_mulai'])
                         ->where('unit_sampai', '>=', $validated['unit_sampai']);
                  });
            })->exists();

        if ($bentrok) {
            return back()
                ->withErrors(['unit_mulai' => 'Rentang unit bertabrakan dengan tahap harga lainnya.'])
                ->withInput();
        }

        $skemaHarga->update($validated);
        Kavling::syncHargaTahap();

        return redirect()->route('skema-harga.index')->with('success', 'Tahap harga berhasil diperbarui.');
    }

    public function destroy(SkemaHarga $skemaHarga)
    {
        $skemaHarga->delete();
        Kavling::syncHargaTahap();

        return redirect()->route('skema-harga.index')->with('success', 'Tahap harga berhasil dihapus.');
    }
}