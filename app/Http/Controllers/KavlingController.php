<?php

namespace App\Http\Controllers;

use App\Models\Kavling;
use Illuminate\Http\Request;

class KavlingController extends Controller
{
    public function index(Request $request)
    {
        $query = Kavling::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('blok')) {
            $query->where('blok', $request->blok);
        }

        $kavlings = $query->orderBy('kode_kavling')->get();

        // Data untuk stat cards
        $stats = [
            'total'     => Kavling::count(),
            'tersedia'  => Kavling::where('status', 'tersedia')->count(),
            'reservasi' => Kavling::where('status', 'reservasi')->count(),
            'booking'   => Kavling::where('status', 'booking')->count(),
            'dp'        => Kavling::where('status', 'dp')->count(),
            'terjual'   => Kavling::where('status', 'terjual')->count(),
        ];

        // Untuk isi dropdown filter blok
        $bloks = Kavling::select('blok')->distinct()->orderBy('blok')->pluck('blok');

        return view('kavling.index', compact('kavlings', 'stats', 'bloks'));
    }

    public function create()
    {
        return view('kavling.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_kavling' => 'required|string|max:255|unique:kavlings,kode_kavling',
            'blok'         => 'required|string|max:255',
            'no'           => 'required|string|max:255',
            'tipe'         => 'nullable|string|max:255',
            'ukuran'       => 'nullable|string|max:255',
            'luas'         => 'nullable|numeric',
            'harga_per_m2' => 'nullable|numeric',
            'harga_jual'   => 'nullable|numeric',
            'status'       => 'required|in:tersedia,reservasi,booking,dp,terjual',
        ]);

        Kavling::create($validated);

        return redirect()
            ->route('kavling.index')
            ->with('success', 'Kavling berhasil ditambahkan.');
    }

    public function show(Kavling $kavling)
    {
        return view('kavling.show', compact('kavling'));
    }

    public function edit(Kavling $kavling)
    {
        return view('kavling.edit', compact('kavling'));
    }

    public function update(Request $request, Kavling $kavling)
    {
        $validated = $request->validate([
            'kode_kavling' => 'required|string|max:255|unique:kavlings,kode_kavling,' . $kavling->id,
            'blok'         => 'required|string|max:255',
            'no'           => 'required|string|max:255',
            'tipe'         => 'nullable|string|max:255',
            'ukuran'       => 'nullable|string|max:255',
            'luas'         => 'nullable|numeric',
            'harga_per_m2' => 'nullable|numeric',
            'harga_jual'   => 'nullable|numeric',
            'status'       => 'required|in:tersedia,reservasi,booking,dp,terjual',
        ]);

        $kavling->update($validated);

        return redirect()
            ->route('kavling.index')
            ->with('success', 'Kavling berhasil diperbarui.');
    }

    public function destroy(Kavling $kavling)
    {
        $kavling->delete();

        return redirect()
            ->route('kavling.index')
            ->with('success', 'Kavling berhasil dihapus.');
    }
}