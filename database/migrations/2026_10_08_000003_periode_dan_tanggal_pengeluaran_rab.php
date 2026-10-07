<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * RAB per periode (bulan, YYYY-MM) dan tanggal pengeluaran yang dipakai sebagai tanggal catatan Kas Proyek.
 * Data lama: periode = bulan dibuat; tanggal pengeluaran baris yang sudah masuk kas = tanggal catatan kasnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rabs', function (Blueprint $table) {
            $table->char('periode', 7)->nullable()->after('id')->index();
            $table->date('tanggal_pengeluaran')->nullable()->after('realisasi');
        });

        foreach (DB::table('rabs')->get() as $rab) {
            $kasTanggal = $rab->kas_transaksi_id ? DB::table('kas_transaksis')->where('id', $rab->kas_transaksi_id)->value('tanggal') : null;
            DB::table('rabs')->where('id', $rab->id)->update([
                'periode'             => substr($kasTanggal ?? $rab->created_at ?? now()->toDateString(), 0, 7),
                'tanggal_pengeluaran' => $kasTanggal,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('rabs', function (Blueprint $table) {
            $table->dropColumn(['periode', 'tanggal_pengeluaran']);
        });
    }
};
