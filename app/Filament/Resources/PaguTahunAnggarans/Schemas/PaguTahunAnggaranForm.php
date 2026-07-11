<?php

namespace App\Filament\Resources\PaguTahunAnggarans\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class PaguTahunAnggaranForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Pagu Tahun Anggaran')
                    ->description('Isi tahun anggaran dan total nominal dana yang tersedia')
                    ->icon('heroicon-o-calendar-date-range')
                    ->schema([
                        TextInput::make('tahun')
                            ->label('Tahun Anggaran')
                            ->placeholder('Misal: 2026')
                            ->prefixIcon('heroicon-o-calendar')
                            ->required()
                            ->numeric()
                            ->columnSpan(1),

                        Toggle::make('status_aktif')
                            ->label('Jadikan Tahun Aktif')
                            ->helperText('Hanya 1 tahun anggaran yang bisa aktif dalam satu waktu')
                            ->required()
                            ->inline(false)
                            ->columnSpan(1),

                        TextInput::make('nominal_dana')
                            ->label('Total Nominal Dana')
                            ->prefix('Rp ')
                            ->required()
                            ->default(0)
                            ->live(debounce: 500)
                            ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 0, ',', '.') : '')
                            ->dehydrateStateUsing(fn ($state) => (int) preg_replace('/\D/', '', $state ?? '0'))
                            ->extraInputAttributes([
                                'onkeyup' => "let v = this.value.replace(/\\D/g,''); this.value = v ? new Intl.NumberFormat('id-ID').format(v) : ''"
                            ])
                            ->extraAttributes(['class' => 'text-right font-medium'])
                            ->columnSpanFull(),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }
}
