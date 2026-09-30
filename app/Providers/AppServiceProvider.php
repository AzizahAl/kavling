<?php

namespace App\Providers;

use App\Services\Pengaturan;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));
        setlocale(LC_TIME, 'id_ID.UTF-8', 'id_ID', 'Indonesian');

        // Nama proyek di layout mengikuti Pengaturan Proyek
        View::composer(['layouts.app', 'partials.sidebar'], fn ($view) => $view->with('namaProyek', Pengaturan::get('nama_proyek', 'Tectona Residen')));
    }
}
