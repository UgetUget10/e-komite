<?php

namespace App\Filament\Resources\RealisasiBulans;

use App\Filament\Resources\RealisasiBulans\Pages\CreateRealisasiBulan;
use App\Filament\Resources\RealisasiBulans\Pages\EditRealisasiBulan;
use App\Filament\Resources\RealisasiBulans\Pages\ListRealisasiBulans;
use App\Filament\Resources\RealisasiBulans\Pages\ViewRealisasiBulan;
use App\Filament\Resources\RealisasiBulans\Schemas\RealisasiBulanForm;
use App\Filament\Resources\RealisasiBulans\Schemas\RealisasiBulanInfolist;
use App\Filament\Resources\RealisasiBulans\Tables\RealisasiBulansTable;
use App\Models\RealisasiBulan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RealisasiBulanResource extends Resource
{
    protected static ?string $model = RealisasiBulan::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Master Realisasi Bulan';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'master-realisasi-bulan'; // ini slug URL






    public static function form(Schema $schema): Schema
    {
        return RealisasiBulanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RealisasiBulanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RealisasiBulansTable::configure($table);
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
            'index' => ListRealisasiBulans::route('/'),
            'create' => CreateRealisasiBulan::route('/create'),
            'view' => ViewRealisasiBulan::route('/{record}'),
            'edit' => EditRealisasiBulan::route('/{record}/edit'),
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
