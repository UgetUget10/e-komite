<?php

namespace App\Filament\Resources\UploadBerkas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UploadBerkasInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Berkas')
                    ->icon('heroicon-o-information-circle')
                    ->iconColor('warning')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('judul')
                                    ->label('Judul')
                                    ->icon('heroicon-o-pencil-square')
                                    ->weight('semibold'),

                                TextEntry::make('tanggal')
                                    ->label('Tanggal')
                                    ->date('d M Y')
                                    ->icon('heroicon-o-calendar-days')
                                    ->badge()
                                    ->color('gray'),
                            ]),

                        TextEntry::make('keterangan')
                            ->label('Keterangan')
                            ->placeholder('-')
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Dibuat')
                                    ->dateTime()
                                    ->icon('heroicon-o-clock')
                                    ->color('gray'),

                                TextEntry::make('updated_at')
                                    ->label('Diperbarui')
                                    ->dateTime()
                                    ->icon('heroicon-o-clock')
                                    ->color('gray'),
                            ]),
                    ]),

                Section::make('Berkas')
                    ->icon('heroicon-o-document-arrow-up')
                    ->iconColor('warning')
                    ->schema([
                        ViewEntry::make('berkas')
                            ->label(false)
                            ->view('filament.upload-berkas.berkas-preview')
                            ->viewData(function ($record) {
                                $path = $record->berkas;
                                $hasFile = filled($path);

                                return [
                                    'existingUrl' => $hasFile ? route('upload-berkas.preview', $record) : null,
                                    'existingIsPdf' => $hasFile && str(strtolower($path))->endsWith('.pdf'),
                                    'showFallbackLink' => true,
                                ];
                            }),
                    ]),
            ]);
    }
}
