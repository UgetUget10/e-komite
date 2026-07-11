<?php

namespace App\Filament\Resources\PaguTahunAnggarans\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class PaguTahunAnggaransTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tahun')
                    ->label('Tahun Anggaran')
                    ->icon('heroicon-o-calendar')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('nominal_dana')
                    ->label('Total Nominal Dana')
                    ->numeric()
                    ->sortable()
                    ->weight('semibold')
                    ->color('success')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),

                ToggleColumn::make('status_aktif')
                    ->label('Status Aktif')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Dihapus Pada')
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
            ])
            ->emptyStateHeading('Belum ada pagu tahun anggaran')
            ->emptyStateIcon('heroicon-o-calendar-date-range')
            ->striped()
            ->defaultSort('tahun', 'desc');
    }
}
