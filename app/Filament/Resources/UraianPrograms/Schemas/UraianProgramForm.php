<?php

namespace App\Filament\Resources\UraianPrograms\Schemas;

use App\Models\ProgramKegiatan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class UraianProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Program Terkait')
                    ->icon('heroicon-o-link')
                    ->schema([
                        Select::make('id_program_kegiatan')
                            ->label('Program Kegiatan')
                            ->prefixIcon('heroicon-o-clipboard-document-list')
                            ->options(function () {
                                $user = auth()->user();

                                $query = ProgramKegiatan::query();
                                if (!$user->hasRole('Super Admin')) {
                                    $standarIds = $user->standars()->pluck('id');
                                    $query->whereIn('id_standar', $standarIds);
                                }

                                return $query->get()->mapWithKeys(function ($program) {
                                    $label = ($program->kode_lengkap ?? $program->kode) . ' - ' . $program->judul_program;
                                    return [$program->id => $label];
                                });
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('standar_display')
                            ->label('Standar (Otomatis)')
                            ->prefixIcon('heroicon-o-building-library')
                            ->readOnly()
                            ->dehydrated(false)
                            ->formatStateUsing(function (callable $get, $record) {
                                $programId = $get('id_program_kegiatan') ?? $record?->id_program_kegiatan;
                                if (!$programId) {
                                    return '';
                                }

                                $program = ProgramKegiatan::with('standars')->find($programId);
                                return $program?->standars?->nama_standar ?? '';
                            })
                            ->live()
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Detail Uraian')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        TextInput::make('nama_uraian')
                            ->label('Nama Uraian')
                            ->prefixIcon('heroicon-o-tag')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('banyaknya')
                            ->label('Banyaknya')
                            ->prefixIcon('heroicon-o-hashtag')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $satuan = (float) preg_replace('/\D/', '', $get('satuan') ?? '0');
                                $set('jumlah_display', 'Rp ' . number_format($state * $satuan, 0, ',', '.'));
                            }),

                        TextInput::make('satuan')
                            ->label('Harga Satuan')
                            ->prefix('Rp')
                            ->required()
                            ->default(0)
                            ->mask(RawJs::make('function($input) { return new Intl.NumberFormat("id-ID").format($input); }'))
                            ->stripCharacters(['.', 'Rp', ','])
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $banyaknya = (float) ($get('banyaknya') ?? 0);
                                $set('jumlah_display', 'Rp ' . number_format($banyaknya * $state, 0, ',', '.'));
                            }),

                        TextInput::make('jumlah_display')
                            ->label('Jumlah (Otomatis)')
                            ->prefixIcon('heroicon-o-banknotes')
                            ->readOnly()
                            ->dehydrated(false)
                            ->formatStateUsing(function (callable $get, $record) {
                                $banyaknya = (float) ($get('banyaknya') ?? $record?->banyaknya ?? 0);
                                $satuan = (float) ($get('satuan') ?? $record?->satuan ?? 0);
                                return 'Rp ' . number_format($banyaknya * $satuan, 0, ',', '.');
                            })
                            ->extraAttributes(['class' => 'font-bold text-success-600']),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
