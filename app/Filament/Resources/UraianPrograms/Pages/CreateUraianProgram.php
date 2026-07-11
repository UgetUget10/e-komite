<?php

namespace App\Filament\Resources\UraianPrograms\Pages;

use App\Filament\Resources\UraianPrograms\UraianProgramResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUraianProgram extends CreateRecord
{
    protected static string $resource = UraianProgramResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-uraian-program'];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
