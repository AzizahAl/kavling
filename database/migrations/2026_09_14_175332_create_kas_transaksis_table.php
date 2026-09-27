<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas_transaksis', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('kode')->unique();
            $table->string('kategori'); // Penjualan, Marketing, Operasional, dll
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->string('uraian');
            $table->decimal('nominal', 15, 2);
            $table->string('sumber')->nullable(); // Sumber / Transaksi
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_transaksis');
    }
};