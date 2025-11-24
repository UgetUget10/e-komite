<?php

namespace App\Filament\Resources\RealisasiBulans\Pages;

use App\Filament\Resources\RealisasiBulans\RealisasiBulanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRealisasiBulan extends ViewRecord
{
    protected static string $resource = RealisasiBulanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
