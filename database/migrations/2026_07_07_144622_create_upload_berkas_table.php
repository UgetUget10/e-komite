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
        Schema::create('tb_upload_berkas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('judul');
            $table->text('keterangan')->nullable();
            $table->date('tanggal');
            $table->string('berkas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_upload_berkas');
    }
};
