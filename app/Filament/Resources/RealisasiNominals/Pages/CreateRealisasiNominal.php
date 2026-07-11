<?php

namespace App\Filament\Resources\RealisasiNominals\Pages;

use App\Filament\Resources\RealisasiNominals\RealisasiNominalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRealisasiNominal extends CreateRecord
{
    protected static string $resource = RealisasiNominalResource::class;

    public function getPageClasses(): array
    {
        return ['fi-page-realisasi-nominal'];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['fk_id_pagu_tahun_anggaran'] =
            \App\Models\PaguTahunAnggaran::where('status_aktif', 1)->value('id');

        $data['fk_id_realisasi_bulan'] =
            \App\Models\RealisasiBulan::fromDate($data['tanggal_realisasi'])->id;

        return $data;
    }
}
