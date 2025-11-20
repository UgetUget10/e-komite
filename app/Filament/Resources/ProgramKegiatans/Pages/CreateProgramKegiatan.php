<?php

namespace App\Filament\Resources\ProgramKegiatans\Pages;

use App\Filament\Resources\ProgramKegiatans\ProgramKegiatanResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateProgramKegiatan extends CreateRecord
{
    protected static string $resource = ProgramKegiatanResource::class;

    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label('Buat')
            ->submit('create')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Pembuatan Program')
            ->modalDescription(fn ($get) =>
                'Apakah Anda yakin ingin membuat program ini?'
            )
            ->modalSubmitActionLabel('Ya, Buat')
            ->modalCancelActionLabel('Batal')
            ->modalContent(function ($get) {
                $total = collect($get('uraianProgram') ?? [])->sum('jumlah') ?? 0;
                $formatted = 'Rp ' . number_format($total, 0, ',', '.');

                return view('filament.modals.confirm-create-program', [
                    'total' => $formatted,
                ]);
            })
            ->action(function () {
                $this->create();
            });
    }
    /**
     * HILANGKAN TOMBOL "Buat & buat lainnya"
     */
    protected function hasCreateAnotherAction(): bool
    {
        return false;
    }
}