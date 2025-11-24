<?php

namespace App\Filament\Resources\RealisasiBulans\Schemas;

use App\Models\RealisasiBulan;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RealisasiBulanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('nama_bulan'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (RealisasiBulan $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
