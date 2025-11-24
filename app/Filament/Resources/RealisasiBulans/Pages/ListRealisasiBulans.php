<?php

namespace App\Filament\Resources\RealisasiBulans\Pages;

use App\Filament\Resources\RealisasiBulans\RealisasiBulanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRealisasiBulans extends ListRecords
{
    protected static string $resource = RealisasiBulanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
