<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopesToUserStandar;
use App\Models\ProgramKegiatan;
use App\Models\RealisasiNominal;
use Filament\Widgets\ChartWidget;

class BudgetVsRealisasiChart extends ChartWidget
{
    use ScopesToUserStandar;

    protected ?string $heading = 'Budget vs Realisasi';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected ?string $maxHeight = '320px';

    public function getDescription(): ?string
    {
        return 'Perbandingan pagu komite dengan realisasi belanja per bulan pada tahun anggaran berjalan';
    }

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
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan')
            ->toArray();

        if (!$isPrivileged && !empty($allowedStandarIds)) {
            $budgetData = ProgramKegiatan::query()
                ->selectRaw('MONTH(created_at) as bulan, SUM(pagu_komite) as total', [])
                ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id)
                ->whereIn('id_standar', $allowedStandarIds)
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->pluck('total', 'bulan')
                ->toArray();
        }

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
                    'label' => 'Pagu Komite',
                    'data' => $budgetDataPoints,
                    'borderColor' => '#F59E0B',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.08)',
                    'pointBackgroundColor' => '#F59E0B',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'fill' => true,
                    'tension' => 0.4,
                    'borderWidth' => 3,
                ],
                [
                    'label' => 'Realisasi',
                    'data' => $realisasiDataPoints,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.08)',
                    'pointBackgroundColor' => '#10B981',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'fill' => true,
                    'tension' => 0.4,
                    'borderWidth' => 3,
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
                    'align' => 'end',
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'padding' => 20,
                        'font' => [
                            'size' => 12,
                            'weight' => '600',
                        ],
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(17, 24, 39, 0.95)',
                    'padding' => 12,
                    'cornerRadius' => 8,
                    'titleFont' => ['size' => 13, 'weight' => '600'],
                    'bodyFont' => ['size' => 12],
                    'callbacks' => [
                        'label' => 'function(context) { return context.dataset.label + ": Rp " + new Intl.NumberFormat("id-ID").format(context.parsed.y); }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.05)',
                    ],
                    'ticks' => [
                        'callback' => 'function(value) { return "Rp " + new Intl.NumberFormat("id-ID", {notation: "compact"}).format(value); }',
                        'font' => ['size' => 11],
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'font' => ['size' => 11],
                    ],
                ],
            ],
        ];
    }
}
