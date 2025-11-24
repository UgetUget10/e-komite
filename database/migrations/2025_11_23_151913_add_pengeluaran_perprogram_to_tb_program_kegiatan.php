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
            // total realisasi per program (default 0)
            $table->unsignedBigInteger('total_pengeluaran_perprogram')
                ->default(0)
                ->after('pagu_komite');

            // sisa anggaran = pagu - total (default 0)
            $table->unsignedBigInteger('sisa_anggaran_perprogram')
                ->default(0)
                ->after('total_pengeluaran_perprogram');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_program_kegiatan', function (Blueprint $table) {
            $table->dropColumn([
                'total_pengeluaran_perprogram',
                'sisa_anggaran_perprogram'
            ]);
        });
    }
};
