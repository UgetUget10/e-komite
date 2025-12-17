<?php

namespace App\Filament\Resources\UraianPrograms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total')   // Ini yang diubah
                            ->money('IDR')
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                            ->extraAttributes(['class' => 'font-bold text-primary-600'])
                    ),
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
                // Filter berdasarkan Standar (relationship)
                SelectFilter::make('standars_id')
                    ->label('Standar')
                    ->relationship('standars', 'nama_standar')
                    ->searchable()
                    ->preload()
                    ->multiple(), // Tambahan di v4: support multiple select lebih smooth

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
