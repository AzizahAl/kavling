<?php

namespace App\Support;

/**
 * Daftar formulir resmi proyek, ditampilkan di Pengaturan Proyek › Formulir (khusus admin).
 * Dokumen transaksi juga terisi otomatis dari tiap transaksi (DokumenController).
 * Setiap formulir = kertas + daftar halaman [view di formulir/halaman, gaya huruf dokumen asli].
 */
class Formulir
{
    /** Formulir yang bisa dicetak terisi dari transaksi. */
    public const TRANSAKSI = ['reservasi', 'booking', 'spk', 'ppjb'];

    public const DAFTAR = [
        // --- Dokumen transaksi ---
        'reservasi' => ['Form Reservasi / Pemesanan', 'a4', [['reservasi', 'calibri']], 'transaksi',
            'Bukti reservasi kavling beserta syarat & ketentuan.'],
        'booking' => ['Form Booking', 'letter', [['booking', 'arial']], 'transaksi',
            'Data booking dan skema pembayaran.'],
        'spk' => ['Surat Pemesanan Kavling (SPK)', 'letter', [['spk', 'arial']], 'transaksi',
            'Dasar administrasi pemesanan, dibuat setelah booking fee terbayar.'],
        'ppjb' => ['PPJB + Lampiran A–D', 'a4', [
            ['ppjb', 'times'], ['ppjb-lampiran-a', 'times'], ['ppjb-lampiran-b', 'times'],
            ['ppjb-lampiran-c', 'times'], ['ppjb-lampiran-d', 'times'],
        ], 'transaksi', 'Perjanjian pengikatan jual beli, siteplan, jadwal pembayaran, pernyataan, checklist dokumen.'],

        // --- Formulir & materi marketing (kolom/angka mengikuti form & data sistem) ---
        'toolkit' => ['Marketing Toolkit (Lengkap)', 'letter', [
            ['toolkit-profil', 'arial'], ['toolkit-paket', 'arial'], ['lead', 'arial'], ['data-konsumen', 'arial'],
            ['booking', 'arial'], ['spk', 'arial'], ['checklist-dokumen', 'arial'],
        ], 'toolkit', 'Gabungan profil & pricelist, paket pembayaran, form lead, form data konsumen, booking, SPK, dan checklist dokumen.'],
        'profil' => ['Profil, Daftar Kavling & Pricelist', 'letter', [['toolkit-profil', 'arial'], ['toolkit-paket', 'arial']], 'toolkit',
            'Data proyek, kavling, harga tahap aktif, dan paket pembayaran dari sistem.'],
        'lead' => ['Form Lead / Calon Konsumen', 'letter', [['lead', 'arial']], 'toolkit',
            'Kolom sama dengan form Input Lead.'],
        'data-konsumen' => ['Form Data Konsumen', 'letter', [['data-konsumen', 'arial']], 'toolkit',
            'Kolom sama dengan form Tambah Konsumen.'],
        'checklist-dokumen' => ['Checklist Dokumen Konsumen', 'letter', [['checklist-dokumen', 'arial']], 'toolkit',
            'Identitas, kwitansi per jenis pembayaran, formulir, SPK, PPJB, dan AJB.'],
    ];

    /** Margin kertas khusus (atas kanan bawah kiri); selain ini mengikuti ukuran kertas. */
    public const MARGIN = ['reservasi' => '10mm 20mm 10mm 20mm'];

    /** Data tata letak untuk view formulir.cetak. */
    public static function tata(string $jenis): array
    {
        abort_unless(isset(self::DAFTAR[$jenis]), 404);
        [$judul, $kertas, $halaman] = self::DAFTAR[$jenis];
        $margin = self::MARGIN[$jenis] ?? null;

        return compact('judul', 'kertas', 'halaman', 'margin');
    }
}
