<?php

namespace App\Filament\Resources\PaguTahunAnggarans\Pages;

use App\Filament\Resources\PaguTahunAnggarans\PaguTahunAnggaranResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPaguTahunAnggaran extends ViewRecord
{
    protected static string $resource = PaguTahunAnggaranResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-pagu-tahun-anggaran'];
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
