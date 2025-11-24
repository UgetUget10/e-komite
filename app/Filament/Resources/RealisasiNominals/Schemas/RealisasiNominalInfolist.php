<?php

namespace App\Filament\Resources\RealisasiNominals\Schemas;

use App\Models\RealisasiNominal;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RealisasiNominalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('fk_id_standar'),
                TextEntry::make('fk_id_program_kegiatan'),
                TextEntry::make('fk_id_pagu_tahun_anggaran'),
                TextEntry::make('fk_id_realisasi_bulan'),
                TextEntry::make('nominal')
                    ->numeric(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (RealisasiNominal $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
