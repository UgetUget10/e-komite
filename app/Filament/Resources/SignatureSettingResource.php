<?php

namespace App\Filament\Resources;

use App\Models\SignatureSetting;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SignatureSettingResource extends Resource
{
    protected static ?string $model = SignatureSetting::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan Tanda Tangan';

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Nama Penanda Tangan')
                    ->description('Atur nama-nama yang akan tampil pada dokumen ekspor (PDF dan CSV)')
                    ->schema([
                        TextInput::make('ketua_komite_name')
                            ->label('Nama Ketua Komite')
                            ->required()
                            ->default('Prof. Dr. H. Nur Ali, M.Pd'),

                        TextInput::make('kepala_sekolah_name')
                            ->label('Nama Kepala Sekolah')
                            ->required()
                            ->default('Dra. Erni Qomaria Rida, M.Pd.'),
                    ]),

                Section::make('Tanggal Dokumen')
                    ->description('Atur tanggal, bulan dan tahun yang tampil pada dokumen')
                    ->schema([
                        TextInput::make('signature_day')
                            ->label('Hari/Tanggal')
                            ->numeric()
                            ->default(0)
                            ->helperText('Masukkan angka hari (1-31). Kosongkan (0) jika tidak ingin menampilkan hari'),

                        TextInput::make('signature_month')
                            ->label('Bulan')
                            ->default('Januari')
                            ->required(),

                        TextInput::make('signature_year')
                            ->label('Tahun')
                            ->numeric()
                            ->default(2025)
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ketua_komite_name')
                    ->label('Ketua Komite'),
                TextColumn::make('kepala_sekolah_name')
                    ->label('Kepala Sekolah'),
                TextColumn::make('signature_month')
                    ->label('Bulan'),
                TextColumn::make('signature_year')
                    ->label('Tahun'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\SignatureSettingResource\Pages\ListSignatureSettings::route('/'),
            'edit' => \App\Filament\Resources\SignatureSettingResource\Pages\EditSignatureSetting::route('/{record}/edit'),
        ];
    }
}
