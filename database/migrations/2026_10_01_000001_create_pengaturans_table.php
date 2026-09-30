<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pengaturan proyek berbentuk kunci => nilai, diubah lewat halaman Pengaturan Proyek.
        Schema::create('pengaturans', function (Blueprint $table) {
            $table->id();
            $table->string('kunci', 100)->unique();
            $table->text('nilai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturans');
    }
};
