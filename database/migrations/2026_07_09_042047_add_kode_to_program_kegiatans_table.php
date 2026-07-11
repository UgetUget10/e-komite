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
        Schema::table('tb_program_kegiatan', function (Blueprint $table) {
            $table->string('kode')->nullable()->after('judul_program');
            $table->string('kode_lengkap')->nullable()->after('kode')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_program_kegiatan', function (Blueprint $table) {
            $table->dropColumn(['kode', 'kode_lengkap']);
        });
    }
};
