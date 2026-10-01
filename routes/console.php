<?php

use App\Services\TransaksiService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Batalkan transaksi "Menunggu Pembayaran" yang lewat batas tahan & lepaskan kavlingnya.
Artisan::command('tectona:lepas-kedaluwarsa', function (TransaksiService $svc) {
    $this->info($svc->lepasKedaluwarsa() . ' transaksi kedaluwarsa dibatalkan.');
})->purpose('Lepaskan kavling yang ditahan tanpa pembayaran reservasi');

// Untuk server dengan cron (php artisan schedule:run). Di XAMPP pemeriksaan juga jalan saat halaman dibuka.
Schedule::command('tectona:lepas-kedaluwarsa')->everyFiveMinutes();
