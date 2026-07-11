<?php

namespace App\Filament\Resources\UraianPrograms\Pages;

use App\Filament\Resources\UraianPrograms\UraianProgramResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUraianProgram extends ViewRecord
{
    protected static string $resource = UraianProgramResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-uraian-program'];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
