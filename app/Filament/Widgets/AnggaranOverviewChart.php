<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AnggaranOverviewChart extends ChartWidget
{
    protected ?string $heading = 'Ringkasan Anggaran Tahun 2025';
    // protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        // Ambil data anggaran 2025 (aktif)
        $anggaran = DB::table('tb_pagu_tahun_anggaran')
            ->where('tahun', 2025)
            ->where('status_aktif', 1)
            ->first();

        $pendapatan = $anggaran->nominal_dana ?? 0;
        $kebutuhan = 8268500000;
        $sisa = $pendapatan - $kebutuhan;

        return [
            'datasets' => [
                [
                    'label' => 'Total Pendapatan (Pagu Dana',
                    'data' => [$pendapatan],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgba(34, 197, 94)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Total Kebutuhan Dana',
                    'data' => [$kebutuhan],
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',    // merah
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Sisa / Defisit Anggaran',
                    'data' => [$sisa],
                    'type' => 'line',                                 // jadi garis
                    'borderColor' => $sisa < 0 ? 'rgb(239, 68, 68)' : 'rgb(34, 197, 94)',
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 4,
                    'pointRadius' => 8,
                    'pointBackgroundColor' => $sisa < 0 ? 'rgb(239, 68, 68)' : 'rgb(34, 197, 94)',
                ],
            ],
            'labels' => ['Anggaran Tahun 2025'],
            'options' => [
                'plugins' => [
                    'title' => [
                        'display' => false,
                    ],
                    'legend' => [
                        'position' => 'bottom',
                    ],
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => false,
                        'ticks' => [
                            'callback' => function ($value) {
                                return 'Rp ' . number_format($value, 0, ',', '.');
                            },
                        ],
                    ],
                ],
                'tooltips' => [
                    'callbacks' => [
                        'label' => function ($tooltipItem) {
                        return $tooltipItem.dataset.label . ': Rp ' . number_format($tooltipItem.raw, 0, ',', '.');
                        },
                    ],
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // biar horizontal bar (lebih bagus untuk 1 data)
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
