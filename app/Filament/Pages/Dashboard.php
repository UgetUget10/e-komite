<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PaguAnggaranChartWidget;
use App\Filament\Widgets\PaguAnggaranOverview;
use App\Filament\Widgets\PaguTrendChartWidget;
use App\Filament\Widgets\RealisasiAnggaranChartWidget;
use App\Filament\Widgets\RealisasiLineChart;
use App\Filament\Widgets\StandarComparisonChartWidget;
use App\Filament\Widgets\StandarFilter;
use App\Filament\Widgets\StandarPaguOverview;
use App\Filament\Widgets\TrendAnggaranChartWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{

    public function getWidgets(): array
    {
        return [
            PaguAnggaranOverview::class,
            StandarPaguOverview::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 3,
            '2xl' => 3,
        ];
    }
}