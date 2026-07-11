<?php

namespace App\Filament\Resources\SignatureSettingResource\Pages;

use App\Filament\Resources\SignatureSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSignatureSetting extends EditRecord
{
    protected static string $resource = SignatureSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
