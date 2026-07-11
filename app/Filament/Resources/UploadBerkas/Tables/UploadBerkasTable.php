<?php

namespace App\Filament\Resources\UploadBerkas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class UploadBerkasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('judul')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->icon('heroicon-o-document-text')
                    ->iconColor('warning')
                    ->description(fn ($record) => $record->keterangan ? \Illuminate\Support\Str::limit($record->keterangan, 60) : null),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar-days')
                    ->iconColor('gray'),

                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->state(fn ($record) => str(\strtolower($record->berkas ?? ''))->endsWith('.pdf') ? 'PDF' : 'Word')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'PDF' ? 'danger' : 'info')
                    ->icon(fn (string $state): string => $state === 'PDF' ? 'heroicon-o-document' : 'heroicon-o-document-text'),

                TextColumn::make('berkas')
                    ->label('Berkas')
                    ->formatStateUsing(fn () => 'Lihat Berkas')
                    ->url(fn ($record) => $record->berkas ? route('upload-berkas.preview', $record) : null)
                    ->openUrlInNewTab()
                    ->badge()
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('warning'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Filter::make('tanggal')
                    ->columnSpan(2)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('tanggal_dari')
                                    ->label('Tanggal Dari')
                                    ->native(false),
                                DatePicker::make('tanggal_sampai')
                                    ->label('Tanggal Sampai')
                                    ->native(false),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_dari'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['tanggal_sampai'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['tanggal_dari'] ?? null) {
                            $indicators[] = 'Dari '.Carbon::parse($data['tanggal_dari'])->format('d M Y');
                        }

                        if ($data['tanggal_sampai'] ?? null) {
                            $indicators[] = 'Sampai '.Carbon::parse($data['tanggal_sampai'])->format('d M Y');
                        }

                        return $indicators;
                    }),

                SelectFilter::make('jenis_berkas')
                    ->label('Jenis Berkas')
                    ->options([
                        'pdf' => 'PDF',
                        'word' => 'Word (DOC/DOCX)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'pdf' => $query->where('berkas', 'like', '%.pdf'),
                            'word' => $query->where(fn (Builder $q) => $q
                                ->where('berkas', 'like', '%.doc')
                                ->orWhere('berkas', 'like', '%.docx')),
                            default => $query,
                        };
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->deferFilters(false)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
