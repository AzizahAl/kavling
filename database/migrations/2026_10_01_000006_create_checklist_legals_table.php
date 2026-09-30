<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Checklist legal per transaksi (Reservasi, Booking/SPK, PPJB, AJB).
        // PPJB "selesai" = PPJB ditandatangani = kavling berstatus Terjual.
        Schema::create('checklist_legals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->unique()->constrained('transaksi_penjualans')->cascadeOnDelete();
            foreach (['reservasi', 'spk', 'ppjb', 'ajb'] as $item) {
                $table->enum("{$item}_status", ['belum', 'proses', 'selesai'])->default('belum');
                $table->date("{$item}_tanggal")->nullable();
            }
            $table->string('nomor_spk')->nullable()->unique();
            $table->string('nomor_ppjb')->nullable()->unique();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_legals');
    }
};
