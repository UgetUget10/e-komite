<?php

namespace App\Filament\Resources\Standars\Pages;

use App\Filament\Resources\Standars\StandarResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStandar extends CreateRecord
{
    protected static string $resource = StandarResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-standar'];
    }
}
