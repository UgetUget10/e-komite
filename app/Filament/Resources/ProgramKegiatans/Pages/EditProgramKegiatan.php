<?php

namespace App\Filament\Resources\ProgramKegiatans\Pages;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProgramKegiatans\ProgramKegiatanResource;

class EditProgramKegiatan extends EditRecord
{
    protected static string $resource = ProgramKegiatanResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-program-kegiatan'];
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            // DeleteAction::make(),
            // 🚫 Ganti Delete bawaan dengan custom action
            // Action::make('safeDelete')
            //     ->label('Hapus Program Kegiatan Ini')
            //     ->color('danger')
            //     ->icon('heroicon-o-trash')
            //     ->requiresConfirmation()
            //     ->action(function () {
            //         if ($this->record->uraianProgram()->exists()) {
            //             Notification::make()
            //                 ->title('Tidak bisa dihapus')
            //                 ->body('Program ini masih memiliki uraian kegiatan. Hapus semua uraian terlebih dahulu.')
            //                 ->danger()
            //                 ->send();
            //             return;
            //         }

            //         $this->record->delete();

            //         Notification::make()
            //             ->title('Berhasil dihapus')
            //             ->success()
            //             ->send();

            //         return redirect($this->getResource()::getUrl('index'));
            //     }),
            DeleteAction::make()
                ->label('Hapus Program Kegiatan Ini')
                ->color('danger')
                ->icon('heroicon-o-trash'),
            // 🚫 Ganti Delete bawaan dengan custom action
            // Action::make('safeDelete')
            //     ->label('Hapus Program Kegiatan Ini')
            //     ->color('danger')
            //     ->icon('heroicon-o-trash')
            //     ->requiresConfirmation()
            //     ->action(function () {
            //         if ($this->record->uraianProgram()->exists()) {
            //             Notification::make()
            //                 ->title('Tidak bisa dihapus')
            //                 ->body('Program ini masih memiliki uraian kegiatan. Hapus semua uraian terlebih dahulu.')
            //                 ->danger()
            //                 ->send();
            //             return;
            //         }

            //         $this->record->delete();

            //         Notification::make()
            //             ->title('Berhasil dihapus')
            //             ->success()
            //             ->send();

            //         return redirect($this->getResource()::getUrl('index'));
            //     }),
        ];
    }

    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }
}
