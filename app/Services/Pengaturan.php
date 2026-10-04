<?php

namespace App\Services;

use App\Models\Pengaturan as PengaturanModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Akses pengaturan proyek. Semua angka bisnis dibaca dari sini, tidak ditulis di kode.
 *
 * DEFINISI berisi: kunci => [grup, label, tipe, nilai bawaan (baseline Excel), keterangan].
 * Nilai bawaan null = belum ditentukan pemilik proyek, harus diisi di halaman Pengaturan.
 */
class Pengaturan
{
    public const DEFINISI = [
        // --- Identitas & baseline proyek ---
        'nama_proyek'          => ['proyek', 'Nama Proyek', 'teks', 'Tectona Residen', null],
        'alamat_proyek'        => ['proyek', 'Alamat / Lokasi Proyek', 'teks', null, 'Dipakai di kop kwitansi, SPK, dan PPJB'],
        'luas_lahan_are'       => ['proyek', 'Luas Lahan (are)', 'desimal', 17.34, 'Baseline proyek'],
        'jumlah_kavling'       => ['proyek', 'Jumlah Kavling', 'angka', 14, '7 Prima + 7 Standard'],
        'lebar_jalan_m'        => ['proyek', 'Lebar Jalan Dalam (m)', 'desimal', 5.3, 'Hasil pengukuran akhir'],
        'status_legal_lahan'   => ['proyek', 'Status Legal Lahan', 'teks', 'Girik - proses AJB/TORA', 'Belum sertifikat'],
        'nama_pemilik_lahan'   => ['proyek', 'Nama Pemilik Lahan', 'teks', null, 'Pihak penerima 20% bagi laba'],
        'nama_pengelola'       => ['proyek', 'Nama Pengelola / Penjual', 'teks', null, 'Pihak penjual di SPK & PPJB'],
        'nik_pengelola'        => ['proyek', 'NIK Pengelola / Penjual', 'teks', null, 'PIHAK PERTAMA di PPJB'],
        'alamat_pengelola'     => ['proyek', 'Alamat Pengelola / Penjual', 'teks', null, 'PIHAK PERTAMA di PPJB'],
        'hp_pengelola'         => ['proyek', 'No. HP Pengelola / Penjual', 'teks', null, 'PIHAK PERTAMA di PPJB'],
        'kota_dokumen'         => ['proyek', 'Kota Penandatanganan Dokumen', 'teks', null, 'Contoh: Garut'],

        // --- Harga ---
        'harga_awal_m2'        => ['harga', 'Harga Awal / m²', 'rupiah', 500000, 'Tahap 1'],
        'kenaikan_harga_m2'    => ['harga', 'Kenaikan Harga / m²', 'rupiah', 50000, 'Naik setiap kelipatan unit terjual'],
        'unit_per_kenaikan'    => ['harga', 'Naik Setiap (unit terjual)', 'angka', 3, 'Harga naik tiap kelipatan ini'],
        'jumlah_tahap'         => ['harga', 'Jumlah Tahap Harga', 'angka', 5, 'Tahap 5 = Rp700.000/m²'],
        'terjual_saat'         => ['harga', 'Kavling Dihitung Terjual Saat', 'pilihan', 'ppjb', 'Untuk kenaikan tahap harga. Sementara, perlu konfirmasi'],

        // --- Reservasi, booking, DP, tenor ---
        'biaya_reservasi'      => ['penjualan', 'Biaya Reservasi', 'rupiah', 500000, 'Di luar harga kavling'],
        'biaya_booking'        => ['penjualan', 'Booking Fee', 'rupiah', 2000000, 'Di luar harga kavling'],
        'potongan_booking'     => ['penjualan', 'Potongan Booking bila Batal', 'rupiah', 1000000, 'Untuk komisi & marketing, masuk pos Marketing. Reservasi dikembalikan penuh'],
        'dp_minimal_persen'    => ['penjualan', 'DP Minimal (%)', 'persen', 0, '0 = boleh tanpa DP'],
        'dp_anjuran_persen'    => ['penjualan', 'DP Anjuran (%)', 'persen', 15, 'Terisi otomatis di form transaksi, bisa diubah'],
        'tenor_maksimal'       => ['penjualan', 'Tenor Maksimal (bulan)', 'angka', 18, 'Batas lama angsuran'],
        'masa_reservasi_hari'  => ['penjualan', 'Masa Berlaku Reservasi (hari)', 'angka', 14, 'Sejak tanggal pembayaran reservasi. Tercetak sebagai "Berlaku s/d" di Form Reservasi'],
        'batas_tahan_jam'      => ['penjualan', 'Tahan Kavling Menunggu Reservasi (jam)', 'angka', 48, 'Lewat batas tanpa uang reservasi: transaksi batal, kavling tersedia lagi. Sementara, perlu konfirmasi'],

        // --- Komisi agen (nominal tetap per transaksi) ---
        'komisi_nominal'       => ['komisi', 'Komisi per Transaksi', 'rupiah', 1000000, 'Standar minimum. Bisa diganti per agen'],
        'komisi_hak_saat'      => ['komisi', 'Komisi Menjadi Hak Saat', 'pilihan', 'booking', 'Sementara, perlu konfirmasi'],
        'komisi_saat_batal'    => ['komisi', 'Komisi bila Transaksi Batal', 'pilihan', 'tetap', 'Sementara, perlu konfirmasi'],

        // --- Alokasi kas (persen dari setiap uang masuk) ---
        'alokasi_tanah'        => ['alokasi', 'Tanah / Kewajiban Pemilik Lahan (%)', 'persen', 50, 'Prioritas sampai tanah lunas'],
        'alokasi_legal_infra'  => ['alokasi', 'Legalitas + Infrastruktur (%)', 'persen', 25, 'AJB/TORA, jalan, drainase, pematangan, gerbang'],
        'alokasi_marketing'    => ['alokasi', 'Marketing + Komisi (%)', 'persen', 10, 'Promosi, agen, banner, sponsorship'],
        'alokasi_cadangan'     => ['alokasi', 'Kas / Cadangan Proyek (%)', 'persen', 10, 'Dana pengaman proyek'],
        'alokasi_operasional'  => ['alokasi', 'Operasional Manajemen (%)', 'persen', 5, 'Transport, admin, cetak, komunikasi'],

        // --- Laba ---
        'laba_pengelola_persen' => ['laba', 'Bagian Pengelola (%)', 'persen', 80, null],
        'laba_pemilik_persen'  => ['laba', 'Bagian Pemilik Lahan (%)', 'persen', 20, null],
        'kategori_rab_legal_infra' => ['laba', 'Kategori RAB untuk Target Legal & Infrastruktur', 'daftar', 'Legalitas, Pematangan Lahan, Infrastruktur, Fasilitas', 'Pisahkan dengan koma'],

        // --- Penomoran ---
        'prefix_konsumen'      => ['penomoran', 'Awalan ID Konsumen', 'teks', 'CUS', 'CUS-2026-0001'],
        'prefix_transaksi'     => ['penomoran', 'Awalan ID Transaksi', 'teks', 'TRX', 'TRX-2026-0001'],
        'prefix_pembayaran'    => ['penomoran', 'Awalan No. Kwitansi', 'teks', 'KWT', 'KWT-2026-0001'],
        'prefix_kavling'       => ['penomoran', 'Awalan Kode Kavling', 'teks', 'TR', 'TR-A01'],

        // --- Dikelola di modul Kewajiban Tanah (tidak tampil di halaman Pengaturan) ---
        'total_kewajiban_tanah' => ['tanah', 'Total Kesepakatan Kewajiban Tanah', 'rupiah', null, 'Kosong sampai hasil pengukuran resmi'],
    ];

