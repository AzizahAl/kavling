<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data tambahan agar formulir resmi (Form Reservasi, PPJB + Lampiran) bisa terisi otomatis:
 *  - metode bayar QRIS + data penyetor (A/N, bank, no. rekening) di Form Reservasi,
 *  - batas-batas kavling (Utara/Selatan/Timur/Barat) di Lampiran A PPJB.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->enum('metode', ['tunai', 'transfer', 'qris'])->default('transfer')->change();
            $table->string('nama_penyetor', 100)->nullable()->after('metode');
            $table->string('bank_penyetor', 60)->nullable()->after('nama_penyetor');
            $table->string('rekening_penyetor', 40)->nullable()->after('bank_penyetor');
        });

        Schema::table('kavlings', function (Blueprint $table) {
            $table->string('batas_utara', 150)->nullable()->after('catatan');
            $table->string('batas_selatan', 150)->nullable()->after('batas_utara');
            $table->string('batas_timur', 150)->nullable()->after('batas_selatan');
            $table->string('batas_barat', 150)->nullable()->after('batas_timur');
        });
    }

    public function down(): void
    {
        Schema::table('kavlings', function (Blueprint $table) {
            $table->dropColumn(['batas_utara', 'batas_selatan', 'batas_timur', 'batas_barat']);
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn(['nama_penyetor', 'bank_penyetor', 'rekening_penyetor']);
        });
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->enum('metode', ['tunai', 'transfer'])->default('transfer')->change();
        });
    }
};
