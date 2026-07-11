<?php

namespace App\Filament\Resources\ProgramKegiatans\Pages;

use Filament\Actions\Action;
use Filament\Widgets\Widget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\ProgramKegiatans\ProgramKegiatanResource;

class ListProgramKegiatans extends ListRecords
{
    protected static string $resource = ProgramKegiatanResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-program-kegiatan'];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->createAnother(false),
        ];
    }
}
