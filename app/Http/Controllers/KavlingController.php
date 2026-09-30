<?php

namespace App\Http\Controllers;

use App\Models\Kavling;
use App\Models\SkemaHarga;
use Illuminate\Http\Request;

class KavlingController extends Controller
{
    public function index(Request $request)
    {
        $query = Kavling::query()->with('tahap');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('blok')) {
            $query->where('blok', $request->blok);
        }

        $kavlings = $query->orderBy('kode_kavling')->get();

        $stats = [
            'total'     => Kavling::count(),
            'tersedia'  => Kavling::where('status', 'tersedia')->count(),
            'reservasi' => Kavling::where('status', 'reservasi')->count(),
            'booking'   => Kavling::where('status', 'booking')->count(),
            'dp'        => Kavling::where('status', 'dp')->count(),
            'terjual'   => Kavling::where('status', 'terjual')->count(),
        ];

        $bloks = Kavling::select('blok')->distinct()->orderBy('blok')->pluck('blok');

        $tahapAktif = SkemaHarga::aktif();
        $hargaAktif = $tahapAktif->harga_per_m2 ?? 0;

        return view('kavling.index', compact('kavlings', 'stats', 'bloks', 'tahapAktif', 'hargaAktif'));
    }

    public function create()
    {
        $tahapAktif = SkemaHarga::aktif();
        $hargaAktif = $tahapAktif->harga_per_m2 ?? 0;

        return view('kavling.create', compact('tahapAktif', 'hargaAktif'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'blok'       => 'required|string|max:10',
            'nomor'      => 'required|integer|min:1',
            'tipe'       => 'required|in:Prima,Standard,Standard Hook',
            'ukuran'     => 'nullable|string|max:255',
            'luas'       => 'nullable|numeric',
            'harga_jual' => 'nullable|numeric',
            'status'     => 'required|in:tersedia,reservasi,booking,dp,terjual',
        ]);

        $blok  = strtoupper($validated['blok']);
        $nomor = $validated['nomor'];

        $data = $validated;
        $data['blok']         = $blok;
        $data['no']           = $blok . $nomor;
        $data['kode_kavling'] = 'TR-' . $blok . str_pad($nomor, 2, '0', STR_PAD_LEFT);
        unset($data['nomor']);

        if (Kavling::where('kode_kavling', $data['kode_kavling'])->exists()) {
            return back()
                ->withErrors(['nomor' => 'Kavling dengan Blok & Nomor ini sudah ada.'])
                ->withInput();
        }

        // Harga/m² otomatis dari tahap yang sedang berlaku
        $aktif = SkemaHarga::aktif();
        $data['skema_harga_id'] = $aktif?->id;
        $data['harga_per_m2']   = $aktif->harga_per_m2 ?? 0;

        if (!empty($data['luas'])) {
            $data['harga_jual'] = round($data['luas'] * $data['harga_per_m2']);
        }

        Kavling::create($data);

        // Jumlah terjual bisa berubah -> tahap bisa ganti
        Kavling::syncHargaTahap();

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
        // Edit sekarang via modal di halaman index
        return redirect()->route('kavling.index');
    }

    public function update(Request $request, Kavling $kavling)
    {
        $validated = $request->validate([
            'blok'       => 'required|string|max:10',
            'nomor'      => 'required|integer|min:1',
            'tipe'       => 'required|in:Prima,Standard,Standard Hook',
            'ukuran'     => 'nullable|string|max:255',
            'luas'       => 'nullable|numeric',
            'harga_jual' => 'nullable|numeric',
            'status'     => 'required|in:tersedia,reservasi,booking,dp,terjual',
        ]);

        $blok  = strtoupper($validated['blok']);
        $nomor = $validated['nomor'];

        $newNo   = $blok . $nomor;
        $newKode = 'TR-' . $blok . str_pad($nomor, 2, '0', STR_PAD_LEFT);

        if ($newKode !== $kavling->kode_kavling) {
            if (Kavling::where('kode_kavling', $newKode)->where('id', '!=', $kavling->id)->exists()) {
                return back()
                    ->withErrors(['nomor' => 'Kavling dengan Blok & Nomor ini sudah ada.'])
                    ->withInput();
            }
        }

        $data = $validated;
        $data['blok']         = $blok;
        $data['no']           = $newNo;
        $data['kode_kavling'] = $newKode;
        unset($data['nomor']);

        // Kavling yang sudah terjual & tetap terjual = harga dikunci
        $terkunci = $kavling->status === 'terjual' && $data['status'] === 'terjual';

        if (!$terkunci) {
            $aktif = SkemaHarga::aktif();
            $data['skema_harga_id'] = $aktif?->id;
            $data['harga_per_m2']   = $aktif->harga_per_m2 ?? 0;

            if (!empty($data['luas'])) {
                $data['harga_jual'] = round($data['luas'] * $data['harga_per_m2']);
            }
        }

        $kavling->update($data);

        Kavling::syncHargaTahap();

        return redirect()
            ->route('kavling.index')
            ->with('success', 'Kavling berhasil diperbarui.');
    }

    public function destroy(Kavling $kavling)
    {
        $kavling->delete();

        Kavling::syncHargaTahap();

        return redirect()
            ->route('kavling.index')
            ->with('success', 'Kavling berhasil dihapus.');
    }
}