<?php

namespace App\Filament\Resources\RealisasiNominals\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class RealisasiNominalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('fk_id_pagu_tahun_anggaran')
                    ->label('Tahun Anggaran (Aktif)')
                    ->options(function () {
                        return \App\Models\PaguTahunAnggaran::where('status_aktif', 1)
                            ->pluck('tahun', 'id');
                    })
                    ->default(function () {
                        return \App\Models\PaguTahunAnggaran::where('status_aktif', 1)->value('id');
                    })
                    ->required()
                    ->disabled()
                    ->dehydrated(false) // jangan kirim ini (karena Hidden yang mengirim)
                    ->reactive(),


                Select::make('fk_id_standar')
                    ->label('Standar')
                    ->relationship('standar', 'nama_standar')
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->required()
                    // ❗ reset fk_id_program_kegiatan saat standar berubah
                    ->afterStateUpdated(function (callable $set) {
                        $set('fk_id_program_kegiatan', null);
                    }),

                Select::make('fk_id_program_kegiatan')
                    ->label('Program Kegiatan')
                    ->options(function (callable $get) {
                        $standarId = $get('fk_id_standar');
                        $paguId    = $get('fk_id_pagu_tahun_anggaran');

                        if (!$standarId || !$paguId) {
                            return [];
                        }

                        return \App\Models\ProgramKegiatan::where('id_standar', $standarId)
                            ->where('fk_id_pagu_tahun_anggaran', $paguId)
                            ->pluck('judul_program', 'id');
                    })
                    ->reactive()
                    ->required()
                    ->searchable()
                    ->preload(),


                Select::make('fk_id_realisasi_bulan')
                    ->label('Di realisasikan pada bulan')
                    ->relationship(
                        'bulan',
                        'nama_bulan',
                        modifyQueryUsing: fn($query) => $query->orderBy('created_at', 'asc')
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('nominal')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
