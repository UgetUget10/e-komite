<?php

namespace App\Filament\Resources\Standars\Pages;

use App\Filament\Resources\Standars\StandarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStandars extends ListRecords
{
    protected static string $resource = StandarResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-standar'];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
