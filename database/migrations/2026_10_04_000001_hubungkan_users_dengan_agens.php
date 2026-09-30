<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Akun berperan "agen" terhubung ke satu data agen; hanya melihat & mengelola data milik agen itu.
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('agen_id')->nullable()->unique()->after('role')->constrained('agens')->nullOnDelete();
            $table->timestamp('terakhir_masuk')->nullable()->after('aktif');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agen_id');
            $table->dropColumn('terakhir_masuk');
        });
    }
};
