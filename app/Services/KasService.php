<?php

namespace App\Services;

use App\Models\KasTransaksi;
use App\Models\Pembayaran;
use App\Models\TransaksiPenjualan;
use Illuminate\Support\Carbon;

/** Buku kas: pembayaran konsumen & refund tercatat otomatis di sini. */
class KasService
{
    public function __construct(private AlokasiService $alokasi) {}

    public function kodeBerikut(string $jenis, $tanggal = null): string
    {
        $tahun = $tanggal ? Carbon::parse($tanggal)->year : now()->year;

        return Penomoran::berikut('kas_transaksis', 'kode', $jenis === 'masuk' ? 'INC' : 'EXP', $tahun);
    }

    /** Buat / perbarui baris kas masuk untuk sebuah pembayaran. */
    public function catatPembayaran(Pembayaran $p): KasTransaksi
    {
        $p->loadMissing('transaksi.konsumen', 'transaksi.kavling');
        $t = $p->transaksi;

        $data = [
            'tanggal'      => $p->tanggal,
            'kategori'     => 'Penjualan',
            'jenis'        => 'masuk',
            'asal'         => 'pembayaran',
            'transaksi_id' => $t->id,
            'uraian'       => "{$p->label_jenis} {$t->kavling->kode_kavling} – {$t->konsumen->nama_lengkap}",
            'nominal'      => $p->nominal,
            'sumber'       => "{$p->kode} / {$t->kode_transaksi}",
            'catatan'      => trim($p->label_metode . ($p->no_bukti ? " · Bukti {$p->no_bukti}" : '')),
        ];

        $kas = KasTransaksi::firstWhere('pembayaran_id', $p->id);
        if ($kas) {
            $kas->update($data);
        } else {
            $kas = KasTransaksi::create($data + [
                'pembayaran_id' => $p->id,
                'kode'          => $this->kodeBerikut('masuk', $p->tanggal),
            ]);
        }
        $this->alokasi->alokasikan($kas);

        return $kas;
    }

    /** $alokasikan = false: alokasi pengembalian diatur per jenis uang oleh AlokasiService::alokasikanPembatalan. */
    public function catatRefund(TransaksiPenjualan $t, float $nominal, $tanggal, string $rincian, bool $alokasikan = true): KasTransaksi
    {
        $kas = KasTransaksi::create([
            'tanggal'      => $tanggal,
            'kode'         => $this->kodeBerikut('keluar', $tanggal),
            'kategori'     => 'Refund Pembatalan',
            'jenis'        => 'keluar',
            'asal'         => 'refund',
            'transaksi_id' => $t->id,
            'uraian'       => "Refund batal {$t->kode_transaksi} {$t->kavling->kode_kavling} – {$t->konsumen->nama_lengkap}",
            'nominal'      => $nominal,
            'sumber'       => $t->kode_transaksi,
            'catatan'      => $rincian,
        ]);
        if ($alokasikan) {
            $this->alokasi->alokasikan($kas);
        }

        return $kas;
    }

    public function saldo(): float
    {
        return (float) KasTransaksi::selectRaw("COALESCE(SUM(CASE WHEN jenis='masuk' THEN nominal ELSE -nominal END),0) s")->value('s');
    }
}
