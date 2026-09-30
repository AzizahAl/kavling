<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kas_transaksis', function (Blueprint $table) {
            // Asal baris kas: manual (diinput admin), pembayaran (otomatis), refund (otomatis dari pembatalan)
            $table->string('asal', 20)->default('manual')->after('jenis');
            $table->foreignId('pembayaran_id')->nullable()->unique()->after('asal')
                  ->constrained('pembayarans')->cascadeOnDelete();
            $table->foreignId('transaksi_id')->nullable()->after('pembayaran_id')
                  ->constrained('transaksi_penjualans')->nullOnDelete();
            $table->index(['jenis', 'tanggal']);
        });

        // Riwayat pembayaran lama (menempel ke konsumen) digantikan tabel pembayarans.
        if (DB::table('riwayat_pembayarans')->exists()) {
            throw new RuntimeException('Tabel riwayat_pembayarans masih berisi data. Backup lalu kosongkan dulu.');
        }
        Schema::dropIfExists('riwayat_pembayarans');
    }

    public function down(): void
    {
        Schema::create('riwayat_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konsumen_id')->constrained('konsumens')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('keterangan');
            $table->decimal('nominal', 15, 2);
            $table->string('bukti_path')->nullable();
            $table->timestamps();
        });

        Schema::table('kas_transaksis', function (Blueprint $table) {
            $table->dropIndex(['jenis', 'tanggal']);
            $table->dropConstrainedForeignId('transaksi_id');
            $table->dropConstrainedForeignId('pembayaran_id');
            $table->dropColumn('asal');
        });
    }
};
