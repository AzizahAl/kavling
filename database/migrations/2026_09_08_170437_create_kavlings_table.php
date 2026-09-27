<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kavlings', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kavling')->unique();
            $table->string('blok');
            $table->string('no');
            $table->string('tipe')->nullable();
            $table->string('ukuran')->nullable();       // contoh: "7 x 14"
            $table->decimal('luas', 10, 2)->nullable();  // m2
            $table->decimal('harga_per_m2', 15, 2)->nullable();
            $table->decimal('harga_jual', 15, 2)->nullable();
            $table->enum('status', ['tersedia', 'reservasi', 'booking', 'dp', 'terjual'])
                  ->default('tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kavlings');
    }
};