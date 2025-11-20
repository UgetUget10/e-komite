<?php

namespace App\Filament\Resources\ProgramKegiatans\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;

class ProgramKegiatansTable
{
    public static function configure(Table $table): Table
    {

        return $table

            ->columns([
                // TextColumn::make('id')
                //     ->label('ID')
                //     ->searchable(),
                TextColumn::make('standar.nama_standar')
                    ->searchable(),
                TextColumn::make('judul_program')
                    ->label('Judul Program')
                    ->searchable(),
                TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                // TextColumn::make('pagu_dipa')
                //     ->numeric()
                //     ->sortable(),
                // TextColumn::make('pagu_komite')
                //     ->numeric()
                //     ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Kolom Pagu Komite — readonly & otomatis sama dengan total
            // TextColumn::make('pagu_komite')
            //     ->label('Pagu Komite')
            //     ->numeric(thousandsSeparator: '.')
            //     ->sortable()
            //     ->color('success')
            //     ->extraAttributes(['class' => 'font-mono']),
            ])
            // ->filters([
            //     SelectFilter::make('id_standar')
            //         ->label('Filter Standar')
            //         ->options(function () {
            //             $user = auth()->user();
            //             return $user->standars()->pluck('nama_standar', 'id');
            //         })
            //     // ->disablePlaceholder(), // <- ini menghilangkan option "All"
            // ], layout: FiltersLayout::BeforeContent) // or `FiltersLayout::AfterContent`
            ->filters([
                SelectFilter::make('id_standar')
                    ->label('Filter Standar')
                    ->options(function () {
                        $user = auth()->user();
                        $options = [];

                        // // Tambahkan opsi "Semua" hanya untuk super admin
                        // if ($user->hasRole('Super Admin')) {
                        //     $options[''] = 'Semua'; // Kunci kosong untuk "All"
                        // }

                        // Ambil standar sesuai role
                        if ($user->hasRole('Super Admin')) {
                            $standars = \App\Models\Standar::pluck('nama_standar', 'id');
                        } else {
                            $standars = $user->standars()->pluck('nama_standar', 'id');
                        }

                        return $options + $standars->toArray();
                    })
                    ->placeholder('Pilih Standar')
                    ->query(function ($query, $state) {
                        if ($state !== null && $state !== '') {
                            $query->where('id_standar', $state);
                        }
                        // Jika $state kosong atau null → tidak filter (tampilkan semua)
                    })
                    ->default('') // Default ke "Semua" jika super admin
            ], layout: FiltersLayout::BeforeContent)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Program Kegiatan')
                    ->modalDescription('Program dan semua uraian di dalamnya akan dihapus permanen.')
                    ->action(function ($record) {
                        // PAKSA delete via model → trigger event deleting
                        $record->delete();
                    })
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Program berhasil dihapus')
                            ->body('Semua uraian terkait juga telah dihapus.')
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