    /** Opsi untuk pengaturan bertipe "pilihan". */
    public const PILIHAN = [
        'terjual_saat'      => ['ppjb' => 'PPJB ditandatangani', 'lunas' => 'Pembayaran lunas'],
        'komisi_hak_saat'   => ['booking' => 'Booking terbayar', 'dp' => 'DP / angsuran mulai dibayar', 'ppjb' => 'PPJB ditandatangani', 'lunas' => 'Pembayaran lunas'],
        'komisi_saat_batal' => ['tetap' => 'Tetap jadi hak agen', 'gugur' => 'Gugur'],
    ];

    public const GRUP = [
        'proyek'    => 'Identitas & Baseline Proyek',
        'harga'     => 'Skema Harga',
        'penjualan' => 'Reservasi, Booking, DP & Angsuran',
        'komisi'    => 'Komisi Agen',
        'alokasi'   => 'Alokasi Kas per Uang Masuk',
        'laba'      => 'Pembagian Laba',
        'penomoran' => 'Format Penomoran',
    ];

    private const CACHE = 'pengaturan.semua';

    /** Semua nilai (tersimpan di DB, bila belum ada pakai bawaan). */
    public static function semua(): array
    {
        return Cache::rememberForever(self::CACHE, function () {
            $tersimpan = Schema::hasTable('pengaturans')
                ? PengaturanModel::pluck('nilai', 'kunci')->all()
                : [];

            $hasil = [];
            foreach (self::DEFINISI as $kunci => [, , $tipe, $bawaan]) {
                $mentah = array_key_exists($kunci, $tersimpan) ? $tersimpan[$kunci] : $bawaan;
                $hasil[$kunci] = self::cast($mentah, $tipe);
            }

            return $hasil;
        });
    }

    public static function get(string $kunci, $default = null)
    {
        return self::semua()[$kunci] ?? $default;
    }

    /** Pastikan pengaturan wajib sudah diisi; lempar pesan yang jelas bila belum. */
    public static function wajib(string $kunci)
    {
        $nilai = self::get($kunci);
        if ($nilai === null || $nilai === '') {
            $label = self::DEFINISI[$kunci][1] ?? $kunci;
            throw new \DomainException("Pengaturan \"{$label}\" belum diisi. Lengkapi dulu di menu Pengaturan Proyek.");
        }

        return $nilai;
    }

    public static function simpan(array $data): void
    {
        foreach ($data as $kunci => $nilai) {
            if (! array_key_exists($kunci, self::DEFINISI)) {
                continue;
            }
            PengaturanModel::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai === '' ? null : $nilai]);
        }
        self::lupakan();
    }

    public static function lupakan(): void
    {
        Cache::forget(self::CACHE);
    }

    /** Persen alokasi sebagai pecahan, mis. ['tanah' => 0.5, ...] */
    public static function alokasi(): array
    {
        return [
            'tanah'       => self::get('alokasi_tanah', 0) / 100,
            'legal_infra' => self::get('alokasi_legal_infra', 0) / 100,
            'marketing'   => self::get('alokasi_marketing', 0) / 100,
            'cadangan'    => self::get('alokasi_cadangan', 0) / 100,
            'operasional' => self::get('alokasi_operasional', 0) / 100,
        ];
    }

    private static function cast($nilai, string $tipe)
    {
        if ($nilai === null || $nilai === '') {
            return null;
        }

        return match ($tipe) {
            'rupiah', 'angka' => (int) round((float) $nilai),
            'desimal', 'persen' => (float) $nilai,
            'daftar' => array_values(array_filter(array_map('trim', explode(',', (string) $nilai)))),
            default => (string) $nilai,
        };
    }
}
