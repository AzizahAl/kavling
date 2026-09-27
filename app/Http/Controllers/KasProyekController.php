<?php

namespace App\Http\Controllers;

use App\Models\KasTransaksi;
use Illuminate\Http\Request;

class KasProyekController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua data terurut kronologis untuk hitung saldo berjalan
        $semua = KasTransaksi::orderBy('tanggal')->orderBy('id')->get();

        $saldoBerjalan = 0;
        $semua = $semua->map(function ($item) use (&$saldoBerjalan) {
            $saldoBerjalan += $item->jenis === 'masuk' ? $item->nominal : -$item->nominal;
            $item->saldo_berjalan = $saldoBerjalan;
            return $item;
        });

        $totalMasuk = $semua->where('jenis', 'masuk')->sum('nominal');
        $totalKeluar = $semua->where('jenis', 'keluar')->sum('nominal');
        $saldoProyek = $totalMasuk - $totalKeluar;

        // Terapkan filter/pencarian untuk tampilan tabel (saldo_berjalan tetap terjaga)
        $transaksis = $semua;

        if ($request->filled('tab') && in_array($request->tab, ['masuk', 'keluar'])) {
            $transaksis = $transaksis->where('jenis', $request->tab);
        }

        if ($request->filled('cari')) {
            $cari = strtolower($request->cari);
            $transaksis = $transaksis->filter(function ($item) use ($cari) {
                return str_contains(strtolower($item->uraian), $cari)
                    || str_contains(strtolower($item->kode), $cari)
                    || str_contains(strtolower($item->kategori), $cari);
            });
        }

        if ($request->filled('kategori') && $request->kategori !== 'semua') {
            $transaksis = $transaksis->where('kategori', $request->kategori);
        }

        $transaksis = $transaksis->values();

        $kategoriList = KasTransaksi::select('kategori')->distinct()->pluck('kategori');

        $tahunIni = now()->year;
        $nextKodeMasuk = 'INC-' . $tahunIni . '-' . str_pad(
            (KasTransaksi::where('jenis', 'masuk')->whereYear('tanggal', $tahunIni)->count() + 1),
            4, '0', STR_PAD_LEFT
        );
        $nextKodeKeluar = 'EXP-' . $tahunIni . '-' . str_pad(
            (KasTransaksi::where('jenis', 'keluar')->whereYear('tanggal', $tahunIni)->count() + 1),
            4, '0', STR_PAD_LEFT
        );

        return view('kas-proyek.index', compact(
            'transaksis',
            'totalMasuk',
            'totalKeluar',
            'saldoProyek',
            'kategoriList',
            'nextKodeMasuk',
            'nextKodeKeluar'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal'  => 'required|date',
            'kode'     => 'required|string|max:50|unique:kas_transaksis,kode',
            'kategori' => 'required|string|max:100',
            'jenis'    => 'required|in:masuk,keluar',
            'uraian'   => 'required|string|max:255',
            'nominal'  => 'required|numeric|min:0',
            'sumber'   => 'nullable|string|max:255',
            'catatan'  => 'nullable|string',
        ]);

        KasTransaksi::create($data);

        return back()->with('success', 'Transaksi kas berhasil ditambahkan.');
    }

    public function update(Request $request, KasTransaksi $kasTransaksi)
    {
        $data = $request->validate([
            'tanggal'  => 'required|date',
            'kode'     => 'required|string|max:50|unique:kas_transaksis,kode,' . $kasTransaksi->id,
            'kategori' => 'required|string|max:100',
            'jenis'    => 'required|in:masuk,keluar',
            'uraian'   => 'required|string|max:255',
            'nominal'  => 'required|numeric|min:0',
            'sumber'   => 'nullable|string|max:255',
            'catatan'  => 'nullable|string',
        ]);

        $kasTransaksi->update($data);

        return back()->with('success', 'Transaksi kas berhasil diperbarui.');
    }

    public function destroy(KasTransaksi $kasTransaksi)
    {
        $kasTransaksi->delete();

        return back()->with('success', 'Transaksi kas berhasil dihapus.');
    }
}