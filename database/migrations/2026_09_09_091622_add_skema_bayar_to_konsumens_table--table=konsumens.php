<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konsumens', function (Blueprint $table) {
            $table->string('skema_bayar')->nullable()->after('down_payment');
            $table->unsignedInteger('jumlah_angsuran')->nullable()->after('skema_bayar');
            $table->string('label')->nullable()->after('nama_lengkap'); // contoh: "Konsumen Prioritas"
        });
    }

    public function down(): void
    {
        Schema::table('konsumens', function (Blueprint $table) {
            $table->dropColumn(['skema_bayar', 'jumlah_angsuran', 'label']);
        });
    }
};