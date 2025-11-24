<?php

namespace App\Filament\Resources\UraianPrograms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UraianProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('id')
                //     ->label('ID')
                //     ->searchable(),
                TextColumn::make('standars.nama_standar')
                    ->label('Standar')
                    ->searchable(),
                TextColumn::make('programKegiatan.judul_program')
                    ->label('Program Kegiatan')
                    ->searchable(),
                TextColumn::make('nama_uraian')
                    ->label('Nama Uraian')
                    ->searchable(),
                TextColumn::make('banyaknya')
                    ->label('Banyaknya')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('satuan')
                    ->label('Satuan')
                    ->searchable(),
                TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
