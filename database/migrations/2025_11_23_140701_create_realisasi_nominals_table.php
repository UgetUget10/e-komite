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
        Schema::create('tb_realisasi_nominal', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('fk_id_standar');
            $table->uuid('fk_id_program_kegiatan');
            $table->uuid('fk_id_pagu_tahun_anggaran');
            $table->uuid('fk_id_realisasi_bulan');

            // Uang — integer
            $table->unsignedBigInteger('nominal')->default(0);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_realisasi_nominal');
    }
};
