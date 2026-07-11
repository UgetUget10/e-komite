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
            $table->decimal('pagu_bos', 15, 2)->default(0)->after('pagu_dipa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_program_kegiatan', function (Blueprint $table) {
            $table->dropColumn('pagu_bos');
        });
    }
};
