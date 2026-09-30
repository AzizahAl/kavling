<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - Setiap kas masuk dari konsumen dialokasikan ke 5 pos (persen saat uang diterima disimpan).
 * - Setiap kas keluar dibebankan ke satu pos dan (opsional) ke satu item RAB.
 * - Realisasi RAB = jumlah kas keluar yang ditautkan ke item itu (tidak diketik manual lagi).
 */
return new class extends Migration
{
    private const POS_RAB = [
        'Tanah' => 'tanah', 'Legalitas' => 'legal_infra', 'Pematangan Lahan' => 'legal_infra',
        'Infrastruktur' => 'legal_infra', 'Fasilitas' => 'legal_infra', 'Marketing' => 'marketing',
        'Operasional' => 'operasional', 'Cadangan' => 'cadangan',
    ];

    public function up(): void
    {
        Schema::create('alokasi_kas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kas_transaksi_id')->constrained('kas_transaksis')->cascadeOnDelete();
            $table->enum('pos', ['tanah', 'legal_infra', 'marketing', 'cadangan', 'operasional']);
            $table->decimal('persen', 5, 2);
            $table->decimal('nominal', 15, 2);      // negatif untuk refund
            $table->timestamps();
            $table->unique(['kas_transaksi_id', 'pos']);
        });

        Schema::table('rabs', function (Blueprint $table) {
            $table->enum('pos', ['tanah', 'legal_infra', 'marketing', 'cadangan', 'operasional'])->nullable()->after('kategori');
        });
        foreach (self::POS_RAB as $kategori => $pos) {
            DB::table('rabs')->where('kategori', $kategori)->update(['pos' => $pos]);
        }

        Schema::table('kas_transaksis', function (Blueprint $table) {
            $table->enum('pos', ['tanah', 'legal_infra', 'marketing', 'cadangan', 'operasional'])->nullable()->after('kategori');
            $table->foreignId('rab_id')->nullable()->after('pos')->constrained('rabs')->nullOnDelete();
        });

        // Tautkan kas keluar lama ke item RAB yang uraiannya sama, lalu pindahkan realisasi manual ke kas.
        foreach (DB::table('rabs')->get() as $rab) {
            $kas = DB::table('kas_transaksis')->where('jenis', 'keluar')->where('uraian', $rab->uraian)->whereNull('rab_id')->get();
            if ($kas->isNotEmpty()) {
                DB::table('kas_transaksis')->whereIn('id', $kas->pluck('id'))->update(['rab_id' => $rab->id, 'pos' => $rab->pos]);
            } elseif ((float) $rab->realisasi > 0) {
                throw new RuntimeException("RAB \"{$rab->uraian}\" punya realisasi manual tanpa catatan kas keluar. Catat dulu di Kas Proyek sebelum migrasi.");
            }
        }
        DB::table('kas_transaksis')->where('jenis', 'keluar')->where('asal', 'komisi')->update(['pos' => 'marketing']);

        Schema::table('rabs', function (Blueprint $table) {
            $table->dropColumn(['realisasi', 'status_realisasi']);
        });
    }

    public function down(): void
    {
        Schema::table('rabs', function (Blueprint $table) {
            $table->decimal('realisasi', 15, 2)->default(0);
            $table->string('status_realisasi')->default('belum_direalisasikan');
            $table->dropColumn('pos');
        });
        Schema::table('kas_transaksis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rab_id');
            $table->dropColumn('pos');
        });
        Schema::dropIfExists('alokasi_kas');
    }
};
