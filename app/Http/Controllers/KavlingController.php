<?php

namespace App\Http\Controllers;

use App\Models\Kavling;
use App\Services\HargaService;
use App\Services\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KavlingController extends Controller
{
    public function index(Request $request, HargaService $harga)
    {
        $kavlings = Kavling::with(['tahap', 'transaksiAktif' => fn ($q) => $q->with('konsumen', 'agen')->denganRingkasan()])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('blok'), fn ($q) => $q->where('blok', $request->blok))
            ->when($request->filled('tipe'), fn ($q) => $q->where('tipe', $request->tipe))
            ->when($request->filled('cari'), fn ($q) => $q->where(fn ($w) => $w
                ->where('kode_kavling', 'like', "%{$request->cari}%")
                ->orWhere('tipe', 'like', "%{$request->cari}%")))
            ->orderBy('blok')->orderByRaw('CAST(SUBSTRING(`no`, 2) AS UNSIGNED)')
            ->get();

        $jumlah = Kavling::selectRaw('status, COUNT(*) n')->groupBy('status')->pluck('n', 'status');

        return view('kavling.index', [
            'kavlings'   => $kavlings,
            'jumlah'     => $jumlah,
            'total'      => $jumlah->sum(),
            'bloks'      => Kavling::distinct()->orderBy('blok')->pluck('blok'),
            'tahapAktif' => $harga->tahapAktif(),
            'hargaAktif' => $harga->hargaAktif(),
        ]);
    }

    public function show(Kavling $kavling)
    {
        $kavling->load(['tahap', 'transaksiPenjualans' => fn ($q) => $q->with('konsumen', 'agen', 'checklist')->denganRingkasan()->latest('tanggal')]);

        return view('kavling.show', compact('kavling'));
    }

    public function store(Request $request, HargaService $harga)
    {
        $data = $this->validasi($request);
        Kavling::create($data + ['status' => 'tersedia']);
        $harga->sinkronHargaKavling();

        return redirect()->route('kavling.index')->with('success', "Kavling {$data['kode_kavling']} berhasil ditambahkan.");
    }

    public function update(Request $request, Kavling $kavling, HargaService $harga)
    {
        $data = $this->validasi($request, $kavling);

        // Blok/nomor/luas tidak boleh diubah saat kavling sedang bertransaksi (harga sudah terkunci di transaksi)
        if ($kavling->transaksiAktif()->exists()) {
            $berubah = $data['kode_kavling'] !== $kavling->kode_kavling || (float) $data['luas'] !== (float) $kavling->luas;
            if ($berubah) {
                return back()->withInput()->with('error', "Kavling {$kavling->kode_kavling} sedang bertransaksi. Blok, nomor, dan luas tidak bisa diubah.");
            }
        }

        $kavling->update($data);
        $harga->sinkronHargaKavling();

        return redirect()->back()->with('success', "Kavling {$kavling->kode_kavling} berhasil diperbarui.");
    }

    public function destroy(Kavling $kavling)
    {
        if ($kavling->transaksiPenjualans()->exists()) {
            return back()->with('error', "Kavling {$kavling->kode_kavling} sudah memiliki riwayat transaksi sehingga tidak bisa dihapus.");
        }

        $kavling->delete();

        return redirect()->route('kavling.index')->with('success', "Kavling {$kavling->kode_kavling} berhasil dihapus.");
    }

    /** Kode kavling dibentuk dari blok & nomor: TR-A01. Harga & status diatur otomatis, bukan dari form. */
    private function validasi(Request $request, ?Kavling $kavling = null): array
    {
        $v = $request->validate([
            'blok'    => ['required', 'alpha', 'max:3'],
            'nomor'   => ['required', 'integer', 'min:1', 'max:99'],
            'tipe'    => ['required', Rule::in(Kavling::TIPE)],
            'ukuran'  => ['nullable', 'string', 'max:100'],
            'luas'    => ['nullable', 'numeric', 'min:1', 'max:100000'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ], [], ['nomor' => 'nomor kavling']);

        $blok = strtoupper($v['blok']);
        $kode = sprintf('%s-%s%02d', Pengaturan::get('prefix_kavling', 'TR'), $blok, $v['nomor']);

        $bentrok = Kavling::where('kode_kavling', $kode)->when($kavling, fn ($q) => $q->whereKeyNot($kavling->id))->exists();
        if ($bentrok) {
            throw \Illuminate\Validation\ValidationException::withMessages(['nomor' => "Kavling {$kode} sudah ada."]);
        }

        return [
            'kode_kavling' => $kode,
            'blok'         => $blok,
            'no'           => $blok . $v['nomor'],
            'tipe'         => $v['tipe'],
            'ukuran'       => $v['ukuran'] ?? null,
            'luas'         => $v['luas'] ?? null,
            'catatan'      => $v['catatan'] ?? null,
        ];
    }
}
