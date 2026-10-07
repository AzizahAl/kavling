<?php

namespace App\Support;

/**
 * Daftar bank Indonesia (berbadan hukum Indonesia) untuk isian "Nama Bank" penyetor: BUMN, swasta nasional, digital,
 * syariah, dan pembangunan daerah. Kantor cabang bank asing tidak dimasukkan. Disimpan sebagai nama bank.
 */
class Bank
{
    public const DAFTAR = [
        // --- BUMN (Himbara) ---
        'Bank Mandiri', 'Bank Rakyat Indonesia (BRI)', 'Bank Negara Indonesia (BNI)', 'Bank Tabungan Negara (BTN)',

        // --- Swasta nasional ---
        'Bank Central Asia (BCA)', 'Bank CIMB Niaga', 'Bank Danamon', 'Bank Permata', 'Bank Maybank Indonesia',
        'Bank OCBC Indonesia', 'Bank Panin', 'Bank UOB Indonesia', 'Bank Mega', 'Bank Sinarmas', 'Bank BTPN (SMBC Indonesia)',
        'Bank Bukopin (KB Bank)', 'Bank Mayapada', 'Bank Artha Graha Internasional', 'Bank Bumi Arta', 'Bank Capital Indonesia',
        'Bank China Construction Bank Indonesia (CCB)', 'Bank CTBC Indonesia', 'Bank DBS Indonesia', 'Bank Ganesha',
        'Bank Hana Indonesia', 'Bank HSBC Indonesia', 'Bank ICBC Indonesia', 'Bank Ina Perdana', 'Bank Index Selindo',
        'Bank JTrust Indonesia', 'Bank Maspion Indonesia', 'Bank Mestika Dharma', 'Bank Mizuho Indonesia', 'Bank MNC Internasional',
        'Bank Multiarta Sentosa (MAS)', 'Bank Nationalnobu', 'Bank Oke Indonesia', 'Bank Resona Perdania', 'Bank Sahabat Sampoerna',
        'Bank Shinhan Indonesia', 'Bank QNB Indonesia', 'Bank Victoria International', 'Bank Woori Saudara', 'Bank IBK Indonesia',
        'Bank Amar Indonesia', 'Bank Fama Internasional', 'Bank Mandiri Taspen', 'Bank Prima Master', 'Bank Krom Indonesia', 'Bank Neo Commerce (BNC)', 'Bank Raya Indonesia',
        'Bank Seabank Indonesia', 'Bank Jago', 'Allo Bank', 'Blu by BCA Digital', 'Bank Aladin Syariah', 'Superbank',
        'Hibank (Bank Hibank Indonesia)', 'Bank Jasa Jakarta (Saqu)',

        // --- Syariah ---
        'Bank Syariah Indonesia (BSI)', 'Bank Muamalat Indonesia', 'Bank Mega Syariah', 'Bank Panin Dubai Syariah',
        'Bank BCA Syariah', 'Bank BTPN Syariah', 'Bank Victoria Syariah', 'Bank KB Bukopin Syariah', 'Bank Jabar Banten Syariah (BJB Syariah)',
        'Bank Aceh Syariah', 'Bank NTB Syariah', 'Bank Riau Kepri Syariah', 'Bank Nagari', 'Bank Net Indonesia Syariah',
        'Bank CIMB Niaga Syariah', 'Bank Permata Syariah', 'Bank Danamon Syariah', 'Bank Maybank Syariah', 'Bank OCBC Syariah',
        'Bank Syariah Nasional (BTN Syariah)',

        // --- Pembangunan daerah (BPD) ---
        'Bank BJB (Jawa Barat & Banten)', 'Bank DKI', 'Bank Jateng', 'Bank Jatim', 'Bank BPD DIY', 'Bank BPD Bali',
        'Bank NTT', 'Bank Kalbar', 'Bank Kalteng', 'Bank Kalsel', 'Bank Kaltimtara', 'Bank Sulselbar', 'Bank SulutGo',
        'Bank Sulteng', 'Bank Sultra', 'Bank Maluku Malut', 'Bank Papua', 'Bank Sumut', 'Bank Sumsel Babel', 'Bank Jambi',
        'Bank Bengkulu', 'Bank Lampung', 'Bank Riau Kepri', 'Bank Banten',

        'Lainnya',
    ];

    /** @return array<string, string> nama => nama, untuk pilihan select */
    public static function pilihan(): array
    {
        return array_combine(self::DAFTAR, self::DAFTAR);
    }
}
