<?php

namespace App\Http\Controllers;

use App\Models\KasTransaksi;
use App\Models\Rab;
use App\Services\KasService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KasProyekController extends Controller
{
    public function __construct(private KasService $kas) {}

    public function index(Request $request)
    {
        // Saldo berjalan dihitung dari seluruh data urut kronologis, baru kemudian difilter.
        $saldo = 0;
        $semua = KasTransaksi::with('rab')->orderBy('tanggal')->orderBy('id')->get()
            ->each(function ($k) use (&$saldo) {
                $saldo += $k->jenis === 'masuk' ? (float) $k->nominal : -(float) $k->nominal;
                $k->saldo_berjalan = $saldo;
            });

        $cari = mb_strtolower(trim((string) $request->cari));
        $hasil = $semua
            ->when(in_array($request->jenis, ['masuk', 'keluar']), fn ($c) => $c->where('jenis', $request->jenis))
            ->when($request->filled('kategori'), fn ($c) => $c->where('kategori', $request->kategori))
            ->when($request->filled('dari'), fn ($c) => $c->filter(fn ($k) => $k->tanggal->toDateString() >= $request->dari))
            ->when($request->filled('sampai'), fn ($c) => $c->filter(fn ($k) => $k->tanggal->toDateString() <= $request->sampai))
            ->when($cari !== '', fn ($c) => $c->filter(fn ($k) => str_contains(mb_strtolower("{$k->kode} {$k->uraian} {$k->kategori} {$k->sumberTampil()} {$k->catatan}"), $cari)))
            ->sortByDesc(fn ($k) => $k->tanggal->format('Ymd') . str_pad((string) $k->id, 10, '0', STR_PAD_LEFT))
            ->values();

        $hal = LengthAwarePaginator::resolveCurrentPage();
        $baris = new LengthAwarePaginator($hasil->forPage($hal, 25), $hasil->count(), 25, $hal, ['path' => $request->url(), 'query' => $request->query()]);

        return view('kas-proyek.index', [
            'baris'        => $baris,
            'jumlahHasil'  => $hasil->count(),
            'totalMasuk'   => $semua->where('jenis', 'masuk')->sum('nominal'),
            'totalKeluar'  => $semua->where('jenis', 'keluar')->sum('nominal'),
            'saldo'        => $saldo,   // = saldo berjalan baris terakhir
            'kategoriList' => $semua->pluck('kategori')->merge(KasTransaksi::kategoriManual('masuk'))->unique()->sort()->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $k = DB::transaction(fn () => KasTransaksi::create($data + [
            'asal' => 'manual',
            'kode' => $this->kas->kodeBerikut($data['jenis'], $data['tanggal']),
        ]));

        return back()->with('success', "Transaksi kas {$k->kode} berhasil dicatat.");
    }

    public function update(Request $request, KasTransaksi $kasTransaksi)
    {
        if ($kasTransaksi->isOtomatis()) {
            return back()->with('error', "Baris kas {$kasTransaksi->kode} dibuat otomatis. Ubah lewat menu " . (KasTransaksi::MENU_ASAL[$kasTransaksi->asal] ?? 'asalnya') . '.');
        }
        $data = $this->validasi($request);
        if ($data['jenis'] !== $kasTransaksi->jenis) {
            return back()->with('error', 'Jenis kas (masuk/keluar) tidak bisa diubah. Hapus lalu catat ulang.');
        }
        $kasTransaksi->update($data);

        return back()->with('success', "Transaksi kas {$kasTransaksi->kode} berhasil diperbarui.");
    }

    public function destroy(KasTransaksi $kasTransaksi)
    {
        if ($kasTransaksi->isOtomatis()) {
            return back()->with('error', "Baris kas {$kasTransaksi->kode} dibuat otomatis. Hapus lewat menu " . (KasTransaksi::MENU_ASAL[$kasTransaksi->asal] ?? 'asalnya') . '.');
        }
        $kasTransaksi->delete();

        return back()->with('success', "Transaksi kas {$kasTransaksi->kode} dihapus.");
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'tanggal'  => ['required', 'date', 'before_or_equal:today'],
            'jenis'    => ['required', 'in:masuk,keluar'],
            'kategori' => ['required', Rule::in(KasTransaksi::kategoriManual((string) $request->jenis))],
            'uraian'   => ['required', 'string', 'max:255'],
            'nominal'  => ['required', 'numeric', 'gt:0'],
            'sumber'   => ['nullable', 'string', 'max:255'],
            'catatan'  => ['nullable', 'string', 'max:1000'],
        ], [
            'kategori.in' => 'Pilih kategori dari daftar.',
            'nominal.gt'  => 'Nominal harus lebih dari nol.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh melewati hari ini.',
        ], ['sumber' => 'sumber/transaksi']);

        // Pos alokasi pengeluaran mengikuti kategori (pemetaan yang sama dengan RAB); kas masuk manual tidak dialokasikan
        $data['pos'] = $data['jenis'] === 'keluar' ? (Rab::KATEGORI[$data['kategori']] ?? null) : null;

        return $data;
    }
}
