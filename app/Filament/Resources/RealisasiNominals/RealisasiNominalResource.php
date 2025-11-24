<?php

namespace App\Filament\Resources\RealisasiNominals;

use App\Filament\Resources\RealisasiNominals\Pages\CreateRealisasiNominal;
use App\Filament\Resources\RealisasiNominals\Pages\EditRealisasiNominal;
use App\Filament\Resources\RealisasiNominals\Pages\ListRealisasiNominals;
use App\Filament\Resources\RealisasiNominals\Pages\ViewRealisasiNominal;
use App\Filament\Resources\RealisasiNominals\Schemas\RealisasiNominalForm;
use App\Filament\Resources\RealisasiNominals\Schemas\RealisasiNominalInfolist;
use App\Filament\Resources\RealisasiNominals\Tables\RealisasiNominalsTable;
use App\Models\RealisasiNominal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RealisasiNominalResource extends Resource
{
    protected static ?string $model = RealisasiNominal::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-pencil-square';


    protected static string|UnitEnum|null $navigationGroup = 'Pendapatan & Anggaran';

    protected static ?string $navigationLabel = 'Realisasi Program';
    protected static ?int $navigationSort = 5;
    protected static ?string $slug = 'realisasi-program'; // ini slug URL

    public static function form(Schema $schema): Schema
    {
        return RealisasiNominalForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RealisasiNominalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RealisasiNominalsTable::configure($table);
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
            'index' => ListRealisasiNominals::route('/'),
            'create' => CreateRealisasiNominal::route('/create'),
            'view' => ViewRealisasiNominal::route('/{record}'),
            'edit' => EditRealisasiNominal::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
