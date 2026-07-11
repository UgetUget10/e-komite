<?php

namespace App\Filament\Resources\UploadBerkas\Pages;

use App\Filament\Resources\UploadBerkas\UploadBerkasResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUploadBerkas extends ViewRecord
{
    protected static string $resource = UploadBerkasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function getPageClasses(): array
    {
        return ['fi-page-upload-berkas'];
    }
}
