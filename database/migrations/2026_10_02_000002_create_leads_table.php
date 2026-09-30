<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lead harian marketing: Lead → Prospek → Closing.
 * Tanggal tiap tahap disimpan agar bisa direkap per hari / minggu / bulan per agen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();                         // LD-2026-0001
            $table->string('nama');
            $table->string('no_hp', 20)->nullable();
            $table->string('domisili')->nullable();
            $table->enum('sumber', ['internet', 'flyer', 'penawaran_langsung', 'kenalan']);
            $table->enum('tahap', ['lead', 'prospek', 'closing'])->default('lead');
            $table->date('tanggal_lead');
            $table->date('tanggal_prospek')->nullable();
            $table->date('tanggal_closing')->nullable();
            $table->foreignId('agen_id')->constrained('agens')->restrictOnDelete();          // penanggung jawab
            $table->foreignId('diinput_oleh')->nullable()->constrained('users')->nullOnDelete(); // penginput
            $table->foreignId('kavling_minat_id')->nullable()->constrained('kavlings')->nullOnDelete();
            $table->foreignId('transaksi_id')->nullable()->unique()->constrained('transaksi_penjualans')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['agen_id', 'tanggal_lead']);
            $table->index(['agen_id', 'tanggal_prospek']);
            $table->index(['agen_id', 'tanggal_closing']);
        });

        // Jejak setiap perubahan tahap (siapa & kapan)
        Schema::create('lead_riwayats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->string('dari', 20)->nullable();
            $table->string('ke', 20);
            $table->date('tanggal');
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_riwayats');
        Schema::dropIfExists('leads');
    }
};
