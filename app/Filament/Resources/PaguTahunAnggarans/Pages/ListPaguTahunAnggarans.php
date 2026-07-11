<?php

namespace App\Filament\Resources\PaguTahunAnggarans\Pages;

use App\Filament\Resources\PaguTahunAnggarans\PaguTahunAnggaranResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaguTahunAnggarans extends ListRecords
{
    protected static string $resource = PaguTahunAnggaranResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-pagu-tahun-anggaran'];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
