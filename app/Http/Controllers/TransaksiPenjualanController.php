<?php

namespace App\Http\Controllers;

use App\Models\Agen;
use App\Models\Kavling;
use App\Models\Konsumen;
use App\Models\RiwayatPembayaran;
use App\Models\TransaksiPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class TransaksiPenjualanController extends Controller
{
    public function index(Request $request)
    {
        $query = TransaksiPenjualan::with(['konsumen', 'kavling', 'agen']);

        if ($request->filled('cari')) {
            $cari = $request->cari;
            $query->where(function ($q) use ($cari) {
                $q->where('kode_transaksi', 'like', "%{$cari}%")
                  ->orWhereHas('konsumen', fn($qq) => $qq->where('nama_lengkap', 'like', "%{$cari}%"));
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

        // Kavling yang masih tersedia, buat dropdown di modal
        $kavlings = Kavling::where('status', 'tersedia')
            ->orderBy('blok')
            ->orderBy('no')
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
            'tanggal'          => 'required|date',
            'status'           => 'required|in:reservasi,booking,dp,lunas',
            'konsumen_id'      => 'required|exists:konsumens,id',
            'kavling_id'       => 'required|exists:kavlings,id',
            'agen_id'          => 'nullable|exists:agens,id',
            'jenis_pembayaran' => 'required|in:cash,angsuran',
            'tenor'            => 'nullable|required_if:jenis_pembayaran,angsuran|integer|min:1',
            'bayar_sekarang'   => 'nullable|numeric|min:0',
            'catatan'          => 'nullable|string',
        ]);

        $konsumen = Konsumen::findOrFail($validated['konsumen_id']);
        $kavling  = Kavling::findOrFail($validated['kavling_id']);

        $nilaiJual  = (float) $kavling->harga_jual;
        $sudahBayar = $this->totalSudahBayar($konsumen); // dihitung server, bukan dari input
        $sisaAwal   = max($nilaiJual - $sudahBayar, 0);

        $bayarSekarang = min((float) ($validated['bayar_sekarang'] ?? 0), $sisaAwal);
        $totalBayar    = $sudahBayar + $bayarSekarang;
        $sisa          = max($nilaiJual - $totalBayar, 0);

        $status = $sisa <= 0 ? 'lunas' : $validated['status'];

        TransaksiPenjualan::create([
            'kode_transaksi'   => TransaksiPenjualan::generateKodeTransaksi(),
            'tanggal'          => $validated['tanggal'],
            'status'           => $status,
            'konsumen_id'      => $validated['konsumen_id'],
            'kavling_id'       => $validated['kavling_id'],
            'agen_id'          => $validated['agen_id'] ?? null,
            'jenis_pembayaran' => $validated['jenis_pembayaran'],
            'nilai_jual'       => $nilaiJual,
            'tenor'            => $validated['jenis_pembayaran'] === 'angsuran' ? $validated['tenor'] : null,
            'nominal_dp'       => $bayarSekarang,
            'total_bayar'      => $totalBayar,
            'sisa_pembayaran'  => $sisa,
            'catatan'          => $validated['catatan'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('transaksi-penjualan.index')
            ->with('success', 'Transaksi berhasil disimpan.');
    }

    // Dipanggil via fetch() di modal "Cari Konsumen"
    public function searchKonsumen(Request $request)
    {
        $q = trim($request->get('q', ''));

        $konsumens = Konsumen::with(['kavling', 'agen'])
            ->where(function ($w) use ($q) {
                $w->where('nama_lengkap', 'like', "%{$q}%")
                  ->orWhere('id_konsumen', 'like', "%{$q}%")
                  ->orWhere('no_hp', 'like', "%{$q}%");
            })
            ->limit(5)
            ->get();

        $hasil = $konsumens->map(function (Konsumen $k) {
            $kv = $k->kavling;

            return [
                'id'               => $k->id,
                'kode_konsumen'    => $k->id_konsumen,
                'nama'             => $k->nama_lengkap,
                'telepon'          => $k->no_hp,
                'agen_id'          => $k->agen_id,
                'status'           => $this->mapStatus($k),
                'jenis_pembayaran' => $this->mapJenisPembayaran($k),
                'tenor'            => $k->jumlah_angsuran ?: null,
                'sudah_bayar'      => $this->totalSudahBayar($k),
                'kavling'          => $kv ? [
                    'id'           => $kv->id,
                    'kode_kavling' => $kv->kode_kavling,
                    'blok'         => $kv->blok,
                    'no'           => $kv->no,
                    'tipe'         => $kv->tipe,
                    'luas'         => $kv->luas,
                    'harga_per_m2' => $kv->harga_per_m2,
                    'harga_jual'   => $kv->harga_jual,
                ] : null,
            ];
        });

        return response()->json($hasil);
    }

    // Total yang sudah dibayar konsumen.
    // Prioritas: riwayat pembayaran. Kalau belum ada riwayat, pakai reservasi + booking + DP.
    private function totalSudahBayar(Konsumen $k): float
    {
        $riwayat = 0.0;
        $table = (new RiwayatPembayaran)->getTable();

        foreach (['nominal', 'jumlah', 'jumlah_bayar', 'nominal_bayar', 'total'] as $kolom) {
            if (Schema::hasColumn($table, $kolom)) {
                $riwayat = (float) $k->riwayatPembayarans()->sum($kolom);
                break;
            }
        }

        if ($riwayat > 0) {
            return $riwayat;
        }

        return (float) $k->nominal_reservasi
             + (float) $k->nominal_booking
             + (float) $k->down_payment;
    }

    // status_transaksi konsumen -> reservasi/booking/dp/lunas
    private function mapStatus(Konsumen $k): string
    {
        $s = strtolower((string) $k->status_transaksi);

        foreach (['lunas', 'dp', 'booking', 'reservasi'] as $opsi) {
            if (str_contains($s, $opsi)) {
                return $opsi;
            }
        }

        return 'reservasi';
    }

    // skema_bayar konsumen -> cash/angsuran
    private function mapJenisPembayaran(Konsumen $k): string
    {
        $s = strtolower((string) $k->skema_bayar);

        if (str_contains($s, 'cash') || str_contains($s, 'tunai')) {
            return 'cash';
        }

        if ($s !== '' || (int) $k->jumlah_angsuran > 0) {
            return 'angsuran';
        }

        return 'cash';
    }
}