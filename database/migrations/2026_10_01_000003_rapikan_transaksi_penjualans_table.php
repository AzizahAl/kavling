<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Transaksi menjadi satu-satunya sumber data penjualan:
 * - harga dikunci saat transaksi (luas, harga/m², tahap),
 * - total bayar & sisa TIDAK disimpan lagi (dihitung dari tabel pembayarans),
 * - status: reservasi, booking, dp, angsuran, lunas, batal,
 * - satu kavling hanya boleh punya satu transaksi aktif (dijaga di level database).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('transaksi_penjualans')->exists()) {
            throw new RuntimeException('Tabel transaksi_penjualans masih berisi data uji. Backup lalu kosongkan dulu.');
        }

        Schema::table('transaksi_penjualans', function (Blueprint $table) {
            $table->dropForeign(['konsumen_id']);
            $table->dropForeign(['kavling_id']);
            $table->dropColumn(['total_bayar', 'sisa_pembayaran']);
        });

        Schema::table('transaksi_penjualans', function (Blueprint $table) {
            $table->enum('status', ['reservasi', 'booking', 'dp', 'angsuran', 'lunas', 'batal'])
                  ->default('reservasi')->change();

            // Hapus konsumen/kavling yang masih punya transaksi harus ditolak, bukan ikut terhapus.
            $table->foreign('konsumen_id')->references('id')->on('konsumens')->restrictOnDelete();
            $table->foreign('kavling_id')->references('id')->on('kavlings')->restrictOnDelete();

            // Harga terkunci
            $table->foreignId('skema_harga_id')->nullable()->after('agen_id')->constrained('skema_hargas')->nullOnDelete();
            $table->decimal('luas', 10, 2)->after('skema_harga_id');
            $table->decimal('harga_per_m2', 15, 2)->after('luas');

            // Biaya di luar harga kavling (nilai saat transaksi dibuat)
            $table->decimal('biaya_reservasi', 15, 2)->default(0)->after('nilai_jual');
            $table->decimal('biaya_booking', 15, 2)->default(0)->after('biaya_reservasi');

            // Pembatalan
            $table->date('tanggal_batal')->nullable()->after('catatan');
            $table->text('alasan_batal')->nullable()->after('tanggal_batal');

            $table->foreignId('dibuat_oleh')->nullable()->after('alasan_batal')->constrained('users')->nullOnDelete();

            // Kolom bantu: berisi kavling_id selama transaksi belum batal -> unik = 1 transaksi aktif per kavling.
            $table->unsignedBigInteger('kavling_aktif_id')->nullable()
                  ->storedAs("IF(`status` <> 'batal', `kavling_id`, NULL)");
            $table->unique('kavling_aktif_id', 'transaksi_kavling_aktif_unique');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_penjualans', function (Blueprint $table) {
            $table->dropUnique('transaksi_kavling_aktif_unique');
            $table->dropColumn('kavling_aktif_id');
            $table->dropConstrainedForeignId('dibuat_oleh');
            $table->dropConstrainedForeignId('skema_harga_id');
            $table->dropColumn(['luas', 'harga_per_m2', 'biaya_reservasi', 'biaya_booking', 'tanggal_batal', 'alasan_batal']);
            $table->dropForeign(['konsumen_id']);
            $table->dropForeign(['kavling_id']);
        });

        Schema::table('transaksi_penjualans', function (Blueprint $table) {
            $table->enum('status', ['reservasi', 'booking', 'dp', 'lunas'])->default('reservasi')->change();
            $table->decimal('total_bayar', 15, 2)->default(0);
            $table->decimal('sisa_pembayaran', 15, 2)->default(0);
            $table->foreign('konsumen_id')->references('id')->on('konsumens')->cascadeOnDelete();
            $table->foreign('kavling_id')->references('id')->on('kavlings')->cascadeOnDelete();
        });
    }
};
