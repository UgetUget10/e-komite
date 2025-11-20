<?php

namespace App\Filament\Resources\ProgramKegiatans\Pages;

use App\Filament\Resources\ProgramKegiatans\ProgramKegiatanResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProgramKegiatan extends ViewRecord
{
    protected static string $resource = ProgramKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol Kembali
            Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->url($this->getResource()::getUrl('index')) // Kembali ke halaman List
                ->color('success'),

            // Tombol Edit
            EditAction::make(),
        ];
    }
}
