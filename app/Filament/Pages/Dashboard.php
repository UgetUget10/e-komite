<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BudgetAlertWidget;
use App\Filament\Widgets\BudgetVsRealisasiChart;
use App\Filament\Widgets\PaguAnggaranOverview;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\StandarPaguOverview;
use App\Filament\Widgets\TahunAnggaranSwitcherWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            TahunAnggaranSwitcherWidget::class,
            PaguAnggaranOverview::class,
            BudgetVsRealisasiChart::class,
            BudgetAlertWidget::class,
            RecentActivityWidget::class,
            StandarPaguOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'sm' => 1,
            'md' => 2,
            'lg' => 12,
            'xl' => 12,
            '2xl' => 12,
        ];
    }

    public function getPageClasses(): array
    {
        return ['fi-page-dashboard'];
    }
}
