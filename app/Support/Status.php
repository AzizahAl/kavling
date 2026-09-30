<?php

namespace App\Support;

/**
 * Satu-satunya tempat label, warna, dan ikon badge status di seluruh aplikasi.
 * Status positif (tersedia, lunas, selesai) sengaja tidak memakai hijau merek:
 * tersedia = teal, lunas/selesai = hijau daun + ikon centang.
 */
class Status
{
    private const WARNA = [
        'teal'   => 'bg-teal-50 text-teal-700 ring-1 ring-inset ring-teal-600/20',
        'sukses' => 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/25',
        'biru'   => 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-600/20',
        'kuning' => 'bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-600/25',
        'ungu'   => 'bg-violet-50 text-violet-700 ring-1 ring-inset ring-violet-600/20',
        'indigo' => 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-600/20',
        'gelap'  => 'bg-slate-800 text-white ring-1 ring-inset ring-slate-800',
        'merek'  => 'bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-600/25',
        'merah'  => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20',
        'abu'    => 'bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-500/15',
        // alias lama
        'hijau'  => 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/25',
        'emas'   => 'bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-600/25',
    ];

    /** status => [label, warna, ikon|null ('titik' = titik bulat)] */
    private const PETA = [
        // kavling
        'tersedia'  => ['Tersedia', 'teal', 'titik'],
        'reservasi' => ['Reservasi', 'biru', null],
        'booking'   => ['Booking', 'kuning', null],
        'dp'        => ['DP', 'ungu', null],
        'terjual'   => ['Terjual', 'gelap', null],
        // transaksi
        'angsuran'  => ['Angsuran', 'indigo', null],
        'lunas'     => ['Lunas', 'sukses', 'check'],
        'batal'     => ['Batal', 'merah', 'x'],
        // metode
        'cash'      => ['Cash', 'abu', null],
        // checklist legal
        'belum'     => ['Belum', 'abu', null],
        'proses'    => ['Proses', 'kuning', null],
        'selesai'   => ['Selesai', 'sukses', 'check'],
        // cicilan
        'terlambat' => ['Terlambat', 'merah', 'clock'],
        'jatuh_tempo' => ['Jatuh Tempo', 'kuning', 'clock'],
        'sebagian'  => ['Sebagian', 'kuning', null],
        // lead
        'lead'      => ['Lead', 'abu', null],
        'prospek'   => ['Prospek', 'kuning', null],
        'closing'   => ['Closing', 'sukses', 'check'],
        // RAB (rumus Excel RAB_MASTER)
        'belum_dianggarkan' => ['Belum Dianggarkan', 'abu', null],
        'berjalan'  => ['Berjalan', 'biru', null],
        'sesuai'    => ['Sesuai', 'sukses', 'check'],
        'melebihi'  => ['Melebihi', 'merah', 'alert'],
        // umum
        'aktif'     => ['Aktif', 'merek', 'titik'],
        'nonaktif'  => ['Nonaktif', 'abu', null],
        'menunggu'  => ['Menunggu', 'abu', null],
        'masuk'     => ['Masuk', 'sukses', null],
        'keluar'    => ['Keluar', 'merah', null],
        'otomatis'  => ['Otomatis', 'abu', null],
    ];

    public static function label(?string $status): string
    {
        return self::PETA[$status][0] ?? ucwords(str_replace('_', ' ', (string) $status));
    }

    public static function kelas(?string $status, ?string $warna = null): string
    {
        return self::WARNA[$warna ?? (self::PETA[$status][1] ?? 'abu')] ?? self::WARNA['abu'];
    }

    public static function ikon(?string $status): ?string
    {
        return self::PETA[$status][2] ?? null;
    }
}
