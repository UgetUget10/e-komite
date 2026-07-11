<?php

namespace App\Filament\Resources\Standars\Pages;

use App\Filament\Resources\Standars\StandarResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStandar extends EditRecord
{
    protected static string $resource = StandarResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-standar'];
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
