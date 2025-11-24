<?php

namespace App\Filament\Resources\RealisasiNominals\Pages;

use App\Filament\Resources\RealisasiNominals\RealisasiNominalResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRealisasiNominal extends ViewRecord
{
    protected static string $resource = RealisasiNominalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
