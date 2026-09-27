<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique(); // TRX-2026-0001
            $table->date('tanggal');
            $table->enum('status', ['reservasi', 'booking', 'dp', 'lunas'])->default('reservasi');

            $table->foreignId('konsumen_id')->constrained('konsumens')->cascadeOnDelete();
            $table->foreignId('kavling_id')->constrained('kavlings')->cascadeOnDelete();
            $table->foreignId('agen_id')->nullable()->constrained('agens')->nullOnDelete();

            $table->enum('jenis_pembayaran', ['cash', 'angsuran'])->default('cash');
            $table->decimal('nilai_jual', 15, 2);
            $table->unsignedInteger('tenor')->nullable(); // dalam bulan, hanya utk angsuran
            $table->decimal('nominal_dp', 15, 2)->nullable();
            $table->decimal('total_bayar', 15, 2)->default(0);
            $table->decimal('sisa_pembayaran', 15, 2)->default(0);

            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_penjualans');
    }
};