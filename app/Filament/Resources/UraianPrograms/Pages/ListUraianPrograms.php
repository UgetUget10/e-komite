<?php

namespace App\Filament\Resources\UraianPrograms\Pages;

use App\Filament\Resources\UraianPrograms\UraianProgramResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUraianPrograms extends ListRecords
{
    protected static string $resource = UraianProgramResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-uraian-program'];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
