<?php

namespace App\Filament\Resources\UraianPrograms\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UraianProgramInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Program Terkait')
                    ->icon('heroicon-o-link')
                    ->schema([
                        TextEntry::make('standars.nama_standar')
                            ->label('Standar')
                            ->icon('heroicon-o-building-library'),

                        TextEntry::make('programKegiatan.judul_program')
                            ->label('Program Kegiatan')
                            ->icon('heroicon-o-clipboard-document-list'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Detail Uraian')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        TextEntry::make('nama_uraian')
                            ->label('Nama Uraian')
                            ->icon('heroicon-o-tag')
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make('banyaknya')
                            ->label('Banyaknya')
                            ->icon('heroicon-o-hashtag')
                            ->numeric(),

                        TextEntry::make('satuan')
                            ->label('Harga Satuan')
                            ->icon('heroicon-o-currency-dollar')
                            ->placeholder('-')
                            ->formatStateUsing(fn ($state) => $state ? 'Rp ' . number_format($state, 0, ',', '.') : '-'),

                        TextEntry::make('jumlah')
                            ->label('Jumlah')
                            ->icon('heroicon-o-banknotes')
                            ->weight('bold')
                            ->color('success')
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),
                    ])
                    ->columns(3)
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
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
