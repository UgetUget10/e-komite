<?php

namespace App\Filament\Resources\RealisasiNominals\Pages;

use App\Filament\Resources\RealisasiNominals\RealisasiNominalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRealisasiNominal extends CreateRecord
{
    protected static string $resource = RealisasiNominalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['fk_id_pagu_tahun_anggaran'] =
            \App\Models\PaguTahunAnggaran::where('status_aktif', 1)->value('id');

        return $data;
    }
}
