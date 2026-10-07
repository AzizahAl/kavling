<?php

namespace App\Http\Controllers;

use App\Models\Kavling;
use App\Models\SkemaHarga;
use App\Models\TransaksiPenjualan;
use App\Services\HargaService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Tabel tahap harga dikelola di sini; setiap perubahan langsung memperbarui harga kavling tersedia. */
class SkemaHargaController extends Controller
{
    public function index(HargaService $harga)
    {
        $bertransaksi = $harga->jumlahBertransaksi();

        // Transaksi (tidak batal) yang harganya terkunci di tiap tahap
        $perTahap = TransaksiPenjualan::where('status', '!=', 'batal')->whereNotNull('skema_harga_id')
            ->selectRaw('skema_harga_id, COUNT(*) n')->groupBy('skema_harga_id')->pluck('n', 'skema_harga_id');

        return view('skema-harga.index', [
            'tahap'        => $harga->daftarTahap(),
            'aktif'        => $harga->nomorTahapAktif($bertransaksi),
            'bertransaksi' => $bertransaksi,
            'hargaAktif'   => $harga->hargaAktif(),
            'totalKavling' => Kavling::count(),
            'menujuNaik'   => $harga->menujuNaik($bertransaksi),
            'perTahap'     => $perTahap,
        ]);
    }

    public function store(Request $request, HargaService $harga)
    {
        $data = $this->validasi($request);
        SkemaHarga::create($data);
        $harga->sinkronHargaKavling();

        return redirect()->route('skema-harga.index')->with('success', "{$data['nama_tahap']} berhasil ditambahkan. Harga kavling tersedia sudah diperbarui.");
    }

    public function update(Request $request, SkemaHarga $skemaHarga, HargaService $harga)
    {
        $skemaHarga->update($this->validasi($request, $skemaHarga));
        $harga->sinkronHargaKavling();

        return redirect()->route('skema-harga.index')->with('success', "{$skemaHarga->nama_tahap} berhasil diperbarui. Harga kavling tersedia sudah diperbarui.");
    }

    public function destroy(SkemaHarga $skemaHarga, HargaService $harga)
    {
        if (TransaksiPenjualan::where('skema_harga_id', $skemaHarga->id)->exists()) {
            return back()->with('error', "{$skemaHarga->nama_tahap} sudah dipakai transaksi sehingga tidak bisa dihapus.");
        }

        $skemaHarga->delete();
        $harga->sinkronHargaKavling();

        return redirect()->route('skema-harga.index')->with('success', "{$skemaHarga->nama_tahap} berhasil dihapus. Harga kavling tersedia sudah diperbarui.");
    }

    /** Rentang unit tidak boleh tumpang tindih dengan tahap lain. */
    private function validasi(Request $request, ?SkemaHarga $skema = null): array
    {
        $data = $request->validate([
            'nama_tahap'   => ['required', 'string', 'max:100'],
            'unit_mulai'   => ['required', 'integer', 'min:0'],
            'unit_sampai'  => ['required', 'integer', 'gte:unit_mulai'],
            'harga_per_m2' => ['required', 'integer', 'min:1'],
        ], [
            'unit_sampai.gte' => 'Unit sampai tidak boleh lebih kecil dari unit mulai.',
        ], [
            'nama_tahap' => 'nama tahap', 'unit_mulai' => 'unit mulai', 'unit_sampai' => 'unit sampai', 'harga_per_m2' => 'harga/m²',
        ]);

        $bentrok = SkemaHarga::when($skema, fn ($q) => $q->whereKeyNot($skema->id))
            ->where('unit_mulai', '<=', $data['unit_sampai'])
            ->where('unit_sampai', '>=', $data['unit_mulai'])
            ->orderBy('unit_mulai')->first();

        if ($bentrok) {
            $pesan = "Rentang bertumpang tindih dengan {$bentrok->nama_tahap} ({$bentrok->unit_mulai}–{$bentrok->unit_sampai} unit).";
            // Pesan ditaruh di kolom yang masuk ke rentang tahap lain
            $kolom = $data['unit_mulai'] >= $bentrok->unit_mulai && $data['unit_mulai'] <= $bentrok->unit_sampai ? 'unit_mulai' : 'unit_sampai';
            throw ValidationException::withMessages([$kolom => $pesan]);
        }

        return $data;
    }
}
