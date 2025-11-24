<?php

namespace App\Filament\Resources\PaguTahunAnggarans\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class PaguTahunAnggaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Pagu Tahun Anggaran')
                    ->schema([
                TextInput::make('tahun')
                    ->required(),
                TextInput::make('nominal_dana')
                    ->required()
                    ->numeric(),
                Toggle::make('status_aktif')
                    ->label('Status Aktif')
                    ->required()
                    ->inline(false), // tampil seperti switch
            ])->columnSpanFull(),
        ]);
    }
}
