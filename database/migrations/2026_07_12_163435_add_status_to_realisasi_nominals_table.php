<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_realisasi_nominal', function (Blueprint $table) {
            $table->string('status', 20)->default('selesai')->after('nominal');
        });
    }

    public function down(): void
    {
        Schema::table('tb_realisasi_nominal', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
