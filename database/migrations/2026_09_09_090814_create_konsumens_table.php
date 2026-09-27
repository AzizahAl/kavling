<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konsumens', function (Blueprint $table) {
            $table->id();
            $table->string('id_konsumen')->unique(); // CUS-2026-0001
            $table->string('nama_lengkap');
            $table->string('nik', 16);
            $table->string('no_hp');
            $table->string('email')->nullable();
            $table->text('alamat');

            $table->foreignId('kavling_id')->constrained('kavlings');
            $table->foreignId('agen_id')->nullable()->constrained('agens')->nullOnDelete();

            $table->enum('status_transaksi', ['reservasi', 'booking', 'dp', 'cash_lunas', 'angsuran']);
            $table->date('tanggal_transaksi');

            $table->decimal('nominal_reservasi', 15, 2)->default(0);
            $table->decimal('nominal_booking', 15, 2)->default(0);
            $table->decimal('down_payment', 15, 2)->default(0);

            $table->enum('status_reservasi', ['belum', 'proses', 'selesai'])->default('belum');
            $table->enum('status_booking', ['belum', 'proses', 'selesai'])->default('belum');
            $table->enum('status_ppjb', ['belum', 'proses', 'selesai'])->default('belum');
            $table->enum('status_ajb', ['belum', 'proses', 'selesai'])->default('belum');

            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konsumens');
    }
};