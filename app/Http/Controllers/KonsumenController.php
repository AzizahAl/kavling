<?php

namespace App\Http\Controllers;

use App\Models\Agen;
use App\Models\Kavling;
use App\Models\Konsumen;
use App\Models\RiwayatPembayaran;
use Illuminate\Http\Request;

class KonsumenController extends Controller
{
    public function index(Request $request)
    {
        $query = Konsumen::with(['kavling', 'agen']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('id_konsumen', 'like', "%{$search}%")
                  ->orWhereHas('kavling', fn ($k) => $k->where('kode_kavling', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status_transaksi', $status);
        }

        if ($agenId = $request->input('agen')) {
            $query->where('agen_id', $agenId);
        }

        if ($dokumen = $request->input('dokumen')) {
            // format: ppjb:proses / ajb:selesai dst.
            [$field, $value] = array_pad(explode(':', $dokumen), 2, null);
            if (in_array($field, ['status_ppjb', 'status_ajb', 'status_reservasi', 'status_booking']) && $value) {
                $query->where($field, $value);
            }
        }

        $konsumens = $query->orderByDesc('id')->get();

        $stats = [
            'total_konsumen'        => Konsumen::count(),
            'reservasi'             => Konsumen::where('status_transaksi', 'reservasi')->count(),
            'booking'                => Konsumen::where('status_transaksi', 'booking')->count(),
            'dp'                     => Konsumen::where('status_transaksi', 'dp')->count(),
            'cash_lunas'             => Konsumen::where('status_transaksi', 'cash_lunas')->count(),
            'angsuran'               => Konsumen::where('status_transaksi', 'angsuran')->count(),
            'total_nilai_penjualan'  => Kavling::whereIn('id', Konsumen::pluck('kavling_id'))->sum('harga_jual'),
            'total_pembayaran_masuk' => Konsumen::sum('nominal_reservasi') + Konsumen::sum('nominal_booking') + Konsumen::sum('down_payment'),
        ];

        $kavlings = Kavling::orderBy('kode_kavling')->get();
        $agens = Agen::orderBy('nama_agen')->get();

        return view('konsumen.index', compact('konsumens', 'stats', 'kavlings', 'agens'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap'      => 'required|string|max:255',
            'nik'                => 'required|digits:16|unique:konsumens,nik',
            'no_hp'              => 'required|string|max:20',
            'email'              => 'nullable|email|max:255',
            'alamat'             => 'required|string',
            'kavling_id'         => 'required|exists:kavlings,id',
            'status_transaksi'   => 'required|in:reservasi,booking,dp,cash_lunas,angsuran',
            'tanggal_transaksi'  => 'required|date',
            'agen_id'            => 'required|exists:agens,id',
            'nominal_reservasi'  => 'nullable|numeric|min:0',
            'nominal_booking'    => 'nullable|numeric|min:0',
            'down_payment'       => 'nullable|numeric|min:0',
            'catatan'            => 'nullable|string',
        ]);

        $validated['id_konsumen'] = $this->generateIdKonsumen();
        $validated['status_reservasi'] = ($validated['nominal_reservasi'] ?? 0) > 0 ? 'selesai' : 'belum';
        $validated['status_booking']   = ($validated['nominal_booking'] ?? 0) > 0 ? 'selesai' : 'belum';
        $validated['status_ppjb']      = 'belum';
        $validated['status_ajb']       = 'belum';

        Konsumen::create($validated);

        return redirect()->route('konsumen.index')->with('success', 'Konsumen baru berhasil ditambahkan.');
    }

    public function show(Konsumen $konsumen)
    {
        $konsumen->load(['kavling', 'agen', 'riwayatPembayarans' => function ($q) {
            $q->orderByDesc('tanggal');
        }]);

        $hargaJualNett = $konsumen->kavling->harga_jual ?? 0;

        $targetReservasi = $konsumen->nominal_reservasi ?? 0;
        $targetBooking   = $konsumen->nominal_booking ?? 0;
        $targetDp        = $konsumen->down_payment ?? 0;
        $targetDpTotal   = $targetReservasi + $targetBooking + $targetDp;

        $totalTerbayar = $konsumen->riwayatPembayarans->sum('nominal');
        $sisaTagihan   = $hargaJualNett - $totalTerbayar;

        $progressPercent = $targetDpTotal > 0
            ? round(($totalTerbayar / $targetDpTotal) * 100, 1)
            : 0;

        $sisaSetelahDp = max($hargaJualNett - $targetDpTotal, 0);
        $angsuranPerBulan = $konsumen->jumlah_angsuran > 0
            ? $sisaSetelahDp / $konsumen->jumlah_angsuran
            : 0;

        $summary = [
            'harga_jual_nett'    => $hargaJualNett,
            'total_terbayar'     => $totalTerbayar,
            'sisa_tagihan'       => $sisaTagihan,
            'target_reservasi'   => $targetReservasi,
            'target_booking'     => $targetBooking,
            'target_dp'          => $targetDp,
            'target_dp_total'    => $targetDpTotal,
            'progress_percent'   => $progressPercent,
            'angsuran_per_bulan' => $angsuranPerBulan,
        ];

        return view('konsumen.show', compact('konsumen', 'summary'));
    }

    public function kwitansi(Konsumen $konsumen, RiwayatPembayaran $riwayat)
    {
        // TODO: generate PDF kwitansi
        abort(501, 'Fitur cetak kwitansi belum diimplementasikan.');
    }

    public function buatSpk(Konsumen $konsumen)
    {
        // TODO: generate dokumen SPK
        abort(501, 'Fitur pembuatan SPK belum diimplementasikan.');
    }

    private function generateIdKonsumen(): string
    {
        $tahun = now()->year;
        $terakhir = Konsumen::where('id_konsumen', 'like', "CUS-{$tahun}-%")
            ->orderByDesc('id_konsumen')
            ->first();

        $urutan = 1;
        if ($terakhir) {
            $bagian = explode('-', $terakhir->id_konsumen);
            $urutan = (int) end($bagian) + 1;
        }

        return sprintf('CUS-%d-%04d', $tahun, $urutan);
    }
}