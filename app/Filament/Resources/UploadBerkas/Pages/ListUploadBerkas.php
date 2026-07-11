<?php

namespace App\Filament\Resources\UploadBerkas\Pages;

use App\Filament\Resources\UploadBerkas\UploadBerkasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUploadBerkas extends ListRecords
{
    protected static string $resource = UploadBerkasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getPageClasses(): array
    {
        return ['fi-page-upload-berkas'];
    }
}
