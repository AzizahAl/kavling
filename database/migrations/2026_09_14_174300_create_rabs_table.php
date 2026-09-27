<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rabs', function (Blueprint $table) {
            $table->id();
            $table->string('kategori'); // Tanah, Marketing, Cadangan, dll
            $table->string('uraian');
            $table->decimal('anggaran', 15, 2)->nullable();
            $table->decimal('realisasi', 15, 2)->default(0);
            $table->enum('status_realisasi', ['belum_direalisasikan', 'sudah_direalisasikan'])
                  ->default('belum_direalisasikan');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rabs');
    }
};