<?php

namespace App\Filament\Resources\UploadBerkas\Pages;

use App\Filament\Resources\UploadBerkas\UploadBerkasResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUploadBerkas extends EditRecord
{
    protected static string $resource = UploadBerkasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    public function getPageClasses(): array
    {
        return ['fi-page-upload-berkas'];
    }
}
