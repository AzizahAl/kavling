<?php

namespace App\Http\Controllers;

use App\Models\PembayaranTanah;
use App\Services\AlokasiService;
use App\Services\KewajibanTanahService;
use App\Services\Pengaturan;
use Illuminate\Http\Request;

/** Kewajiban tanah kepada pemilik lahan: total kesepakatan, pembayaran, sisa. */
class KewajibanTanahController extends Controller
{
    public function __construct(private KewajibanTanahService $svc) {}

    public function index(Request $request, AlokasiService $alokasi)
    {
        $daftar = PembayaranTanah::with(['kas', 'pembuat'])
            ->when($request->filled('cari'), fn ($q) => $q->where('keterangan', 'like', "%{$request->cari}%"))
            ->when($request->filled('metode'), fn ($q) => $q->where('metode', $request->metode))
            ->when($request->filled('tahun'), fn ($q) => $q->whereYear('tanggal', $request->tahun))
            ->latest('tanggal')->latest('id')->get();

        return view('kewajiban-tanah.index', [
            'ringkas'  => $this->svc->ringkasan(),
            'laba'     => $alokasi->kelayakanLaba(),
            'daftar'   => $daftar,
            'tahun'    => PembayaranTanah::selectRaw('YEAR(tanggal) t')->distinct()->orderByDesc('t')->pluck('t'),
            'pemilik'  => Pengaturan::get('nama_pemilik_lahan'),
        ]);
    }

    public function aturTotal(Request $request)
    {
        $data = $request->validate(['total' => ['nullable', 'numeric', 'min:1']], [], ['total' => 'total kesepakatan']);
        $this->svc->aturTotal(isset($data['total']) ? (int) $data['total'] : null);

        return back()->with('success', isset($data['total']) ? 'Total kesepakatan kewajiban tanah disimpan.' : 'Total kesepakatan dikosongkan (menunggu penetapan).');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tanggal'    => ['required', 'date', 'before_or_equal:today'],
            'nominal'    => ['required', 'numeric', 'min:1'],
            'keterangan' => ['required', 'string', 'max:255'],
            'metode'     => ['required', 'in:tunai,transfer'],
        ]);
        $this->svc->bayar($data);

        return back()->with('success', 'Pembayaran ke pemilik lahan ' . rupiah($data['nominal']) . ' dicatat dan masuk Kas Proyek sebagai pengeluaran.');
    }

    public function destroy(PembayaranTanah $pembayaran)
    {
        $this->svc->hapus($pembayaran);

        return back()->with('success', 'Pembayaran dihapus beserta catatan kasnya.');
    }
}
