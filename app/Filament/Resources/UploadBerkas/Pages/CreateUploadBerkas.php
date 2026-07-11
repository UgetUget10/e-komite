<?php

namespace App\Filament\Resources\UploadBerkas\Pages;

use App\Filament\Resources\UploadBerkas\UploadBerkasResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUploadBerkas extends CreateRecord
{
    protected static string $resource = UploadBerkasResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getPageClasses(): array
    {
        return ['fi-page-upload-berkas'];
    }
}
