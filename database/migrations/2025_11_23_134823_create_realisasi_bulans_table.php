<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tb_realisasi_bulan', function (Blueprint $table) {
            $table->uuid('id')->primary();    // UUID
            $table->string('nama_bulan');     // Nama bulan
            $table->softDeletes();            // Soft delete
            $table->timestamps();             // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_realisasi_bulan');
    }
};
