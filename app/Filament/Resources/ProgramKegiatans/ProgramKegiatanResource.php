<?php

namespace App\Filament\Resources\ProgramKegiatans;

use App\Filament\Resources\ProgramKegiatans\Pages\CreateProgramKegiatan;
use App\Filament\Resources\ProgramKegiatans\Pages\EditProgramKegiatan;
use App\Filament\Resources\ProgramKegiatans\Pages\ListProgramKegiatans;
use App\Filament\Resources\ProgramKegiatans\Pages\ViewProgramKegiatan;
use App\Filament\Resources\ProgramKegiatans\Schemas\ProgramKegiatanForm;
use App\Filament\Resources\ProgramKegiatans\Schemas\ProgramKegiatanInfolist;
use App\Filament\Resources\ProgramKegiatans\Tables\ProgramKegiatansTable;
use App\Models\ProgramKegiatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProgramKegiatanResource extends Resource
{
    protected static ?string $model = ProgramKegiatan::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-pencil-square';


    protected static string|UnitEnum|null $navigationGroup = 'Pendapatan & Anggaran';

    protected static ?string $navigationLabel = 'Rencana Program Kegiatan';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug = 'program-kegiatan'; // ini slug URL

    public static function form(Schema $schema): Schema
    {
        return ProgramKegiatanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProgramKegiatanInfolist::configure($schema);
    }

    public static function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();
        $query = parent::getTableQuery();

        // Super Admin bisa lihat semua data tanpa filter
        if ($user->hasRole('Super Admin')) {
            return $query;
        }

        // Role lain: filter ke standar yang ditangani user
        $standarIds = $user->standars()->pluck('id');
        return $query->whereIn('id_standar', $standarIds);
    }


    public static function table(Table $table): Table
    {
        return ProgramKegiatansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProgramKegiatans::route('/'),
            'create' => CreateProgramKegiatan::route('/create'),
            'view' => ViewProgramKegiatan::route('/{record}'),
            'edit' => EditProgramKegiatan::route('/{record}/edit'),
        ];
    }
}
