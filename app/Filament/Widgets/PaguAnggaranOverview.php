<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaguAnggaranOverview extends BaseWidget
{

    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    // custom dashboard view
    // protected string $view = 'filament.kelola.widgets.pagu-anggaran-overview';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('Super Admin'); // Sesuaikan jika perlu

        // 1. Ambil pagu aktif atau terbaru
        $pagu = DB::table('komite.tb_pagu_tahun_anggaran')
            ->where('status_aktif', 1)
            ->first();

        if (!$pagu) {
            $pagu = DB::table('komite.tb_pagu_tahun_anggaran')
                ->orderBy('tahun', 'desc')
                ->first();
        }

        if (!$pagu) {
            return [
                Stat::make('Total Pagu Anggaran', 'Tidak ada data')
                    ->description('Belum ada pagu anggaran')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
                Stat::make('Pagu Diajukan', 'Rp. 0'),
                Stat::make('Sisa Pagu', 'Rp. 0'),
            ];
        }

        $tahun = $pagu->tahun;
        $totalPagu = $pagu->nominal_dana ?? 0;
        $isAktif = $pagu->status_aktif == 1;

        // === TOTAL DIAJUKAN GLOBAL (UNTUK SISA PAGU) ===
        $totalDiajukanGlobal = DB::table('komite.tb_program_kegiatan')
            ->where('fk_id_pagu_tahun_anggaran', $pagu->id)
            ->sum('total');

        $totalDiajukanGlobal = $totalDiajukanGlobal ?? 0;
        $sisaPagu = $totalPagu - $totalDiajukanGlobal;

        // === PAGU DIAJUKAN USER (ROLE-BASED) ===
        $query = DB::table('komite.tb_program_kegiatan as pk')
            ->where('pk.fk_id_pagu_tahun_anggaran', $pagu->id);

        if (!$isSuperAdmin) {
            // Ambil program kegiatan yang memiliki uraian dengan standar user
            $allowedProgramIds = DB::table('komite.tb_uraian_program as up')
                ->join('komite.tb_standar_user as su', 'up.id_standar', '=', 'su.standar_id')
                ->where('su.user_id', $user->id)
                ->distinct()
                ->pluck('up.id_program_kegiatan')
                ->toArray();

            if (empty($allowedProgramIds)) {
                $paguDiajukan = 0;
                $desc = 'Tidak punya akses standar';
                $icon = 'heroicon-m-lock-closed';
                $color = 'gray';
            } else {
                $paguDiajukan = $query->whereIn('pk.id', $allowedProgramIds)->sum('pk.total');
                $paguDiajukan = $paguDiajukan ?? 0;
                $desc = 'Standar Anda';
                $icon = 'heroicon-m-arrow-up-tray';
                $color = 'primary';
            }
        } else {
            $paguDiajukan = $totalDiajukanGlobal;
            $desc = 'Semua standar';
            $icon = 'heroicon-m-shield-check';
            $color = 'primary';
        }

        return [
            // 1. Total Pagu
            Stat::make("Total Pendapatan {$tahun}", 'Rp. ' . number_format($totalPagu, 0, ',', '.'))
                ->description($isAktif ? 'Aktif' : 'Tidak Aktif')
                ->descriptionIcon($isAktif ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($isAktif ? 'success' : 'warning')
                ->extraAttributes(['class' => 'text-2xl font-bold']),

            // 2. Pagu Diajukan (User)
            Stat::make('Total Keperluan Dana', 'Rp. ' . number_format($paguDiajukan, 0, ',', '.'))
                ->description($desc)
                ->descriptionIcon($icon)
                ->color($color),

            // 3. Sisa Pagu (Global)
            Stat::make('Sisa Anggaran', 'Rp. ' . number_format($sisaPagu, 0, ',', '.'))
                ->description($sisaPagu < 0 ? 'Melebihi Anggaran Pendapatan!' : ($sisaPagu == 0 ? 'Habis' : 'Tersedia'))
                ->descriptionIcon(
                    $sisaPagu < 0
                        ? 'heroicon-m-exclamation-triangle'
                        : ($sisaPagu == 0 ? 'heroicon-m-minus-circle' : 'heroicon-m-banknotes')
                )
                ->color(
                    $sisaPagu < 0
                        ? 'danger'
                        : ($sisaPagu == 0 ? 'warning' : 'success')
                ),
        ];
    }
}