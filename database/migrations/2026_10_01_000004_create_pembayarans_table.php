<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Setiap uang masuk dari konsumen, selalu terikat ke satu transaksi.
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();                    // PAY-2026-0001 (juga nomor kwitansi)
            $table->foreignId('transaksi_id')->constrained('transaksi_penjualans')->restrictOnDelete();
            $table->date('tanggal');
            $table->enum('jenis', ['reservasi', 'booking', 'dp', 'angsuran', 'pelunasan']);
            $table->decimal('nominal', 15, 2);
            $table->enum('metode', ['tunai', 'transfer'])->default('transfer');
            $table->string('no_bukti')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['transaksi_id', 'jenis']);
            $table->index('tanggal');
        });

        // Jadwal cicilan per transaksi angsuran. Status tiap cicilan dihitung dari pembayaran.
        Schema::create('jadwal_angsurans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksi_penjualans')->cascadeOnDelete();
            $table->unsignedSmallInteger('ke');
            $table->date('jatuh_tempo');
            $table->decimal('nominal', 15, 2);
            $table->timestamps();

            $table->unique(['transaksi_id', 'ke']);
            $table->index('jatuh_tempo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_angsurans');
        Schema::dropIfExists('pembayarans');
    }
};
