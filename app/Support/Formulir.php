<?php

namespace App\Support;

/**
 * Daftar formulir resmi proyek (dari docs/FORM RESERVASI, PPJB + LAMPIRAN, MARKETING TOOLKIT).
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

        // --- Marketing Toolkit ---
        'toolkit' => ['Marketing Toolkit (Lengkap)', 'letter', [
            ['toolkit-profil', 'arial'], ['toolkit-paket', 'arial'], ['data-konsumen', 'arial'],
            ['booking', 'arial'], ['spk', 'arial'], ['checklist-ppjb', 'arial'], ['checklist-dokumen', 'arial'],
            ['kunjungan', 'arial'], ['follow-up', 'arial'], ['closing', 'arial'],
            ['toolkit-siteplan', 'arial'], ['toolkit-brosur', 'arial'],
        ], 'toolkit', 'Profil proyek, product knowledge, pricelist, alur, seluruh form, siteplan, dan brosur.'],
        'profil' => ['Profil, Product Knowledge & Pricelist', 'letter', [['toolkit-profil', 'arial'], ['toolkit-paket', 'arial']], 'toolkit',
            'Bahan presentasi untuk calon konsumen.'],
        'data-konsumen' => ['Form Data Calon Konsumen', 'letter', [['data-konsumen', 'arial']], 'toolkit',
            'Data, kebutuhan, dan catatan sales.'],
        'kunjungan' => ['Form Kunjungan / Survey Lokasi', 'letter', [['kunjungan', 'arial']], 'toolkit',
            'Hal yang ditunjukkan saat survey dan kesan konsumen.'],
        'follow-up' => ['Follow-up Sheet', 'letter', [['follow-up', 'arial']], 'toolkit',
            'Daftar follow-up prospek dan kode Hot / Warm / Cold.'],
        'closing' => ['Sales Closing Sheet', 'letter', [['closing', 'arial']], 'toolkit',
            'Keberatan konsumen dan checklist sebelum closing.'],
        'checklist-ppjb' => ['Checklist PPJBS / PPJB', 'letter', [['checklist-ppjb', 'arial']], 'toolkit',
            'Persiapan sebelum penandatanganan PPJB.'],
        'checklist-dokumen' => ['Checklist Dokumen Konsumen', 'letter', [['checklist-dokumen', 'arial']], 'toolkit',
            'Kelengkapan dokumen dan data arsip.'],
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
