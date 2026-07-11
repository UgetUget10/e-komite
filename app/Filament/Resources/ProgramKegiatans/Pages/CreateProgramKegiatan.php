<?php

namespace App\Filament\Resources\ProgramKegiatans\Pages;

use App\Filament\Resources\ProgramKegiatans\ProgramKegiatanResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;

class CreateProgramKegiatan extends CreateRecord
{
    protected static string $resource = ProgramKegiatanResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-program-kegiatan'];
    }

    /**
     * Nonaktifkan tombol "Create & create another"
     */
    protected function hasCreateAnotherAction(): bool
    {
        return false;
    }

    /**
     * Custom create action dengan konfirmasi modal dan ringkasan total anggaran
     */
    protected function getCreateFormAction(): Action
    {
        return Action::make('create')
            ->label('Buat')
            ->submit('create')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Pembuatan Program')
            ->modalDescription('Apakah Anda yakin ingin membuat program ini?')
            ->modalSubmitActionLabel('Ya, Buat')
            ->modalCancelActionLabel('Batal')
            ->modalContent(fn () => $this->renderConfirmationModalContent())
            ->action(fn () => $this->create());
    }

    /**
     * Render konten modal konfirmasi berisi total anggaran
     */
    private function renderConfirmationModalContent(): \Illuminate\Contracts\View\View
    {
        $totalAnggaran = $this->calculateTotalAnggaran();

        return view('filament.modals.confirm-create-program', [
            'total' => $this->formatRupiah($totalAnggaran),
        ]);
    }

    /**
     * Hitung total anggaran dari field repeater uraianProgram
     */
    private function calculateTotalAnggaran(): int
    {
        $uraianProgram = $this->form->getState()['uraianProgram'] ?? [];

        return Collection::make($uraianProgram)
            ->sum('jumlah');
    }

    /**
     * Format angka ke format rupiah Indonesia
     */
    private function formatRupiah(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}