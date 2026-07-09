<?php

namespace App\Filament\Resources\UraianPrograms;

use App\Filament\Resources\UraianPrograms\Pages\CreateUraianProgram;
use App\Filament\Resources\UraianPrograms\Pages\EditUraianProgram;
use App\Filament\Resources\UraianPrograms\Pages\ListUraianPrograms;
use App\Filament\Resources\UraianPrograms\Pages\ViewUraianProgram;
use App\Filament\Resources\UraianPrograms\Schemas\UraianProgramForm;
use App\Filament\Resources\UraianPrograms\Schemas\UraianProgramInfolist;
use App\Filament\Resources\UraianPrograms\Tables\UraianProgramsTable;
use App\Models\UraianProgram;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UraianProgramResource extends Resource
{
    protected static ?string $model = UraianProgram::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Pendapatan & Anggaran';

    protected static ?string $navigationLabel = 'Uraian Program';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return UraianProgramForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UraianProgramInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UraianProgramsTable::configure($table);
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUraianPrograms::route('/'),
            'create' => CreateUraianProgram::route('/create'),
            'view' => ViewUraianProgram::route('/{record}'),
            'edit' => EditUraianProgram::route('/{record}/edit'),
        ];
    }
}
