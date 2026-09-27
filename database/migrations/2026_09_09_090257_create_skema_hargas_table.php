<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skema_hargas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tahap');
            $table->unsignedInteger('unit_mulai');
            $table->unsignedInteger('unit_sampai');
            $table->decimal('harga_per_m2', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skema_hargas');
    }
};