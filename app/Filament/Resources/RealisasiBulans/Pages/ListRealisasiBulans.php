<?php

namespace App\Filament\Resources\RealisasiBulans\Pages;

use App\Filament\Resources\RealisasiBulans\RealisasiBulanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRealisasiBulans extends ListRecords
{
    protected static string $resource = RealisasiBulanResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-realisasi-bulan'];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
