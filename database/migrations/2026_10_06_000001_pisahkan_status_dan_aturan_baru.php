<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Arahan baru pemilik aturan proyek:
 *  - status pembayaran, status dokumen, dan status kavling dipisah + riwayat perubahan status,
 *  - transaksi tanpa uang masuk = "Reservasi – Menunggu Pembayaran" (kavling ditahan dengan batas waktu),
 *  - pembatalan dirinci per jenis uang (reservasi penuh, booking dipotong, DP/angsuran potongan diisi admin),
 *  - komisi agen nominal tetap (bukan persen),
 *  - modul kewajiban tanah (total kesepakatan + pembayaran ke pemilik lahan).
 * Data lama tidak dihapus; dipetakan ke status/kunci baru yang paling sesuai.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------- 1. Status pembayaran transaksi: tambah "menunggu" (Reservasi – Menunggu Pembayaran) ----------
        // Kolom bantu kavling_aktif_id bergantung pada status, jadi dilepas dulu lalu dipasang lagi.
        Schema::table('transaksi_penjualans', function (Blueprint $table) {
            $table->dropUnique('transaksi_kavling_aktif_unique');
            $table->dropColumn('kavling_aktif_id');
        });
        Schema::table('transaksi_penjualans', function (Blueprint $table) {
            $table->enum('status', ['menunggu', 'reservasi', 'booking', 'dp', 'angsuran', 'lunas', 'batal'])->default('menunggu')->change();
            $table->dateTime('batas_tahan')->nullable()->after('tanggal');
        });
        Schema::table('transaksi_penjualans', function (Blueprint $table) {
            $table->unsignedBigInteger('kavling_aktif_id')->nullable()->storedAs("IF(`status` <> 'batal', `kavling_id`, NULL)");
            $table->unique('kavling_aktif_id', 'transaksi_kavling_aktif_unique');
        });
        // Status "reservasi" lama tanpa uang masuk sama sekali → Menunggu Pembayaran
        DB::table('transaksi_penjualans')->where('status', 'reservasi')
            ->whereNotExists(fn ($q) => $q->from('pembayarans')->whereColumn('pembayarans.transaksi_id', 'transaksi_penjualans.id'))
            ->update(['status' => 'menunggu']);

        // ---------- 2. Status kavling: tambah "lunas" ----------
        Schema::table('kavlings', function (Blueprint $table) {
            $table->enum('status', ['tersedia', 'reservasi', 'booking', 'dp', 'lunas', 'terjual'])->default('tersedia')->change();
        });
        DB::table('kavlings')->where('status', 'dp')
            ->whereExists(fn ($q) => $q->from('transaksi_penjualans')->whereColumn('transaksi_penjualans.kavling_id', 'kavlings.id')->where('transaksi_penjualans.status', 'lunas'))
            ->update(['status' => 'lunas']);

        // ---------- 3. Riwayat perubahan status (hanya ditambah, tidak diubah/dihapus dari antarmuka) ----------
        Schema::create('status_riwayats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->nullable()->constrained('transaksi_penjualans')->cascadeOnDelete();
            $table->foreignId('kavling_id')->nullable()->constrained('kavlings')->cascadeOnDelete();
            $table->enum('jenis', ['pembayaran', 'dokumen', 'kavling', 'pembatalan']);
            $table->string('item', 20)->nullable();          // spk / ppjb / ajb untuk dokumen
            $table->string('dari', 30)->nullable();
            $table->string('ke', 30)->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('waktu')->useCurrent();
            $table->index(['transaksi_id', 'waktu']);
        });
        $sekarang = now();
        foreach (DB::table('transaksi_penjualans')->get() as $t) {
            DB::table('status_riwayats')->insert([
                'transaksi_id' => $t->id, 'kavling_id' => $t->kavling_id, 'jenis' => 'pembayaran',
                'dari' => null, 'ke' => $t->status, 'catatan' => 'Status awal saat riwayat mulai dicatat', 'waktu' => $sekarang,
            ]);
        }

        // ---------- 4. Pembatalan dirinci per jenis uang ----------
        Schema::create('pembatalan_transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->unique()->constrained('transaksi_penjualans')->cascadeOnDelete();
            $table->date('tanggal');
            $table->decimal('reservasi_dibayar', 15, 2)->default(0);
            $table->decimal('reservasi_refund', 15, 2)->default(0);
            $table->decimal('booking_dibayar', 15, 2)->default(0);
            $table->decimal('booking_potongan', 15, 2)->default(0);
            $table->decimal('booking_refund', 15, 2)->default(0);
            $table->decimal('pokok_dibayar', 15, 2)->default(0);       // DP + angsuran + pelunasan
            $table->decimal('pokok_potongan', 15, 2)->default(0);      // diisi admin (ketentuan PPJB)
            $table->decimal('pokok_refund', 15, 2)->default(0);
            $table->decimal('total_refund', 15, 2)->default(0);
            $table->text('dasar_ketentuan')->nullable();
            $table->text('alasan')->nullable();
            $table->boolean('kedaluwarsa')->default(false);
            $table->foreignId('kas_transaksi_id')->nullable()->constrained('kas_transaksis')->nullOnDelete();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        // Pembatalan lama (aturan persen) dipindahkan sebagai ringkasan total
        foreach (DB::table('transaksi_penjualans')->where('status', 'batal')->get() as $t) {
            $refund = DB::table('kas_transaksis')->where('transaksi_id', $t->id)->where('asal', 'refund')->first();
            DB::table('pembatalan_transaksis')->insert([
                'transaksi_id' => $t->id, 'tanggal' => $t->tanggal_batal ?? $t->updated_at, 'alasan' => $t->alasan_batal,
                'total_refund' => $refund->nominal ?? 0, 'kas_transaksi_id' => $refund->id ?? null,
                'dasar_ketentuan' => 'Data lama (dihitung dengan aturan persen sebelum arahan baru)',
                'created_at' => $sekarang, 'updated_at' => $sekarang,
            ]);
        }

        // Alokasi koreksi pembatalan (balik alokasi + potongan booking ke Marketing) menempel ke data pembatalan
        Schema::table('alokasi_kas', function (Blueprint $table) {
            $table->dropForeign(['kas_transaksi_id']);
        });
        Schema::table('alokasi_kas', function (Blueprint $table) {
            $table->unsignedBigInteger('kas_transaksi_id')->nullable()->change();
            $table->foreign('kas_transaksi_id')->references('id')->on('kas_transaksis')->cascadeOnDelete();
            $table->foreignId('pembatalan_id')->nullable()->after('kas_transaksi_id')->constrained('pembatalan_transaksis')->cascadeOnDelete();
        });

        // ---------- 5. Komisi nominal per agen (kosong = nominal bawaan di Pengaturan) ----------
        Schema::table('agens', function (Blueprint $table) {
            $table->decimal('komisi_nominal', 15, 2)->nullable()->after('komisi_persen');
        });

        // ---------- 6. Kewajiban tanah: pembayaran ke pemilik lahan ----------
        Schema::create('pembayaran_tanahs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->decimal('nominal', 15, 2);
            $table->string('keterangan');
            $table->enum('metode', ['tunai', 'transfer'])->default('transfer');
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::table('kas_transaksis', function (Blueprint $table) {
            $table->foreignId('pembayaran_tanah_id')->nullable()->unique()->after('komisi_pembayaran_id')
                  ->constrained('pembayaran_tanahs')->cascadeOnDelete();
        });

        // ---------- 7. Kunci pengaturan: petakan yang lama ke aturan baru ----------
        $nilai = DB::table('pengaturans')->pluck('nilai', 'kunci');
        $simpan = fn ($k, $v) => DB::table('pengaturans')->updateOrInsert(['kunci' => $k], ['nilai' => $v, 'updated_at' => $sekarang, 'created_at' => $sekarang]);
        if (isset($nilai['biaya_booking'], $nilai['refund_booking']) && $nilai['biaya_booking'] !== null && $nilai['refund_booking'] !== null) {
            $simpan('potongan_booking', max(0, (int) $nilai['biaya_booking'] - (int) $nilai['refund_booking']));
        }
        if (! empty($nilai['target_kewajiban_tanah'])) {
            $simpan('total_kewajiban_tanah', $nilai['target_kewajiban_tanah']);
        }
        DB::table('pengaturans')->whereIn('kunci', [
            'refund_reservasi', 'refund_booking', 'refund_dp_persen', 'refund_angsuran_persen', 'komisi_default_persen', 'target_kewajiban_tanah',
        ])->delete();
    }

    public function down(): void
    {
        Schema::table('kas_transaksis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pembayaran_tanah_id');
        });
        Schema::dropIfExists('pembayaran_tanahs');
        Schema::table('agens', fn (Blueprint $t) => $t->dropColumn('komisi_nominal'));
        Schema::table('alokasi_kas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pembatalan_id');
        });
        Schema::dropIfExists('pembatalan_transaksis');
        Schema::dropIfExists('status_riwayats');
        DB::table('kavlings')->where('status', 'lunas')->update(['status' => 'dp']);
        Schema::table('kavlings', fn (Blueprint $t) => $t->enum('status', ['tersedia', 'reservasi', 'booking', 'dp', 'terjual'])->default('tersedia')->change());
        DB::table('transaksi_penjualans')->where('status', 'menunggu')->update(['status' => 'reservasi']);
        Schema::table('transaksi_penjualans', function (Blueprint $table) {
            $table->dropUnique('transaksi_kavling_aktif_unique');
            $table->dropColumn(['kavling_aktif_id', 'batas_tahan']);
        });
        Schema::table('transaksi_penjualans', fn (Blueprint $t) => $t->enum('status', ['reservasi', 'booking', 'dp', 'angsuran', 'lunas', 'batal'])->default('reservasi')->change());
        Schema::table('transaksi_penjualans', function (Blueprint $table) {
            $table->unsignedBigInteger('kavling_aktif_id')->nullable()->storedAs("IF(`status` <> 'batal', `kavling_id`, NULL)");
            $table->unique('kavling_aktif_id', 'transaksi_kavling_aktif_unique');
        });
    }
};
