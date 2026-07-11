<?php

namespace App\Filament\Resources\SignatureSettingResource\Pages;

use App\Filament\Resources\SignatureSettingResource;
use App\Models\SignatureSetting;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSignatureSettings extends ListRecords
{
    protected static string $resource = SignatureSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pengaturan Tanda Tangan')
                ->visible(fn () => SignatureSetting::count() === 0),
        ];
    }
}
