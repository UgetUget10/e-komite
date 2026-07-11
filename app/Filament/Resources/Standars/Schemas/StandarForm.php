<?php

namespace App\Filament\Resources\Standars\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class StandarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_standar')
                    ->required(),

                TextInput::make('kode')
                    ->label('Kode Standar')
                    ->placeholder('Isi kode standar')
                    ->nullable(),

                Select::make('users')
                    ->label('User yang punya standar ini')
                    ->multiple()
                    ->relationship('users', 'name'),
            ]);
    }
}
