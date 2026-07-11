<?php

namespace App\Filament\Resources\Standars\Pages;

use App\Filament\Resources\Standars\StandarResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStandar extends ViewRecord
{
    protected static string $resource = StandarResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-standar'];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
