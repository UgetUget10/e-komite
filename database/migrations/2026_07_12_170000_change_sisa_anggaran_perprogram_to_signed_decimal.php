<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Sisa anggaran sekarang bisa negatif (menandakan kelebihan pakai/overspend),
        // konsisten dengan formula: Sisa = Pagu Komite - Realisasi - Pengajuan Berjalan + Pengembalian.
        // unsignedBigInteger sebelumnya tidak bisa menyimpan nilai negatif.
        //
        // Menggunakan raw SQL (bukan Schema::table(...)->change()) karena package
        // doctrine/dbal yang dibutuhkan Blueprint::change() tidak terinstall di project ini.
        DB::statement('ALTER TABLE `tb_program_kegiatan` MODIFY `sisa_anggaran_perprogram` DECIMAL(15,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE `tb_program_kegiatan` MODIFY `total_pengeluaran_perprogram` DECIMAL(15,2) NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `tb_program_kegiatan` MODIFY `sisa_anggaran_perprogram` BIGINT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE `tb_program_kegiatan` MODIFY `total_pengeluaran_perprogram` BIGINT UNSIGNED NOT NULL DEFAULT 0');
    }
};
