<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signature_settings', function (Blueprint $table) {
            $table->integer('alert_threshold')->default(80)->comment('Alert threshold in percentage (80 = 80%)')->after('signature_year');
        });
    }

    public function down(): void
    {
        Schema::table('signature_settings', function (Blueprint $table) {
            $table->dropColumn('alert_threshold');
        });
    }
};
