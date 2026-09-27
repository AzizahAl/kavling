<?php

namespace App\Http\Controllers;

use App\Models\Agen;
use App\Models\Kavling;
use App\Models\Konsumen;
use App\Models\TransaksiPenjualan;
use Illuminate\Http\Request;

class TransaksiPenjualanController extends Controller
{
    public function index(Request $request)
    {
        $query = TransaksiPenjualan::with(['konsumen', 'kavling', 'agen']);

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('kode_transaksi', 'like', "%{$cari}%")
                  ->orWhereHas('konsumen', fn($qq) => $qq->where('nama', 'like', "%{$cari}%"));
            });
        }

        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis') && $request->jenis !== 'semua') {
            $query->where('jenis_pembayaran', $request->jenis);
        }

        if ($request->filled('periode')) {
            $query->whereDate('tanggal', $request->periode);
        }

        $transaksis = $query->orderByDesc('tanggal')->paginate(10)->withQueryString();

        $stats = [
            'total' => TransaksiPenjualan::count(),
            'reservasi' => TransaksiPenjualan::where('status', 'reservasi')->count(),
            'booking' => TransaksiPenjualan::where('status', 'booking')->count(),
            'dp' => TransaksiPenjualan::where('status', 'dp')->count(),
            'total_nilai_jual' => TransaksiPenjualan::sum('nilai_jual'),
            'total_bayar' => TransaksiPenjualan::sum('total_bayar'),
            'total_sisa' => TransaksiPenjualan::sum('sisa_pembayaran'),
            'lunas' => TransaksiPenjualan::where('status', 'lunas')->count(),
        ];

        // Kavling yang masih tersedia, buat dropdown di modal (auto-fill info kavling)
        $kavlings = Kavling::where('status', 'tersedia')
            ->orWhereHas('transaksiPenjualans')
            ->get(['id', 'kode_kavling', 'blok', 'no', 'tipe', 'luas', 'harga_per_m2', 'harga_jual']);

        $agens = Agen::orderBy('nama_agen')->get(['id', 'nama_agen']);

        $kodeBaru = TransaksiPenjualan::generateKodeTransaksi();

        return view('transaksi-penjualan.index', compact(
            'transaksis', 'stats', 'kavlings', 'agens', 'kodeBaru'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'status' => 'required|in:reservasi,booking,dp,lunas',
            'konsumen_id' => 'required|exists:konsumens,id',
            'kavling_id' => 'required|exists:kavlings,id',
            'agen_id' => 'nullable|exists:agens,id',
            'jenis_pembayaran' => 'required|in:cash,angsuran',
            'nilai_jual' => 'required|numeric|min:0',
            'tenor' => 'nullable|required_if:jenis_pembayaran,angsuran|integer|min:1',
            'nominal_dp' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string',
        ]);

        $nominalDp = $validated['jenis_pembayaran'] === 'angsuran'
            ? ($validated['nominal_dp'] ?? 0)
            : $validated['nilai_jual']; // cash langsung lunas

        $totalBayar = $nominalDp;
        $sisa = max($validated['nilai_jual'] - $totalBayar, 0);

        TransaksiPenjualan::create([
            'kode_transaksi' => TransaksiPenjualan::generateKodeTransaksi(),
            'tanggal' => $validated['tanggal'],
            'status' => $validated['status'],
            'konsumen_id' => $validated['konsumen_id'],
            'kavling_id' => $validated['kavling_id'],
            'agen_id' => $validated['agen_id'] ?? null,
            'jenis_pembayaran' => $validated['jenis_pembayaran'],
            'nilai_jual' => $validated['nilai_jual'],
            'tenor' => $validated['jenis_pembayaran'] === 'angsuran' ? $validated['tenor'] : null,
            'nominal_dp' => $nominalDp,
            'total_bayar' => $totalBayar,
            'sisa_pembayaran' => $sisa,
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()->route('transaksi-penjualan.index')
            ->with('success', 'Transaksi berhasil disimpan.');
    }

    // Dipanggil via fetch() di modal "Cari Konsumen"
    public function searchKonsumen(Request $request)
    {
        $q = $request->get('q', '');

        $hasil = Konsumen::where('nama', 'like', "%{$q}%")
            ->orWhere('kode_konsumen', 'like', "%{$q}%")
            ->orWhere('telepon', 'like', "%{$q}%")
            ->limit(5)
            ->get(['id', 'kode_konsumen', 'nama', 'telepon']);

        return response()->json($hasil);
    }
}