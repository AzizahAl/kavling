<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\TransaksiPenjualan;
use App\Services\DokumenService;
use App\Services\TransaksiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PembayaranController extends Controller
{
    public function __construct(private TransaksiService $svc) {}

    public function store(Request $request, TransaksiPenjualan $transaksi)
    {
        $this->pastikanMilik($transaksi->agen_id);
        $p = $this->svc->catatPembayaran($transaksi, $this->validasi($request), auth()->id());

        return redirect()->route('transaksi-penjualan.show', $transaksi)
            ->with('success', "Pembayaran {$p->label_jenis} " . rupiah($p->nominal) . " tercatat ({$p->kode}) dan masuk ke Kas Proyek.");
    }

    public function update(Request $request, TransaksiPenjualan $transaksi, Pembayaran $pembayaran)
    {
        $this->svc->ubahPembayaran($pembayaran, $this->validasi($request));

        return redirect()->route('transaksi-penjualan.show', $transaksi)->with('success', "Pembayaran {$pembayaran->kode} diperbarui.");
    }

    public function destroy(TransaksiPenjualan $transaksi, Pembayaran $pembayaran)
    {
        $this->svc->hapusPembayaran($pembayaran);

        return redirect()->route('transaksi-penjualan.show', $transaksi)->with('success', "Pembayaran {$pembayaran->kode} dihapus beserta catatan kasnya.");
    }

    public function kwitansi(Pembayaran $pembayaran, DokumenService $dok)
    {
        $this->pastikanMilik($pembayaran->transaksi->agen_id);
        return view('dokumen.kwitansi', ['k' => $dok->kwitansi($pembayaran), 'p' => $pembayaran, 'pdf' => false]);
    }

    public function kwitansiUnduh(Pembayaran $pembayaran, DokumenService $dok)
    {
        $this->pastikanMilik($pembayaran->transaksi->agen_id);
        $k = $dok->kwitansi($pembayaran);

        return Pdf::loadView('dokumen.kwitansi', ['k' => $k, 'p' => $pembayaran, 'pdf' => true])
            ->setPaper('a4', 'portrait')
            ->download('Kwitansi-' . $pembayaran->kode . '-' . str($k['nama'])->slug() . '.pdf');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'tanggal'  => ['required', 'date', 'before_or_equal:today'],
            'jenis'    => ['required', Rule::in(array_keys(Pembayaran::JENIS))],
            'nominal'  => ['required', 'numeric', 'min:1'],
            'metode'   => ['required', Rule::in(array_keys(Pembayaran::METODE_KONSUMEN))],
            'nama_penyetor'     => ['nullable', 'string', 'max:100'],
            'bank_penyetor'     => ['nullable', Rule::in(\App\Support\Bank::DAFTAR)],
            'rekening_penyetor' => ['nullable', 'string', 'max:40'],
            'no_bukti' => ['nullable', 'string', 'max:100'],
            'catatan'  => ['nullable', 'string', 'max:500'],
        ], [], ['tanggal' => 'tanggal bayar']);
    }
}
