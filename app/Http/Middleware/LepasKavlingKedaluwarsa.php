<?php

namespace App\Http\Middleware;

use App\Services\TransaksiService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * XAMPP tidak punya penjadwal otomatis, jadi transaksi "Menunggu Pembayaran" yang lewat batas tahan
 * diperiksa saat halaman aplikasi dibuka (paling sering sekali per menit) lalu dibatalkan & kavlingnya dilepas.
 * Di server dengan cron, perintah `php artisan tectona:lepas-kedaluwarsa` juga dijadwalkan tiap 5 menit.
 */
class LepasKavlingKedaluwarsa
{
    public function __construct(private TransaksiService $svc) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && Cache::add('tectona:periksa-kedaluwarsa', true, 60)) {
            try {
                $this->svc->lepasKedaluwarsa();
            } catch (\Throwable $e) {
                report($e); // jangan sampai pemeriksaan ini menggagalkan halaman
            }
        }

        return $next($request);
    }
}
