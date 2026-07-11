<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PaguAnggaranOverview;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\StandarPaguOverview;
use App\Services\DashboardReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            PaguAnggaranOverview::class,
            StandarPaguOverview::class,
            RecentActivityWidget::class,
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
            'md' => 1,
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
