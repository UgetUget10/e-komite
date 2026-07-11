<?php

namespace App\Filament\Resources\PaguTahunAnggarans\Pages;

use App\Filament\Resources\PaguTahunAnggarans\PaguTahunAnggaranResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPaguTahunAnggaran extends EditRecord
{
    protected static string $resource = PaguTahunAnggaranResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-pagu-tahun-anggaran'];
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
