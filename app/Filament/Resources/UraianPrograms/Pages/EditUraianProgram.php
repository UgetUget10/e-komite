<?php

namespace App\Filament\Resources\UraianPrograms\Pages;

use App\Filament\Resources\UraianPrograms\UraianProgramResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUraianProgram extends EditRecord
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

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
