<?php

namespace App\Filament\Resources\ProgramKegiatans\Pages;

use App\Filament\Resources\ProgramKegiatans\ProgramKegiatanResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProgramKegiatan extends ViewRecord
{
    protected static string $resource = ProgramKegiatanResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-program-kegiatan'];
    }

    protected function getHeaderActions(): array
    {
        return [
            // Tombol Kembali
            Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->url($this->getResource()::getUrl('index')) // Kembali ke halaman List
                ->color('success'),

            // Tombol Ekspor PDF
            Action::make('ekspor_pdf')
                ->label('Ekspor PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    $record = $this->record->load('standar', 'uraianProgram');

                    $pdf = Pdf::loadView('pdf.program-kegiatan-detail', [
                        'record' => $record,
                    ])->setPaper('a4', 'portrait');

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'program-kegiatan-' . ($record->kode_lengkap ?? $record->id) . '.pdf',
                    );
                }),

            // Tombol Edit
            EditAction::make(),
        ];
    }
}
