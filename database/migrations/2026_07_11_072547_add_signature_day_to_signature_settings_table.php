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
        Schema::table('signature_settings', function (Blueprint $table) {
            $table->integer('signature_day')->default(0)->after('signature_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signature_settings', function (Blueprint $table) {
            $table->dropColumn('signature_day');
        });
    }
};
