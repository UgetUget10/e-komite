<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopesToUserStandar;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnggaranOverviewChart extends ChartWidget
{
    use ScopesToUserStandar;

    protected ?string $heading = 'Tren Realisasi Bulanan';
    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $tahunAktif = $this->activeTahunAnggaran();
        if (!$tahunAktif) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $isPrivileged = $this->isPrivilegedUser();
        $allowedStandarIds = $this->allowedStandarIds();

        // Query total pagu komite untuk scope ini
        $paguQuery = DB::table('komite.tb_program_kegiatan')
            ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id);

        if (!$isPrivileged && !empty($allowedStandarIds)) {
            $paguQuery->whereIn('id_standar', $allowedStandarIds);
        }

        $totalPagu = $paguQuery->sum('pagu_komite') ?? 0;

        // Query realisasi per bulan (Jan-Des)
        $realisasiPerBulan = DB::table('komite.tb_realisasi_nominal')
            ->where('fk_id_pagu_tahun_anggaran', $tahunAktif->id)
            ->whereNull('deleted_at')
            ->whereYear('tanggal_realisasi', $tahunAktif->tahun)
            ->selectRaw('MONTH(tanggal_realisasi) as bulan, SUM(nominal) as total')
            ->when(!$isPrivileged && !empty($allowedStandarIds), fn ($q) => $q->whereIn('fk_id_standar', $allowedStandarIds))
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        // Build monthly data (Jan-Dec)
        $monthlyData = collect(range(1, 12))->map(fn ($m) => (float) ($realisasiPerBulan[$m] ?? 0))->toArray();

        // Build cumulative data untuk line
        $cumulativeData = [];
        $sum = 0;
        foreach ($monthlyData as $value) {
            $sum += $value;
            $cumulativeData[] = $totalPagu > 0 ? round(($sum / $totalPagu) * 100, 1) : 0;
        }

        // Month labels
        $labels = collect(range(1, 12))
            ->map(fn ($m) => Carbon::create()->month($m)->translatedFormat('M'))
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Realisasi Bulanan (Rp)',
                    'data' => $monthlyData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.6)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 1,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Execution Rate Kumulatif (%)',
                    'data' => $cumulativeData,
                    'type' => 'line',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 3,
                    'pointRadius' => 5,
                    'pointBackgroundColor' => 'rgba(34, 197, 94, 1)',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'interaction' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
                'plugins' => [
                    'legend' => [
                        'position' => 'top',
                    ],
                ],
                'scales' => [
                    'y' => [
                        'type' => 'linear',
                        'display' => true,
                        'position' => 'left',
                        'title' => [
                            'display' => true,
                            'text' => 'Nominal (Rp)',
                        ],
                    ],
                    'y1' => [
                        'type' => 'linear',
                        'display' => true,
                        'position' => 'right',
                        'title' => [
                            'display' => true,
                            'text' => 'Execution Rate (%)',
                        ],
                        'min' => 0,
                        'max' => 100,
                        'grid' => [
                            'drawOnChartArea' => false,
                        ],
                    ],
                ],
            ],
        ];
    }
}
