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

        // Jumlah kavling yang statusnya "terjual" menentukan tahap mana yang aktif
        $totalTerjual = Kavling::where('status', 'terjual')->count();

        $hargaAktif = 0;

        $tahaps = $tahaps->map(function ($tahap) use ($totalTerjual, &$hargaAktif) {
            if ($totalTerjual > $tahap->unit_sampai) {
                $tahap->status_label = 'Selesai';
            } elseif ($totalTerjual >= $tahap->unit_mulai && $totalTerjual <= $tahap->unit_sampai) {
                $tahap->status_label = 'Aktif';
                $hargaAktif = $tahap->harga_per_m2;
            } else {
                $tahap->status_label = 'Menunggu';
            }
            return $tahap;
        });

        $stats = [
            'total_tahap'      => $tahaps->count(),
            'harga_awal'       => optional($tahaps->sortBy('unit_mulai')->first())->harga_per_m2 ?? 0,
            'harga_tertinggi'  => $tahaps->max('harga_per_m2') ?? 0,
            'total_kavling'    => optional($tahaps->sortByDesc('unit_sampai')->first())->unit_sampai ?? 0,
            'harga_aktif'      => $hargaAktif,
        ];

        // Saran default untuk form Tambah Tahap
        $tahapTerakhir = $tahaps->sortByDesc('unit_sampai')->first();
        $nextTahapNumber = $tahaps->count() + 1;
        $nextUnitMulai   = $tahapTerakhir ? $tahapTerakhir->unit_sampai + 1 : 1;

        return view('skema-harga.index', compact('tahaps', 'stats', 'nextTahapNumber', 'nextUnitMulai'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_tahap'    => 'required|string|max:100',
            'unit_mulai'    => 'required|integer|min:1',
            'unit_sampai'   => 'required|integer|gte:unit_mulai',
            'harga_per_m2'  => 'required|numeric|min:0',
        ]);

        // Pastikan rentang unit tidak bertabrakan dengan tahap lain
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

        return redirect()->route('skema-harga.index')->with('success', 'Tahap harga baru berhasil ditambahkan.');
    }

    public function update(Request $request, SkemaHarga $skemaHarga)
    {
        $validated = $request->validate([
            'nama_tahap'    => 'required|string|max:100',
            'unit_mulai'    => 'required|integer|min:1',
            'unit_sampai'   => 'required|integer|gte:unit_mulai',
            'harga_per_m2'  => 'required|numeric|min:0',
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

        return redirect()->route('skema-harga.index')->with('success', 'Tahap harga berhasil diperbarui.');
    }

    public function destroy(SkemaHarga $skemaHarga)
    {
        $skemaHarga->delete();

        return redirect()->route('skema-harga.index')->with('success', 'Tahap harga berhasil dihapus.');
    }
}