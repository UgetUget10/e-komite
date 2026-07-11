<?php

namespace App\Filament\Resources\RealisasiNominals\Schemas;

use App\Models\RealisasiNominal;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class RealisasiNominalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Program')
                    ->icon('heroicon-o-link')
                    ->schema([
                        TextEntry::make('paguTahunAnggaran.tahun')
                            ->label('Tahun Anggaran')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('standar.nama_standar')
                            ->label('Standar')
                            ->icon('heroicon-o-building-library'),

                        TextEntry::make('programKegiatan.kode_lengkap')
                            ->label('Kode Program')
                            ->icon('heroicon-o-hashtag')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('programKegiatan.judul_program')
                            ->label('Program Kegiatan')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Detail Realisasi')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        TextEntry::make('tanggal_realisasi')
                            ->label('Tanggal Realisasi')
                            ->icon('heroicon-o-calendar-days')
                            ->date('d F Y'),

                        TextEntry::make('nominal')
                            ->label('Nominal Realisasi')
                            ->icon('heroicon-o-banknotes')
                            ->weight('bold')
                            ->size('lg')
                            ->color('success')
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),

                        TextEntry::make('keterangan')
                            ->label('Keterangan Realisasi')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Dokumen Pendukung')
                    ->icon('heroicon-o-document-arrow-up')
                    ->schema([
                        TextEntry::make('google_drive_url')
                            ->label('Google Drive')
                            ->visible(fn (RealisasiNominal $record): bool => filled($record->google_drive_url))
                            ->formatStateUsing(fn () => 'Lihat di Google Drive')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->color('success')
                            ->url(fn (RealisasiNominal $record) => $record->google_drive_url)
                            ->openUrlInNewTab(),

                        ViewEntry::make('dokumen')
                            ->label('Dokumen PDF')
                            ->hiddenLabel()
                            ->visible(fn (RealisasiNominal $record): bool => filled($record->dokumen))
                            ->view('filament.upload-berkas.berkas-preview')
                            ->viewData(function (RealisasiNominal $record) {
                                $path = $record->dokumen;
                                $hasFile = filled($path);

                                return [
                                    'existingUrl' => $hasFile ? Storage::disk('public')->url($path) : null,
                                    'existingIsPdf' => $hasFile,
                                    'showFallbackLink' => true,
                                ];
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->visible(fn (RealisasiNominal $record): bool => filled($record->dokumen) || filled($record->google_drive_url)),

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
                            ->visible(fn (RealisasiNominal $record): bool => $record->trashed()),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
