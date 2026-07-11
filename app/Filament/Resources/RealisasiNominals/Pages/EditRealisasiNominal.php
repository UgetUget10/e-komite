<?php

namespace App\Filament\Resources\RealisasiNominals\Pages;

use App\Filament\Resources\RealisasiNominals\RealisasiNominalResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRealisasiNominal extends EditRecord
{
    protected static string $resource = RealisasiNominalResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-realisasi-nominal'];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['fk_id_pagu_tahun_anggaran'] =
            \App\Models\PaguTahunAnggaran::where('status_aktif', 1)->value('id');

        $data['fk_id_realisasi_bulan'] =
            \App\Models\RealisasiBulan::fromDate($data['tanggal_realisasi'])->id;

        return $data;
    }


    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
