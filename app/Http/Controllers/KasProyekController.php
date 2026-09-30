<?php

namespace App\Http\Controllers;

use App\Models\AlokasiKas;
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

        $cari = mb_strtolower((string) $request->cari);
        $hasil = $semua
            ->when(in_array($request->jenis, ['masuk', 'keluar']), fn ($c) => $c->where('jenis', $request->jenis))
            ->when($request->filled('asal'), fn ($c) => $c->where('asal', $request->asal))
            ->when($request->filled('pos'), fn ($c) => $c->where('pos', $request->pos))
            ->when($request->filled('kategori'), fn ($c) => $c->where('kategori', $request->kategori))
            ->when($request->filled('bulan'), fn ($c) => $c->filter(fn ($k) => $k->tanggal->format('Y-m') === $request->bulan))
            ->when($cari !== '', fn ($c) => $c->filter(fn ($k) => str_contains(mb_strtolower("{$k->kode} {$k->uraian} {$k->kategori} {$k->sumber}"), $cari)))
            ->sortByDesc(fn ($k) => $k->tanggal->format('Ymd') . str_pad((string) $k->id, 10, '0', STR_PAD_LEFT))
            ->values();

        $hal = LengthAwarePaginator::resolveCurrentPage();
        $baris = new LengthAwarePaginator($hasil->forPage($hal, 25), $hasil->count(), 25, $hal, ['path' => $request->url(), 'query' => $request->query()]);

        return view('kas-proyek.index', [
            'baris'       => $baris,
            'totalMasuk'  => $semua->where('jenis', 'masuk')->sum('nominal'),
            'totalKeluar' => $semua->where('jenis', 'keluar')->sum('nominal'),
            'saldo'       => $saldo,
            'filterMasuk' => $hasil->where('jenis', 'masuk')->sum('nominal'),
            'filterKeluar' => $hasil->where('jenis', 'keluar')->sum('nominal'),
            'kategoriList' => $semua->pluck('kategori')->unique()->sort()->values(),
            'rabs'        => Rab::orderBy('kategori')->get(['id', 'kategori', 'uraian', 'pos']),
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
            return back()->with('error', "Baris kas {$kasTransaksi->kode} dibuat otomatis dari pembayaran/pembatalan transaksi. Ubah lewat halaman transaksinya.");
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
            return back()->with('error', "Baris kas {$kasTransaksi->kode} dibuat otomatis dari pembayaran/pembatalan transaksi. Ubah lewat halaman transaksinya.");
        }
        $kasTransaksi->delete();

        return back()->with('success', "Transaksi kas {$kasTransaksi->kode} dihapus.");
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'tanggal'  => ['required', 'date', 'before_or_equal:today'],
            'jenis'    => ['required', 'in:masuk,keluar'],
            'kategori' => ['required', 'string', 'max:100'],
            'rab_id'   => ['nullable', Rule::exists('rabs', 'id')],
            'pos'      => ['nullable', Rule::requiredIf($request->jenis === 'keluar' && ! $request->filled('rab_id')), Rule::in(array_keys(AlokasiKas::POS))],
            'uraian'   => ['required', 'string', 'max:255'],
            'nominal'  => ['required', 'numeric', 'min:1'],
            'sumber'   => ['nullable', 'string', 'max:255'],
            'catatan'  => ['nullable', 'string', 'max:1000'],
        ], ['pos.required' => 'Pilih pos alokasi yang menanggung pengeluaran ini.'], ['pos' => 'pos alokasi']);

        if ($data['jenis'] === 'masuk') {
            $data['pos'] = null;
            $data['rab_id'] = null;
        } elseif (! empty($data['rab_id'])) {
            // Pengeluaran RAB selalu dibebankan ke pos RAB tersebut
            $data['pos'] = Rab::find($data['rab_id'])->pos ?? $data['pos'];
        }

        return $data;
    }
}
