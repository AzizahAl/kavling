<?php

use App\Http\Controllers\AgenController;
use App\Http\Controllers\AngsuranController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashflowController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\FormulirController;
use App\Http\Controllers\KasProyekController;
use App\Http\Controllers\KavlingController;
use App\Http\Controllers\KonsumenController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\ProyekController;
use App\Http\Controllers\RabController;
use App\Http\Controllers\SkemaHargaController;
use App\Http\Controllers\TransaksiPenjualanController;
use Illuminate\Support\Facades\Route;

// ================= Tamu =================
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'formMasuk'])->name('login');
    Route::post('/masuk', [AuthController::class, 'masuk'])->middleware('throttle:20,1')->name('login.proses');
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthController::class, 'keluar'])->name('logout');
    Route::get('/', [AuthController::class, 'beranda'])->name('beranda');
    Route::get('/profil', [AuthController::class, 'profil'])->name('profil');
    Route::put('/profil', [AuthController::class, 'ubahProfil'])->name('profil.update');

    // ================= Admin & Agen =================
    // Agen hanya melihat/mengelola data miliknya (dicek di controller).
    Route::middleware('peran:admin,agen')->group(function () {
        Route::prefix('lead-harian')->name('lead.')->controller(LeadController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/rekap', 'rekap')->name('rekap');
            Route::post('/', 'store')->name('store');
            Route::put('/{lead}', 'update')->name('update');
            Route::delete('/{lead}', 'destroy')->name('destroy');
            Route::post('/{lead}/prospek', 'prospek')->name('prospek');
            Route::post('/{lead}/closing', 'closing')->name('closing');
            Route::post('/{lead}/mundur', 'mundur')->name('mundur');
        });

        // Formulir kosong & Marketing Toolkit

        Route::get('/data-agen/{agen}', [AgenController::class, 'show'])->whereNumber('agen')->name('agen.show');
        Route::get('/transaksi-penjualan/{transaksi}', [TransaksiPenjualanController::class, 'show'])->whereNumber('transaksi')->name('transaksi-penjualan.show');

        // Transaksi Penjualan: agen melihat, membuat, dan mencatat pembayaran transaksi miliknya sendiri
        Route::get('/transaksi-penjualan', [TransaksiPenjualanController::class, 'index'])->name('transaksi-penjualan.index');
        Route::get('/transaksi-penjualan/baru', [TransaksiPenjualanController::class, 'create'])->name('transaksi-penjualan.create');
        Route::post('/transaksi-penjualan', [TransaksiPenjualanController::class, 'store'])->name('transaksi-penjualan.store');
        Route::post('/transaksi-penjualan/{transaksi}/pembayaran', [PembayaranController::class, 'store'])->name('pembayaran.store');
        Route::get('/kwitansi/{pembayaran}', [PembayaranController::class, 'kwitansi'])->name('pembayaran.kwitansi');
        Route::get('/kwitansi/{pembayaran}/unduh', [PembayaranController::class, 'kwitansiUnduh'])->name('pembayaran.kwitansi.unduh');
        Route::get('/data-konsumen/cari', [KonsumenController::class, 'cari'])->name('konsumen.cari');
    });

    // ================= Admin =================
    Route::middleware('peran:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // --- Data Master ---
        Route::get('/pengaturan-proyek', [ProyekController::class, 'index'])->name('proyek.index');
        Route::put('/pengaturan-proyek', [ProyekController::class, 'update'])->name('proyek.update');
        Route::get('/pengaturan-proyek/formulir/{jenis}', [FormulirController::class, 'lihat'])->name('formulir.lihat');
        Route::get('/pengaturan-proyek/formulir/{jenis}/unduh', [FormulirController::class, 'unduh'])->name('formulir.unduh');

        Route::resource('master-kavling', KavlingController::class)
            ->names('kavling')->parameters(['master-kavling' => 'kavling'])->except(['create', 'edit']);

        Route::resource('skema-harga', SkemaHargaController::class)
            ->parameters(['skema-harga' => 'skemaHarga'])->only(['index', 'store', 'update', 'destroy']);

        Route::get('/data-agen/next-kode', [AgenController::class, 'nextKode'])->name('agen.next-kode');
        Route::resource('data-agen', AgenController::class)
            ->names('agen')->parameters(['data-agen' => 'agen'])->except(['create', 'edit', 'show']);
        Route::patch('/data-agen/{agen}/nonaktifkan', [AgenController::class, 'nonaktifkan'])->name('agen.nonaktifkan');
        Route::post('/data-agen/{agen}/komisi', [AgenController::class, 'bayarKomisi'])->name('agen.komisi.store');
        Route::delete('/data-agen/{agen}/komisi/{pembayaran}', [AgenController::class, 'hapusBayarKomisi'])->name('agen.komisi.destroy');

        // --- Penjualan ---
        Route::prefix('transaksi-penjualan')->name('transaksi-penjualan.')->controller(TransaksiPenjualanController::class)->group(function () {
            Route::get('/{transaksi}/ubah', 'edit')->name('edit');
            Route::put('/{transaksi}', 'update')->name('update');
            Route::post('/{transaksi}/batal', 'batal')->name('batal');
        });

        Route::prefix('transaksi-penjualan/{transaksi}/pembayaran')->name('pembayaran.')->controller(PembayaranController::class)->group(function () {
            Route::put('/{pembayaran}', 'update')->name('update')->scopeBindings();
            Route::delete('/{pembayaran}', 'destroy')->name('destroy')->scopeBindings();
        });
        Route::get('/angsuran-piutang', [AngsuranController::class, 'index'])->name('angsuran.index');

        Route::put('/data-konsumen/dokumen/{checklist}', [KonsumenController::class, 'updateDokumen'])->name('konsumen.dokumen.update');
        Route::resource('data-konsumen', KonsumenController::class)
            ->names('konsumen')->parameters(['data-konsumen' => 'konsumen'])->except(['create', 'edit']);

        // --- Dokumen ---
        Route::get('/transaksi-penjualan/{transaksi}/dokumen/{jenis}', [DokumenController::class, 'lihat'])->name('dokumen.lihat');
        Route::get('/transaksi-penjualan/{transaksi}/dokumen/{jenis}/unduh', [DokumenController::class, 'unduh'])->name('dokumen.unduh');

        // --- Keuangan ---
        Route::get('/rab-realisasi', [RabController::class, 'index'])->name('rab.index');
        Route::post('/rab-realisasi', [RabController::class, 'store'])->name('rab.store');
        Route::put('/rab-realisasi/{rab}', [RabController::class, 'update'])->name('rab.update');
        Route::delete('/rab-realisasi/{rab}', [RabController::class, 'destroy'])->name('rab.destroy');
        Route::post('/rab-realisasi/centang-semua', [RabController::class, 'centangSemua'])->name('rab.centang-semua');
        Route::post('/rab-realisasi/{rab}/centang', [RabController::class, 'centang'])->name('rab.centang');
        Route::delete('/rab-realisasi/{rab}/centang', [RabController::class, 'batalCentang'])->name('rab.batal-centang');

        Route::get('/kas-proyek', [KasProyekController::class, 'index'])->name('kas-proyek.index');
        Route::post('/kas-proyek', [KasProyekController::class, 'store'])->name('kas-proyek.store');
        Route::put('/kas-proyek/{kasTransaksi}', [KasProyekController::class, 'update'])->name('kas-proyek.update');
        Route::delete('/kas-proyek/{kasTransaksi}', [KasProyekController::class, 'destroy'])->name('kas-proyek.destroy');

        Route::get('/alokasi-cashflow', [CashflowController::class, 'index'])->name('cashflow.index');

    });
});
