<?php

namespace App\Filament\Resources\PaguTahunAnggarans\Schemas;

use App\Models\PaguTahunAnggaran;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaguTahunAnggaranInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Pagu Tahun Anggaran')
                    ->icon('heroicon-o-calendar-date-range')
                    ->schema([
                        TextEntry::make('tahun')
                            ->label('Tahun Anggaran')
                            ->icon('heroicon-o-calendar')
                            ->badge()
                            ->size('lg')
                            ->color('primary'),

                        IconEntry::make('status_aktif')
                            ->label('Status')
                            ->icon(fn ($state) => $state ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                            ->color(fn ($state) => $state ? 'success' : 'danger'),

                        TextEntry::make('nominal_dana')
                            ->label('Total Nominal Dana')
                            ->numeric()
                            ->icon('heroicon-o-banknotes')
                            ->weight('bold')
                            ->size('lg')
                            ->color('success')
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                            ->columnSpanFull()
                            ->extraEntryWrapperAttributes(['class' => 'ptha-stat-box']),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Informasi Lainnya')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime()
                            ->icon('heroicon-o-plus-circle')
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime()
                            ->icon('heroicon-o-pencil-square')
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->label('Dihapus Pada')
                            ->dateTime()
                            ->icon('heroicon-o-trash')
                            ->color('danger')
                            ->visible(fn (PaguTahunAnggaran $record): bool => $record->trashed()),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
