<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopesToUserStandar;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaguAnggaranOverview extends BaseWidget
{
    use ScopesToUserStandar;

    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getHeading(): ?string
    {
        return 'Ringkasan Anggaran';
    }

    protected function getDescription(): ?string
    {
        return 'Kondisi pendapatan dan keperluan dana tahun anggaran berjalan';
    }

    protected function getStats(): array
    {
        $isPrivileged = $this->isPrivilegedUser();
        $allowedStandarIds = $this->allowedStandarIds();

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
                Stat::make('Total Keperluan Dana', 'Rp 0'),
                Stat::make('Execution Rate', '-'),
                Stat::make('Sisa Anggaran', 'Rp 0'),
            ];
        }

        $tahun     = $pagu->tahun;
        $totalPagu = $pagu->nominal_dana ?? 0;
        $isAktif   = $pagu->status_aktif == 1;

        // Format rupiah
        $format = fn($value) => 'Rp ' . number_format($value, 0, ',', '.');

        // Base query program kegiatan untuk pagu aktif
        $baseQuery = DB::table('komite.tb_program_kegiatan')
            ->where('fk_id_pagu_tahun_anggaran', $pagu->id);

        if ($isPrivileged) {
            // SUPER ADMIN / PELAKSANA KOMITE / KEPALA MADRASAH
            $paguDiajukan = $baseQuery->sum(DB::raw('pagu_dipa + pagu_komite')) ?? 0;
            $descDiajukan = 'Semua Standar';
            $iconDiajukan = 'heroicon-m-shield-check';
            $colorDiajukan = 'success';
        } else {
            // USER RESTRICTED → Hanya data Standar yang di-assign
            if (empty($allowedStandarIds)) {
                $paguDiajukan = 0;
                $descDiajukan = 'Tidak ada akses standar';
                $iconDiajukan = 'heroicon-m-lock-closed';
                $colorDiajukan = 'gray';
            } else {
                $filteredQuery = $baseQuery->whereIn('id_standar', $allowedStandarIds);
                $paguDiajukan = $filteredQuery->sum('pagu_komite') ?? 0;
                $descDiajukan = 'Standar Anda';
                $iconDiajukan = 'heroicon-m-arrow-up-tray';
                $colorDiajukan = 'primary';
            }
        }

        // Sisa Anggaran
        $sisaAnggaran = $totalPagu - $paguDiajukan;
        $descSisa = $sisaAnggaran < 0 ? 'Melebihi Anggaran!' : ($sisaAnggaran == 0 ? 'Habis' : 'Tersedia');
        $iconSisa = $sisaAnggaran < 0 ? 'heroicon-m-exclamation-triangle' : ($sisaAnggaran == 0 ? 'heroicon-m-minus-circle' : 'heroicon-m-banknotes');
        $colorSisa = $sisaAnggaran < 0 ? 'danger' : ($sisaAnggaran == 0 ? 'warning' : 'success');

        $colorPendapatan = $isAktif ? 'success' : 'warning';

        return [
            Stat::make("Total Pendapatan {$tahun}", $format($totalPagu))
                ->description($isAktif ? 'Aktif' : 'Tidak Aktif')
                ->descriptionIcon($isAktif ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->icon('heroicon-o-banknotes')
                ->color($colorPendapatan)
                ->extraAttributes(['style' => "--stat-accent: var(--color-{$colorPendapatan}-500);"]),

            Stat::make('Total Keperluan Dana', $format($paguDiajukan))
                ->description($descDiajukan)
                ->descriptionIcon($iconDiajukan)
                ->icon('heroicon-o-calculator')
                ->color($colorDiajukan)
                ->url(route('filament.kelola.pages.reka-pagu-standar'))
                ->extraAttributes(['style' => "--stat-accent: var(--color-{$colorDiajukan}-500);"])
                ->extraAttributes(['class' => 'cursor-pointer hover:scale-105 transition-transform']),

            Stat::make('Sisa Anggaran', $format($sisaAnggaran))
                ->description($descSisa)
                ->descriptionIcon($iconSisa)
                ->icon('heroicon-o-scale')
                ->color($colorSisa)
                ->extraAttributes(['style' => "--stat-accent: var(--color-{$colorSisa}-500);"]),
        ];
    }
}