<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KasProyekController;
use App\Http\Controllers\KavlingController;
use App\Http\Controllers\AgenController;
use App\Http\Controllers\KonsumenController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\ProyekController;
use App\Http\Controllers\RabController;
use App\Http\Controllers\SkemaHargaController;
use App\Http\Controllers\TransaksiPenjualanController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// --- Data Master ---
Route::get('/pengaturan-proyek', [ProyekController::class, 'index'])->name('proyek.index');
Route::put('/pengaturan-proyek', [ProyekController::class, 'update'])->name('proyek.update');

Route::resource('master-kavling', KavlingController::class)
    ->names('kavling')
    ->parameters(['master-kavling' => 'kavling'])
    ->except(['create', 'edit']);

Route::get('/skema-harga', [SkemaHargaController::class, 'index'])->name('skema-harga.index');

Route::get('/data-agen/next-kode', [AgenController::class, 'nextKode'])->name('agen.next-kode');
Route::resource('data-agen', AgenController::class)
    ->names('agen')
    ->parameters(['data-agen' => 'agen'])
    ->except(['create', 'edit']);

// --- Penjualan ---
Route::prefix('transaksi-penjualan')->name('transaksi-penjualan.')->controller(TransaksiPenjualanController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/baru', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/{transaksi}', 'show')->name('show');
    Route::get('/{transaksi}/ubah', 'edit')->name('edit');
    Route::put('/{transaksi}', 'update')->name('update');
    Route::post('/{transaksi}/batal', 'batal')->name('batal');
});

Route::prefix('transaksi-penjualan/{transaksi}/pembayaran')->name('pembayaran.')->controller(PembayaranController::class)->group(function () {
    Route::post('/', 'store')->name('store');
    Route::put('/{pembayaran}', 'update')->name('update')->scopeBindings();
    Route::delete('/{pembayaran}', 'destroy')->name('destroy')->scopeBindings();
});
Route::get('/kwitansi/{pembayaran}', [PembayaranController::class, 'kwitansi'])->name('pembayaran.kwitansi');
Route::get('/kwitansi/{pembayaran}/unduh', [PembayaranController::class, 'kwitansiUnduh'])->name('pembayaran.kwitansi.unduh');

Route::get('/data-konsumen/cari', [KonsumenController::class, 'cari'])->name('konsumen.cari');
Route::resource('data-konsumen', KonsumenController::class)
    ->names('konsumen')
    ->parameters(['data-konsumen' => 'konsumen'])
    ->except(['create', 'edit']);

// --- Keuangan ---
Route::get('/rab-realisasi', [RabController::class, 'index'])->name('rab.index');
Route::post('/rab-realisasi', [RabController::class, 'store'])->name('rab.store');
Route::put('/rab-realisasi/{rab}', [RabController::class, 'update'])->name('rab.update');
Route::delete('/rab-realisasi/{rab}', [RabController::class, 'destroy'])->name('rab.destroy');
Route::patch('/rab-realisasi/{rab}/verifikasi', [RabController::class, 'verifikasi'])->name('rab.verifikasi');

Route::get('/kas-proyek', [KasProyekController::class, 'index'])->name('kas-proyek.index');
Route::post('/kas-proyek', [KasProyekController::class, 'store'])->name('kas-proyek.store');
Route::put('/kas-proyek/{kasTransaksi}', [KasProyekController::class, 'update'])->name('kas-proyek.update');
Route::delete('/kas-proyek/{kasTransaksi}', [KasProyekController::class, 'destroy'])->name('kas-proyek.destroy');
