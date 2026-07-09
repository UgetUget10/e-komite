<?php

namespace App\Filament\Resources\ProgramKegiatans\Tables;

use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ProgramKegiatansTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Ambil ID tahun anggaran yang aktif
                $tahunAktifId = \App\Models\PaguTahunAnggaran::where('status_aktif', 1)
                    ->value('id');

                // Jika tidak ada tahun aktif (misal data belum di-set), tampilkan semua (atau kosong)
                if ($tahunAktifId) {
                    $query->where('fk_id_pagu_tahun_anggaran', $tahunAktifId);
                } else {
                    // Opsional: jika tidak ada tahun aktif, sembunyikan semua data
                    $query->whereRaw('1 = 0'); // tidak ada data yang ditampilkan
                }
            })


            ->columns([
                // TextColumn::make('id')
                //     ->label('ID')
                //     ->searchable(),
                TextColumn::make('standars.nama_standar')
                    ->label('Standar')
                    ->searchable(),
                TextColumn::make('judul_program')
                    ->label('Judul Program')
                    ->searchable(),
                TextColumn::make('total')
                    ->label('Total Diajukan')
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
                    ->default('')
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

            ->headerActions([
                Action::make('tahun_aktif')
                    ->label(function () {
                        $tahun = \App\Models\PaguTahunAnggaran::where('status_aktif', 1)->value('tahun');
                        return "Tahun Anggaran Aktif: " . ($tahun ?? 'Tidak ada');
                    })
                    ->color('secondary'),
                Action::make('total_diajukan')
                    ->label(function () {
                        $total = \App\Models\ProgramKegiatan::where('fk_id_pagu_tahun_anggaran',
                            \App\Models\PaguTahunAnggaran::where('status_aktif', 1)->value('id')
                        )->sum('total');
                        return "Total Diajukan: Rp " . number_format($total, 0, ',', '.');
                    })
                    ->color('success')
                    ->disabled(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);

    }
}
