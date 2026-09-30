<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Konsumen hanya menyimpan identitas. Kavling, harga, reservasi, booking, DP, tenor,
 * dan status dokumen pindah ke transaksi_penjualans / pembayarans / checklist legal.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('konsumens')->exists()) {
            throw new RuntimeException(
                'Tabel konsumens masih berisi data. Backup lalu kosongkan dulu (lihat storage/backups), '
                . 'karena kolom transaksi di tabel ini akan dipindahkan ke transaksi_penjualans.'
            );
        }

        Schema::table('konsumens', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kavling_id');
            $table->dropConstrainedForeignId('agen_id');
            $table->dropColumn([
                'status_transaksi', 'tanggal_transaksi',
                'nominal_reservasi', 'nominal_booking', 'down_payment',
                'skema_bayar', 'jumlah_angsuran',
                'status_reservasi', 'status_booking', 'status_ppjb', 'status_ajb',
            ]);
        });

        Schema::table('konsumens', function (Blueprint $table) {
            $table->string('pekerjaan')->nullable()->after('alamat');
        });
    }

    public function down(): void
    {
        Schema::table('konsumens', function (Blueprint $table) {
            $table->dropColumn('pekerjaan');
            $table->foreignId('kavling_id')->nullable()->constrained('kavlings');
            $table->foreignId('agen_id')->nullable()->constrained('agens')->nullOnDelete();
            $table->string('status_transaksi')->nullable();
            $table->date('tanggal_transaksi')->nullable();
            $table->decimal('nominal_reservasi', 15, 2)->default(0);
            $table->decimal('nominal_booking', 15, 2)->default(0);
            $table->decimal('down_payment', 15, 2)->default(0);
            $table->string('skema_bayar')->nullable();
            $table->unsignedInteger('jumlah_angsuran')->nullable();
            $table->string('status_reservasi')->default('belum');
            $table->string('status_booking')->default('belum');
            $table->string('status_ppjb')->default('belum');
            $table->string('status_ajb')->default('belum');
        });
    }
};
