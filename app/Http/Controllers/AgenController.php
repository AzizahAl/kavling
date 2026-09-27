<?php

namespace App\Http\Controllers;

use App\Models\Agen;
use Illuminate\Http\Request;

class AgenController extends Controller
{
    public function index(Request $request)
    {
        $query = Agen::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_agen', 'like', "%{$search}%")
                  ->orWhere('kode_agen', 'like', "%{$search}%");
            });
        }

        match ($request->input('sort')) {
            'closing_desc' => $query->orderByDesc('closing'),
            'komisi_desc'  => $query->orderByDesc('komisi_terhitung'),
            'sisa_desc'    => $query->orderByDesc('sisa_komisi'),
            default        => $query->orderBy('kode_agen'),
        };

        $agens = $query->get();

        $totalAgen     = Agen::count();
        $totalLead     = Agen::sum('lead');
        $totalProspek  = Agen::sum('prospek');
        $totalClosing  = Agen::sum('closing');
        $nilaiPenjualan = Agen::sum('nilai_penjualan');
        $totalKomisi    = Agen::sum('komisi_terhitung');

        $stats = [
            'total_agen'             => $totalAgen,
            'total_lead'              => $totalLead,
            'total_prospek'           => $totalProspek,
            'total_closing'           => $totalClosing,
            'nilai_penjualan_short'   => $this->formatSingkat($nilaiPenjualan),
            'total_komisi_short'      => $this->formatSingkat($totalKomisi),
        ];

        return view('agen.index', compact('agens', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_agen'        => 'required|string|unique:agens,kode_agen',
            'nama_agen'        => 'required|string|max:255',
            'no_hp'            => 'nullable|string|max:20',
            'lead'             => 'nullable|integer|min:0',
            'prospek'          => 'nullable|integer|min:0',
            'closing'          => 'nullable|integer|min:0',
            'nilai_penjualan'  => 'nullable|numeric|min:0',
            'komisi_persen'    => 'nullable|numeric|min:0|max:100',
            'dibayar'          => 'nullable|numeric|min:0',
        ]);

        // Hitung ulang di server, jangan percaya nilai readonly dari client
        $nilai   = $validated['nilai_penjualan'] ?? 0;
        $persen  = $validated['komisi_persen'] ?? 0;
        $dibayar = $validated['dibayar'] ?? 0;

        $terhitung = round($nilai * ($persen / 100), 2);
        $sisa      = $terhitung - $dibayar;

        Agen::create([
            ...$validated,
            'komisi_terhitung' => $terhitung,
            'sisa_komisi'      => $sisa,
        ]);

        return redirect()->route('agen.index')->with('success', 'Agen baru berhasil ditambahkan.');
    }

    public function show(Agen $agen)
    {
        return view('agen.show', compact('agen'));
    }

    public function edit(Agen $agen)
    {
        return view('agen.edit', compact('agen'));
    }

    public function update(Request $request, Agen $agen)
    {
        $validated = $request->validate([
            'kode_agen'        => 'required|string|unique:agens,kode_agen,' . $agen->id,
            'nama_agen'        => 'required|string|max:255',
            'no_hp'            => 'nullable|string|max:20',
            'lead'             => 'nullable|integer|min:0',
            'prospek'          => 'nullable|integer|min:0',
            'closing'          => 'nullable|integer|min:0',
            'nilai_penjualan'  => 'nullable|numeric|min:0',
            'komisi_persen'    => 'nullable|numeric|min:0|max:100',
            'dibayar'          => 'nullable|numeric|min:0',
        ]);

        $nilai   = $validated['nilai_penjualan'] ?? 0;
        $persen  = $validated['komisi_persen'] ?? 0;
        $dibayar = $validated['dibayar'] ?? 0;

        $terhitung = round($nilai * ($persen / 100), 2);
        $sisa      = $terhitung - $dibayar;

        $agen->update([
            ...$validated,
            'komisi_terhitung' => $terhitung,
            'sisa_komisi'      => $sisa,
        ]);

        return redirect()->route('agen.index')->with('success', 'Data agen berhasil diperbarui.');
    }

    public function destroy(Agen $agen)
    {
        $agen->delete();

        return redirect()->route('agen.index')->with('success', 'Data agen berhasil dihapus.');
    }

    private function formatSingkat($angka)
    {
        if ($angka >= 1_000_000_000) {
            return rtrim(rtrim(number_format($angka / 1_000_000_000, 2, ',', '.'), '0'), ',') . ' M';
        }
        if ($angka >= 1_000_000) {
            return rtrim(rtrim(number_format($angka / 1_000_000, 0, ',', '.'), '0'), ',') . ' Juta';
        }
        return number_format($angka, 0, ',', '.');
    }
}