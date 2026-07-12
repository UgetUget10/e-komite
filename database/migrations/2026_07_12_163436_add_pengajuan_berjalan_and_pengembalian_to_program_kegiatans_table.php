<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_program_kegiatan', function (Blueprint $table) {
            $table->decimal('total_pengajuan_berjalan_perprogram', 15, 2)->default(0)->after('total_pengeluaran_perprogram');
            $table->decimal('pengembalian_sisa_anggaran', 15, 2)->default(0)->after('sisa_anggaran_perprogram');
        });
    }

    public function down(): void
    {
        Schema::table('tb_program_kegiatan', function (Blueprint $table) {
            $table->dropColumn(['total_pengajuan_berjalan_perprogram', 'pengembalian_sisa_anggaran']);
        });
    }
};
