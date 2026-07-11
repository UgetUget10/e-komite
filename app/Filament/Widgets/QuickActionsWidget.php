<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected string $view = 'filament.widgets.quick-actions';

    protected static ?int $sort = 9;
    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        return [
            'createRealisasiUrl' => route('filament.kelola.resources.realisasi-program.create'),
            'realisasiIndexUrl' => route('filament.kelola.resources.realisasi-program.index'),
            'programIndexUrl' => route('filament.kelola.resources.program-kegiatan.index'),
            'paguIndexUrl' => route('filament.kelola.resources.pagu-tahun-anggarans.index'),
        ];
    }
}
