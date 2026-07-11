<?php

namespace App\Filament\Resources\ProgramKegiatans\Tables;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
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
                TextColumn::make('kode_lengkap')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('standars.nama_standar')
                    ->label('Standar')
                    ->searchable(),
                TextColumn::make('judul_program')
                    ->label('Judul Program')
                    ->searchable()
                    ->wrap()
                    ->extraAttributes(['class' => 'max-w-xs']),
                TextColumn::make('pagu_dipa')
                    ->label('Pagu DIPA')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),
                TextColumn::make('pagu_bos')
                    ->label('Pagu BOS')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),
                TextColumn::make('pagu_swadaya')
                    ->label('Pagu Swadaya')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),
                TextColumn::make('pagu_komite')
                    ->label('Total Diajukan')
                    ->numeric()
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Diajukan')
                            ->money('IDR')
                            ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                            ->extraAttributes(['class' => 'font-bold text-primary-600'])
                    ),
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
            ])
            ->filters([
                SelectFilter::make('id_standar')
                    ->label('Filter Standar')
                    ->options(function () {
                        $user = auth()->user();

                        // Ambil standar sesuai role
                        if ($user->hasRole('Super Admin')) {
                            $standars = \App\Models\Standar::all();
                        } else {
                            $standars = $user->standars()->get();
                        }

                        return $standars->mapWithKeys(function ($standar) {
                            $label = $standar->nama_standar;
                            if ($standar->kode) {
                                $label .= ' (' . $standar->kode . ')';
                            }
                            return [$standar->id => $label];
                        });
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
                Action::make('ekspor_pdf')
                    ->label('Ekspor PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->action(function ($record) {
                        $record->load('standars', 'uraianProgram');

                        $pdf = Pdf::loadView('pdf.program-kegiatan-detail', [
                            'record' => $record,
                        ])->setPaper('a4', 'portrait');

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'program-kegiatan-' . ($record->kode_lengkap ?? $record->id) . '.pdf',
                        );
                    }),
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

                Action::make('ekspor_pdf_semua')
                    ->label('Ekspor PDF (Semua)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function ($livewire) {
                        $records = $livewire->getFilteredTableQuery()
                            ->with(['standars', 'uraianProgram'])
                            ->get();

                        $tahun = \App\Models\PaguTahunAnggaran::where('status_aktif', 1)->value('tahun');

                        $pdf = Pdf::loadView('pdf.program-kegiatan-detail-list', [
                            'records' => $records,
                            'periodeLabel' => 'Tahun Anggaran ' . ($tahun ?? '-'),
                        ])->setPaper('a4', 'portrait');

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'program-kegiatan-detail-' . now()->format('Y-m-d-His') . '.pdf',
                        );
                    }),

                Action::make('cetak_rkam')
                    ->label('Cetak RKAM')
                    ->icon('heroicon-o-table-cells')
                    ->color('warning')
                    ->action(function ($livewire) {
                        $records = $livewire->getFilteredTableQuery()
                            ->with('standars')
                            ->get();

                        $groups = $records
                            ->groupBy('id_standar')
                            ->map(function ($programsInStandar) {
                                $standar = $programsInStandar->first()->standars;

                                $rows = $programsInStandar
                                    ->sortBy(fn ($p) => $p->kode)
                                    ->values()
                                    ->map(fn ($program) => [
                                        'judul_program' => $program->judul_program,
                                        'pagu_dipa' => (float) ($program->pagu_dipa ?? 0),
                                        'pagu_komite' => (float) ($program->pagu_komite ?? 0),
                                    ]);

                                return [
                                    'standar' => $standar,
                                    'rows' => $rows,
                                    'subtotal_dipa' => $rows->sum('pagu_dipa'),
                                    'subtotal_komite' => $rows->sum('pagu_komite'),
                                ];
                            })
                            ->sortBy(fn ($group) => $group['standar']?->kode)
                            ->values();

                        $tahun = \App\Models\PaguTahunAnggaran::where('status_aktif', 1)->value('tahun');

                        $pdf = Pdf::loadView('pdf.rkam-pagu', [
                            'groups' => $groups,
                            'periodeLabel' => 'Tahun Anggaran ' . ($tahun ?? '-'),
                        ])->setPaper('a4', 'portrait');

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'rkam-' . now()->format('Y-m-d-His') . '.pdf',
                        );
                    }),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada program kegiatan')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->striped();
    }
}
