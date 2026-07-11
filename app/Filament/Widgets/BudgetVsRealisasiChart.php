<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopesToUserStandar;
use App\Models\ProgramKegiatan;
use App\Models\RealisasiNominal;
use Filament\Widgets\ChartWidget;

class BudgetVsRealisasiChart extends ChartWidget
{
    use ScopesToUserStandar;

    protected ?string $heading = 'Budget vs Realisasi - Trend Bulanan';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $isPrivileged = $this->isPrivilegedUser();
        $allowedStandarIds = $this->allowedStandarIds();
        $tahunAktif = $this->activeTahunAnggaran();

        if (!$tahunAktif) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        // Get monthly realisasi data
        $realisasiData = RealisasiNominal::query()
            ->selectRaw('MONTH(tanggal_realisasi) as bulan, SUM(nominal) as total', [])
            ->whereYear('tanggal_realisasi', $tahunAktif->tahun)
            ->whereNull('deleted_at');

        // Apply role-based scoping
        if (!$isPrivileged && !empty($allowedStandarIds)) {
            $realisasiData->whereIn('fk_id_standar', $allowedStandarIds);
        }

        $realisasiData = $realisasiData->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        // Get monthly budget data
        $budgetData = ProgramKegiatan::query()
            ->selectRaw('MONTH(created_at) as bulan, SUM(pagu_komite) as total', [])
            ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id)
            ->whereNull('deleted_at');

        if (!$isPrivileged && !empty($allowedStandarIds)) {
            $budgetData->whereIn('id_standar', $allowedStandarIds);
        }

        $budgetData = $budgetData->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        // Build monthly data
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $budgetDataPoints = [];
        $realisasiDataPoints = [];

        for ($i = 1; $i <= 12; $i++) {
            $budgetDataPoints[] = $budgetData[$i] ?? 0;
            $realisasiDataPoints[] = $realisasiData[$i] ?? 0;
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Budget Komite (Rp)',
                    'data' => $budgetDataPoints,
                    'borderColor' => '#FF9800',
                    'backgroundColor' => 'rgba(255, 152, 0, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Realisasi (Rp)',
                    'data' => $realisasiDataPoints,
                    'borderColor' => '#4CAF50',
                    'backgroundColor' => 'rgba(76, 175, 80, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'borderWidth' => 2,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.dataset.label + ": Rp " + new Intl.NumberFormat("id-ID").format(context.parsed.y); }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "Rp " + new Intl.NumberFormat("id-ID", {notation: "compact"}).format(value); }',
                    ],
                ],
            ],
        ];
    }
}
