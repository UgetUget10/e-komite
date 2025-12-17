<?php

namespace App\Filament\Resources\RealisasiNominals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RealisasiNominalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('paguTahunAnggaran.tahun')
                    ->label('Tahun Anggaran')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('standar.nama_standar')
                    ->label('Standar')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('programKegiatan.judul_program')
                    ->label('Program Kegiatan')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('bulan.nama_bulan')
                    ->label('Bulan Realisasi')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Realisasi')   // Ini yang diubah
                            ->money('IDR')
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                            ->extraAttributes(['class' => 'font-bold text-primary-600'])
                    ),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                // Filter Tahun Anggaran
                SelectFilter::make('fk_id_pagu_tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->relationship('paguTahunAnggaran', 'tahun')
                    ->searchable()
                    ->preload(),

                // Filter Standar
                SelectFilter::make('fk_id_standar')
                    ->label('Standar')
                    ->relationship('standar', 'nama_standar')
                    ->searchable()
                    ->preload(),

                // Filter Program Kegiatan
                SelectFilter::make('fk_id_program_kegiatan')
                    ->label('Program Kegiatan')
                    ->relationship('programKegiatan', 'judul_program')
                    ->searchable()
                    ->preload(),

                // Filter Bulan
                SelectFilter::make('fk_id_bulan')
                    ->label('Bulan Realisasi')
                    ->relationship('bulan', 'nama_bulan')
                    ->searchable()
                    ->preload(),

                // Filter Soft Delete (sudah ada)
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withTrashed())
            ->emptyStateHeading('Belum ada realisasi nominal')
            ->striped()
            ->defaultSort('created_at', 'desc');
    }
}
