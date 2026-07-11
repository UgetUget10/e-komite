<?php

namespace App\Filament\Resources\ProgramKegiatans\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramKegiatanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Program')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('kode_lengkap')
                            ->label('Kode Program')
                            ->icon('heroicon-o-hashtag')
                            ->badge()
                            ->color('primary')
                            ->extraEntryWrapperAttributes(['class' => 'info-stat-box']),

                        TextEntry::make('standars.nama_standar')
                            ->label('Standar')
                            ->icon('heroicon-o-building-library')
                            ->formatStateUsing(function ($state, $record) {
                                $label = $state;
                                if ($record->standars?->kode) {
                                    $label .= ' (' . $record->standars->kode . ')';
                                }
                                return $label;
                            })
                            ->extraEntryWrapperAttributes(['class' => 'info-stat-box']),

                        TextEntry::make('judul_program')
                            ->label('Judul Program')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->weight('bold')
                            ->size('lg')
                            ->extraEntryWrapperAttributes(['class' => 'info-stat-box']),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Ringkasan Anggaran')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        TextEntry::make('total')
                            ->numeric()
                            ->label('Total Kebutuhan')
                            ->icon('heroicon-o-calculator')
                            ->weight('bold')
                            ->size('lg')
                            ->color('success')
                            ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                            ->extraEntryWrapperAttributes(['class' => 'anggaran-stat-box anggaran-stat-success']),

                        TextEntry::make('pagu_dipa')
                            ->numeric()
                            ->label('Pagu DIPA')
                            ->icon('heroicon-o-building-office')
                            ->weight('bold')
                            ->size('lg')
                            ->color('info')
                            ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                            ->extraEntryWrapperAttributes(['class' => 'anggaran-stat-box anggaran-stat-info']),

                        TextEntry::make('pagu_bos')
                            ->numeric()
                            ->label('Pagu BOS')
                            ->icon('heroicon-o-building-library')
                            ->weight('bold')
                            ->size('lg')
                            ->color('gray')
                            ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                            ->extraEntryWrapperAttributes(['class' => 'anggaran-stat-box anggaran-stat-gray']),

                        TextEntry::make('pagu_komite')
                            ->numeric()
                            ->label('Pagu Komite')
                            ->icon('heroicon-o-user-group')
                            ->weight('bold')
                            ->size('lg')
                            ->color('warning')
                            ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                            ->extraEntryWrapperAttributes(['class' => 'anggaran-stat-box anggaran-stat-warning']),

                        TextEntry::make('pagu_swadaya')
                            ->numeric()
                            ->label('Pagu Swadaya')
                            ->icon('heroicon-o-hand-raised')
                            ->weight('bold')
                            ->size('lg')
                            ->color('danger')
                            ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                            ->extraEntryWrapperAttributes(['class' => 'anggaran-stat-box anggaran-stat-danger']),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make('Daftar Uraian Kegiatan')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        RepeatableEntry::make('uraianProgram')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('nama_uraian')
                                    ->label('Nama Uraian')
                                    ->icon('heroicon-o-tag')
                                    ->weight('semibold'),

                                TextEntry::make('banyaknya')
                                    ->label('Banyaknya')
                                    ->icon('heroicon-o-hashtag'),

                                TextEntry::make('satuan')
                                    ->label('Harga Satuan')
                                    ->icon('heroicon-o-currency-dollar')
                                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),

                                TextEntry::make('jumlah')
                                    ->label('Jumlah')
                                    ->icon('heroicon-o-banknotes')
                                    ->weight('bold')
                                    ->color('success')
                                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
