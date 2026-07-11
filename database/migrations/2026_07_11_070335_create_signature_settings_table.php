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
        Schema::create('signature_settings', function (Blueprint $table) {
            $table->id();
            $table->string('ketua_komite_name')->default('Prof. Dr. H. Nur Ali, M.Pd');
            $table->string('kepala_sekolah_name')->default('Dra. Erni Qomaria Rida, M.Pd.');
            $table->string('signature_month')->default('Januari');
            $table->integer('signature_year')->default(2025);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signature_settings');
    }
};
