<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kavlings', function (Blueprint $table) {
            $table->foreignId('skema_harga_id')
                  ->nullable()
                  ->after('tipe')
                  ->constrained('skema_hargas')   // ganti kalau nama tabel skema harga kamu beda
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kavlings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('skema_harga_id');
        });
    }
};