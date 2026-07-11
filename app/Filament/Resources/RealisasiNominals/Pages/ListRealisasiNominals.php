<?php

namespace App\Filament\Resources\RealisasiNominals\Pages;

use App\Filament\Resources\RealisasiNominals\RealisasiNominalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRealisasiNominals extends ListRecords
{
    protected static string $resource = RealisasiNominalResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-realisasi-nominal'];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
