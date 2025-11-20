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

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->createAnother(false),
        ];
    }
}
