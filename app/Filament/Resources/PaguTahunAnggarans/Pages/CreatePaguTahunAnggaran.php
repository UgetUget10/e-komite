<?php

namespace App\Filament\Resources\PaguTahunAnggarans\Pages;

use App\Filament\Resources\PaguTahunAnggarans\PaguTahunAnggaranResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaguTahunAnggaran extends CreateRecord
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
}
