<?php

namespace App\Filament\Resources\UploadBerkas\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class UploadBerkasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Berkas')
                    ->description('Lengkapi judul, tanggal, dan keterangan singkat untuk berkas ini.')
                    ->icon('heroicon-o-information-circle')
                    ->iconColor('warning')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('judul')
                                    ->label('Judul')
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-pencil-square'),

                                DatePicker::make('tanggal')
                                    ->label('Tanggal')
                                    ->required()
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-calendar-days'),
                            ]),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Berkas')
                    ->description('Format PDF atau Word, maksimal 10MB.')
                    ->icon('heroicon-o-document-arrow-up')
                    ->iconColor('warning')
                    ->schema([
                        FileUpload::make('berkas')
                            ->label('Berkas')
                            ->required()
                            ->disk('public')
                            ->directory('upload-berkas')
                            ->visibility('public')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ])
                            ->downloadable()
                            ->openable()
                            ->maxSize(10240)
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, Get $get): string {
                                $tanggal = $get('tanggal');
                                $tanggalFormatted = $tanggal
                                    ? Carbon::parse($tanggal)->format('d-m-Y')
                                    : now()->format('d-m-Y');

                                $judulSlug = Str::slug((string) $get('judul')) ?: 'berkas';
                                $extension = $file->getClientOriginalExtension();

                                $baseName = "{$tanggalFormatted}_{$judulSlug}";
                                $filename = "{$baseName}.{$extension}";

                                $disk = Storage::disk('public');
                                $counter = 2;

                                while ($disk->exists('upload-berkas/'.$filename)) {
                                    $filename = "{$baseName}-{$counter}.{$extension}";
                                    $counter++;
                                }

                                return $filename;
                            })
                            ->deleteUploadedFileUsing(function (string $file): void {
                                Storage::disk('public')->delete($file);
                            })
                            ->columnSpanFull(),

                        View::make('filament.upload-berkas.berkas-preview')
                            ->viewData(function (Get $get, ?Model $record) {
                                $path = $get('berkas');
                                $hasExisting = $record && is_string($path) && filled($path);

                                return [
                                    'existingUrl' => $hasExisting ? route('upload-berkas.preview', $record) : null,
                                    'existingIsPdf' => $hasExisting && str(strtolower($path))->endsWith('.pdf'),
                                ];
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
