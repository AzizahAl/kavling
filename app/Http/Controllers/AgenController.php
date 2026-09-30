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

        // Kode agen berikutnya, buat ditampilkan di form Tambah (preview doang)
        $nextKodeAgen = $this->generateKodeAgen();

        return view('agen.index', compact('agens', 'stats', 'nextKodeAgen'));
    }

    /**
     * Endpoint AJAX buat ambil kode agen berikutnya secara real-time,
     * dipanggil tiap kali modal Tambah Agen dibuka biar gak pernah stale.
     */
    public function nextKode()
    {
        return response()->json([
            'kode' => $this->generateKodeAgen(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_agen'        => 'required|string|max:255',
            'no_hp'            => 'nullable|string|max:20',
            'lead'             => 'nullable|integer|min:0',
            'prospek'          => 'nullable|integer|min:0',
            'closing'          => 'nullable|integer|min:0',
            'nilai_penjualan'  => 'nullable|numeric|min:0',
            'komisi_persen'    => 'nullable|numeric|min:0|max:100',
            'dibayar'          => 'nullable|numeric|min:0',
        ]);

        // Kode agen di-generate di server, bukan dari input form
        $kodeAgen = $this->generateKodeAgen();

        $nilai   = $validated['nilai_penjualan'] ?? 0;
        $persen  = $validated['komisi_persen'] ?? 0;
        $dibayar = $validated['dibayar'] ?? 0;

        $terhitung = round($nilai * ($persen / 100), 2);
        $sisa      = $terhitung - $dibayar;

        Agen::create([
            ...$validated,
            'kode_agen'        => $kodeAgen,
            'komisi_terhitung' => $terhitung,
            'sisa_komisi'      => $sisa,
        ]);

        return redirect()->route('agen.index')->with('success', "Agen baru ({$kodeAgen}) berhasil ditambahkan.");
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
            'nama_agen'        => 'required|string|max:255',
            'no_hp'            => 'nullable|string|max:20',
            'lead'             => 'nullable|integer|min:0',
            'prospek'          => 'nullable|integer|min:0',
            'closing'          => 'nullable|integer|min:0',
            'nilai_penjualan'  => 'nullable|numeric|min:0',
            'komisi_persen'    => 'nullable|numeric|min:0|max:100',
            'dibayar'          => 'nullable|numeric|min:0',
        ]);

        // kode_agen TIDAK diubah lagi setelah dibuat pertama kali

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

    /**
     * Generate kode agen berikutnya, format: AG-001, AG-002, dst.
     */
    private function generateKodeAgen()
    {
        // Ambil semua kode_agen yang ada, terus cari angka terbesarnya secara manual di PHP
        // (biar gak bergantung sama sintaks SQL yang beda-beda tiap database)
        $semuaKode = Agen::pluck('kode_agen');

        $angkaTerbesar = 0;
        foreach ($semuaKode as $kode) {
            $angka = (int) substr($kode, 3); // ambil bagian setelah "AG-"
            if ($angka > $angkaTerbesar) {
                $angkaTerbesar = $angka;
            }
        }

        $nextNumber = $angkaTerbesar + 1;

        return 'AG-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
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