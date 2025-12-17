<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaguAnggaranOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin');
        $isPelaksanaKomite = $user->hasRole('Pelaksana Komite'); // Sesuaikan jika nama role berbeda
        $isPrivileged = $isSuperAdmin || $isPelaksanaKomite;

        // Ambil pagu aktif atau terbaru
        $pagu = DB::table('komite.tb_pagu_tahun_anggaran')
            ->where('status_aktif', 1)
            ->first();

        if (!$pagu) {
            $pagu = DB::table('komite.tb_pagu_tahun_anggaran')
                ->orderByDesc('tahun')
                ->first();
        }

        if (!$pagu) {
            return [
                Stat::make('Total Pendapatan', 'Tidak ada data pagu')
                    ->description('Belum ada pagu anggaran')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
                Stat::make('Total Keperluan Dana', 'Rp. 0'),
                Stat::make('Sisa Anggaran', 'Rp. 0'),
            ];
        }

        $tahun     = $pagu->tahun;
        $totalPagu = $pagu->nominal_dana ?? 0;
        $isAktif   = $pagu->status_aktif == 1;

        // Format rupiah
        $format = fn($value) => 'Rp. ' . number_format($value, 0, ',', '.');

        // Base query program kegiatan untuk pagu aktif
        $baseQuery = DB::table('komite.tb_program_kegiatan')
            ->where('fk_id_pagu_tahun_anggaran', $pagu->id);

        if ($isPrivileged) {
            // SUPER ADMIN / PELAKSANA KOMITE
            $paguDiajukan = $baseQuery->sum(DB::raw('pagu_dipa + pagu_komite')) ?? 0;
            $sisaAnggaran = $totalPagu - $paguDiajukan; // Pendapatan - Keperluan Dana

            $descDiajukan = 'Semua Standar';
            $iconDiajukan = 'heroicon-m-shield-check';
            $colorDiajukan = 'success';

            $descSisa = $sisaAnggaran < 0 ? 'Melebihi Anggaran!' : ($sisaAnggaran == 0 ? 'Habis' : 'Tersedia') . ' - Keseluruhan';
            $iconSisa = $sisaAnggaran < 0 ? 'heroicon-m-exclamation-triangle' : ($sisaAnggaran == 0 ? 'heroicon-m-minus-circle' : 'heroicon-m-banknotes');
            $colorSisa = $sisaAnggaran < 0 ? 'danger' : ($sisaAnggaran == 0 ? 'warning' : 'success');
        } else {
            // USER BIASA → Hanya pagu_komite dan sisa dari field
            $handledStandarIds = DB::table('komite.tb_standar_user')
                ->where('user_id', $user->id)
                ->pluck('standar_id')
                ->toArray();

            if (empty($handledStandarIds)) {
                $paguDiajukan = 0;
                $sisaAnggaran = 0;

                $descDiajukan = 'Tidak ada akses standar';
                $iconDiajukan = 'heroicon-m-lock-closed';
                $colorDiajukan = 'gray';

                $descSisa = 'Tidak ada data standar';
                $iconSisa = 'heroicon-m-lock-closed';
                $colorSisa = 'gray';
            } else {
                $allowedProgramIds = DB::table('komite.tb_uraian_program')
                    ->whereIn('id_standar', $handledStandarIds)
                    ->distinct()
                    ->pluck('id_program_kegiatan')
                    ->toArray();

                if (empty($allowedProgramIds)) {
                    $paguDiajukan = 0;
                    $sisaAnggaran = 0;

                    $descDiajukan = 'Standar Anda (belum ada program)';
                    $iconDiajukan = 'heroicon-m-information-circle';
                    $colorDiajukan = 'warning';

                    $descSisa = 'Belum ada program - Standar Anda';
                    $iconSisa = 'heroicon-m-information-circle';
                    $colorSisa = 'warning';
                } else {
                    $filteredQuery = $baseQuery->whereIn('id', $allowedProgramIds);

                    $paguDiajukan = $filteredQuery->sum('pagu_komite') ?? 0; // Hanya pagu_komite
                    $sisaAnggaran = $filteredQuery->sum('sisa_anggaran_perprogram') ?? 0;

                    $descDiajukan = 'Standar Anda';
                    $iconDiajukan = 'heroicon-m-arrow-up-tray';
                    $colorDiajukan = 'primary';

                    $descSisa = ($sisaAnggaran < 0 ? 'Melebihi Anggaran!' : ($sisaAnggaran == 0 ? 'Habis' : 'Tersedia')) . ' - Standar Anda';
                    $iconSisa = $sisaAnggaran < 0 ? 'heroicon-m-exclamation-triangle' : ($sisaAnggaran == 0 ? 'heroicon-m-minus-circle' : 'heroicon-m-banknotes');
                    $colorSisa = $sisaAnggaran < 0 ? 'danger' : ($sisaAnggaran == 0 ? 'warning' : 'success');
                }
            }
        }

        return [
            Stat::make("Total Pendapatan {$tahun}", $format($totalPagu))
                ->description($isAktif ? 'Aktif' : 'Tidak Aktif')
                ->descriptionIcon($isAktif ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($isAktif ? 'success' : 'warning'),

            Stat::make('Total Keperluan Dana', $format($paguDiajukan))
                ->description($descDiajukan)
                ->descriptionIcon($iconDiajukan)
                ->color($colorDiajukan),

            Stat::make('Sisa Anggaran', $format($sisaAnggaran))
                ->description($descSisa)
                ->descriptionIcon($iconSisa)
                ->color($colorSisa),
        ];
    }
}