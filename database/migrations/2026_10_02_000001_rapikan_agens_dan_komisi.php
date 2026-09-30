<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Angka lead/prospek/closing/penjualan/komisi agen tidak lagi diketik manual:
 * dihitung dari tabel leads, transaksi_penjualans, dan komisi_pembayarans.
 */
return new class extends Migration
{
    public function up(): void
    {
        $adaAngka = DB::table('agens')->where(fn ($q) => $q->where('lead', '>', 0)->orWhere('prospek', '>', 0)
            ->orWhere('closing', '>', 0)->orWhere('dibayar', '>', 0))->exists();
        if ($adaAngka) {
            throw new RuntimeException('Tabel agens berisi angka yang diketik manual. Backup dulu sebelum kolom-kolom itu dihapus.');
        }

        Schema::table('agens', function (Blueprint $table) {
            $table->dropColumn(['lead', 'prospek', 'closing', 'nilai_penjualan', 'komisi_terhitung', 'dibayar', 'sisa_komisi']);
        });
        Schema::table('agens', function (Blueprint $table) {
            // null = pakai komisi bawaan di Pengaturan Proyek
            $table->decimal('komisi_persen', 5, 2)->nullable()->default(null)->change();
            $table->string('email')->nullable()->after('no_hp');
            $table->boolean('aktif')->default(true)->after('komisi_persen');
            $table->text('catatan')->nullable()->after('aktif');
        });

        // Pembayaran komisi ke agen (bisa bertahap). Setiap baris juga tercatat sebagai kas keluar.
        Schema::create('komisi_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agen_id')->constrained('agens')->restrictOnDelete();
            $table->foreignId('transaksi_id')->nullable()->constrained('transaksi_penjualans')->nullOnDelete();
            $table->date('tanggal');
            $table->decimal('nominal', 15, 2);
            $table->enum('metode', ['tunai', 'transfer'])->default('transfer');
            $table->text('catatan')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('kas_transaksis', function (Blueprint $table) {
            $table->foreignId('komisi_pembayaran_id')->nullable()->unique()->after('transaksi_id')
                  ->constrained('komisi_pembayarans')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kas_transaksis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('komisi_pembayaran_id');
        });
        Schema::dropIfExists('komisi_pembayarans');
        Schema::table('agens', function (Blueprint $table) {
            $table->dropColumn(['email', 'aktif', 'catatan']);
            $table->unsignedInteger('lead')->default(0);
            $table->unsignedInteger('prospek')->default(0);
            $table->unsignedInteger('closing')->default(0);
            $table->decimal('nilai_penjualan', 15, 2)->default(0);
            $table->decimal('komisi_terhitung', 15, 2)->default(0);
            $table->decimal('dibayar', 15, 2)->default(0);
            $table->decimal('sisa_komisi', 15, 2)->default(0);
        });
    }
};
