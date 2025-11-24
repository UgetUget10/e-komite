<?php

namespace App\Filament\Resources\RealisasiBulans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RealisasiBulanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_bulan')
                    ->required(),
            ]);
    }
}
