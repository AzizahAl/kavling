<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agens', function (Blueprint $table) {
            $table->id();
            $table->string('kode_agen')->unique();
            $table->string('nama_agen');
            $table->string('no_hp')->nullable();
            $table->unsignedInteger('lead')->default(0);
            $table->unsignedInteger('prospek')->default(0);
            $table->unsignedInteger('closing')->default(0);
            $table->decimal('nilai_penjualan', 15, 2)->default(0);
            $table->decimal('komisi_persen', 5, 2)->default(0);
            $table->decimal('komisi_terhitung', 15, 2)->default(0);
            $table->decimal('dibayar', 15, 2)->default(0);
            $table->decimal('sisa_komisi', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agens');
    }
};