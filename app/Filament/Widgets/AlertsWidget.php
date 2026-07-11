<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopesToUserStandar;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AlertsWidget extends BaseWidget
{
    use ScopesToUserStandar;

    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 2;
    }

    protected function getHeading(): ?string
    {
        return 'Peringatan & Status Kritis';
    }

    protected function getStats(): array
    {
        $isPrivileged = $this->isPrivilegedUser();
        $allowedStandarIds = $this->allowedStandarIds();
        $tahunAktif = $this->activeTahunAnggaran();

        if (!$tahunAktif) {
            return [
                Stat::make('Program Over-Budget', '0')
                    ->description('Tidak ada data')
                    ->color('gray'),
                Stat::make('Realisasi Tanpa Dokumen', '0')
                    ->description('Tidak ada data')
                    ->color('gray'),
            ];
        }

        // Hitung program yang over-budget
        $overBudgetQuery = DB::table('komite.tb_program_kegiatan')
            ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id)
            ->whereColumn('total_pengeluaran_perprogram', '>', 'pagu_komite');

        if (!$isPrivileged && !empty($allowedStandarIds)) {
            $overBudgetQuery->whereIn('id_standar', $allowedStandarIds);
        }

        $overBudgetCount = $overBudgetQuery->count();

        // Hitung realisasi tanpa dokumen
        $noDokumenQuery = DB::table('komite.tb_realisasi_nominal')
            ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id)
            ->where('nominal', '>', 0)
            ->whereNull('dokumen')
            ->whereNull('google_drive_url')
            ->whereNull('deleted_at');

        if (!$isPrivileged && !empty($allowedStandarIds)) {
            $noDokumenQuery->whereIn('fk_id_standar', $allowedStandarIds);
        }

        $noDokumenCount = $noDokumenQuery->count();

        return [
            Stat::make('Program Over-Budget', (string) $overBudgetCount)
                ->description($overBudgetCount > 0 ? 'Perhatian!' : 'Semua sesuai anggaran')
                ->descriptionIcon($overBudgetCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->icon('heroicon-o-exclamation-circle')
                ->color($overBudgetCount > 0 ? 'danger' : 'success'),

            Stat::make('Realisasi Tanpa Dokumen', (string) $noDokumenCount)
                ->description($noDokumenCount > 0 ? 'Segera lengkapi' : 'Semua lengkap')
                ->descriptionIcon($noDokumenCount > 0 ? 'heroicon-m-document-minus' : 'heroicon-m-check-circle')
                ->icon('heroicon-o-document-text')
                ->color($noDokumenCount > 0 ? 'warning' : 'success'),
        ];
    }
}
