<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Realisasi RAB diisi di form RAB. Saat baris final dicentang, realisasi dicatat sebagai satu pengeluaran Kas Proyek (asal "rab").
 *  - rabs.realisasi: nominal realisasi (kosong = belum direalisasikan),
 *  - rabs.kas_transaksi_id / dicentang_oleh / dicentang_pada: catatan kas hasil centang, siapa & kapan.
 * Item yang sudah tertaut ke kas keluar: realisasi = total kas itu dan langsung ditandai sudah masuk kas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rabs', function (Blueprint $table) {
            $table->decimal('realisasi', 15, 2)->nullable()->after('anggaran');
            $table->foreignId('kas_transaksi_id')->nullable()->after('catatan')->constrained('kas_transaksis')->nullOnDelete();
            $table->foreignId('dicentang_oleh')->nullable()->after('kas_transaksi_id')->constrained('users')->nullOnDelete();
            $table->timestamp('dicentang_pada')->nullable()->after('dicentang_oleh');
        });

        foreach (DB::table('rabs')->get() as $rab) {
            $kas = DB::table('kas_transaksis')->where('jenis', 'keluar')->where('rab_id', $rab->id)->orderBy('id')->get();
            if ($kas->isEmpty()) {
                continue;
            }
            DB::table('rabs')->where('id', $rab->id)->update([
                'realisasi'        => $kas->sum('nominal'),
                'kas_transaksi_id' => $kas->last()->id,
                'dicentang_pada'   => $kas->last()->created_at ?? now(),
            ]);
            // Catatan kas dari RAB hanya diubah lewat RAB agar angkanya tetap sama
            DB::table('kas_transaksis')->whereIn('id', $kas->pluck('id'))->where('asal', 'manual')->update(['asal' => 'rab']);
        }
    }

    public function down(): void
    {
        DB::table('kas_transaksis')->where('asal', 'rab')->update(['asal' => 'manual']);
        Schema::table('rabs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dicentang_oleh');
            $table->dropConstrainedForeignId('kas_transaksi_id');
            $table->dropColumn(['realisasi', 'dicentang_pada']);
        });
    }
};
