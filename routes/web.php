<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KavlingController;
use App\Http\Controllers\AgenController;
use App\Http\Controllers\SkemaHargaController;
use App\Http\Controllers\KonsumenController;
use App\Http\Controllers\TransaksiPenjualanController;
use App\Http\Controllers\RabController;
use App\Http\Controllers\KasProyekController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Resource ini mencakup: index, create, store, show, edit, update, destroy
Route::resource('master-kavling', KavlingController::class)->names('kavling');

// --- Data Master ---
Route::view('/proyek', 'proyek.index')->name('proyek.index');
Route::get('/skema-harga', [SkemaHargaController::class, 'index'])->name('skema-harga.index');
Route::post('/skema-harga', [SkemaHargaController::class, 'store'])->name('skema-harga.store');
Route::put('/skema-harga/{skemaHarga}', [SkemaHargaController::class, 'update'])->name('skema-harga.update');
Route::delete('/skema-harga/{skemaHarga}', [SkemaHargaController::class, 'destroy'])->name('skema-harga.destroy');

Route::get('/data-agen', [AgenController::class, 'index'])->name('agen.index');
Route::post('/data-agen', [AgenController::class, 'store'])->name('agen.store');
Route::get('/data-agen/{agen}', [AgenController::class, 'show'])->name('agen.show');
Route::get('/data-agen/{agen}/edit', [AgenController::class, 'edit'])->name('agen.edit');
Route::put('/data-agen/{agen}', [AgenController::class, 'update'])->name('agen.update');
Route::delete('/data-agen/{agen}', [AgenController::class, 'destroy'])->name('agen.destroy');

// --- Penjualan ---
Route::prefix('transaksi-penjualan')->name('transaksi-penjualan.')->group(function () {
    Route::get('/', [TransaksiPenjualanController::class, 'index'])->name('index');
    Route::post('/', [TransaksiPenjualanController::class, 'store'])->name('store');
    Route::get('/cari-konsumen', [TransaksiPenjualanController::class, 'searchKonsumen'])->name('cari-konsumen');
});

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

Route::view('/alokasi-cashflow', 'cashflow.index')->name('cashflow.index');

Route::get('/data-konsumen', [KonsumenController::class, 'index'])->name('konsumen.index');
Route::post('/data-konsumen', [KonsumenController::class, 'store'])->name('konsumen.store');
Route::get('/data-konsumen/{konsumen}', [KonsumenController::class, 'show'])->name('konsumen.show');
Route::get('/data-konsumen/{konsumen}/kwitansi/{riwayat}', [KonsumenController::class, 'kwitansi'])->name('konsumen.kwitansi');
Route::get('/data-konsumen/{konsumen}/buat-spk', [KonsumenController::class, 'buatSpk'])->name('konsumen.buat-spk');