<?php

namespace App\Support;

/**
 * Satu-satunya tempat label & warna badge status di seluruh aplikasi.
 */
class Status
{
    private const WARNA = [
        'hijau'  => 'bg-forest-50 text-forest-600 ring-1 ring-inset ring-forest-200',
        'biru'   => 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200',
        'kuning' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200',
        'ungu'   => 'bg-violet-50 text-violet-700 ring-1 ring-inset ring-violet-200',
        'indigo' => 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200',
        'gelap'  => 'bg-forest-800 text-white ring-1 ring-inset ring-forest-800',
        'emas'   => 'bg-gold-100 text-gold-800 ring-1 ring-inset ring-gold-300',
        'merah'  => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200',
        'abu'    => 'bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200',
    ];

    /** status => [label, warna] */
    private const PETA = [
        // kavling
        'tersedia'  => ['Tersedia', 'hijau'],
        'reservasi' => ['Reservasi', 'biru'],
        'booking'   => ['Booking', 'kuning'],
        'dp'        => ['DP', 'ungu'],
        'terjual'   => ['Terjual', 'gelap'],
        // transaksi
        'angsuran'  => ['Angsuran', 'indigo'],
        'lunas'     => ['Lunas', 'hijau'],
        'batal'     => ['Batal', 'merah'],
        // metode
        'cash'      => ['Cash', 'emas'],
        // checklist legal
        'belum'     => ['Belum', 'abu'],
        'proses'    => ['Proses', 'kuning'],
        'selesai'   => ['Selesai', 'hijau'],
        // cicilan
        'terlambat' => ['Terlambat', 'merah'],
        'jatuh_tempo' => ['Jatuh Tempo', 'kuning'],
        'sebagian'  => ['Sebagian', 'kuning'],
        // lead
        'lead'      => ['Lead', 'abu'],
        'prospek'   => ['Prospek', 'kuning'],
        'closing'   => ['Closing', 'hijau'],
        'gugur'     => ['Gugur', 'merah'],
        // RAB (rumus Excel RAB_MASTER)
        'belum_dianggarkan' => ['Belum Dianggarkan', 'abu'],
        'berjalan'  => ['Berjalan', 'biru'],
        'sesuai'    => ['Sesuai', 'hijau'],
        'melebihi'  => ['Melebihi', 'merah'],
        // umum
        'aktif'     => ['Aktif', 'hijau'],
        'menunggu'  => ['Menunggu', 'abu'],
        'masuk'     => ['Masuk', 'hijau'],
        'keluar'    => ['Keluar', 'merah'],
    ];

    public static function label(?string $status): string
    {
        return self::PETA[$status][0] ?? ucwords(str_replace('_', ' ', (string) $status));
    }

    public static function kelas(?string $status, ?string $warna = null): string
    {
        return self::WARNA[$warna ?? (self::PETA[$status][1] ?? 'abu')];
    }
}
