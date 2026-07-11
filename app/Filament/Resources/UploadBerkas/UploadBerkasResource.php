<?php

namespace App\Filament\Resources\UploadBerkas;

use App\Filament\Resources\UploadBerkas\Pages\CreateUploadBerkas;
use App\Filament\Resources\UploadBerkas\Pages\EditUploadBerkas;
use App\Filament\Resources\UploadBerkas\Pages\ListUploadBerkas;
use App\Filament\Resources\UploadBerkas\Pages\ViewUploadBerkas;
use App\Filament\Resources\UploadBerkas\Schemas\UploadBerkasForm;
use App\Filament\Resources\UploadBerkas\Schemas\UploadBerkasInfolist;
use App\Filament\Resources\UploadBerkas\Tables\UploadBerkasTable;
use App\Models\UploadBerkas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class UploadBerkasResource extends Resource
{
    protected static ?string $model = UploadBerkas::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static string|UnitEnum|null $navigationGroup = 'Dokumen';

    protected static ?string $navigationLabel = 'Upload Berkas';

    protected static ?string $slug = 'upload-berkas';

    public static function form(Schema $schema): Schema
    {
        return UploadBerkasForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UploadBerkasInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UploadBerkasTable::configure($table);
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
            'index' => ListUploadBerkas::route('/'),
            'create' => CreateUploadBerkas::route('/create'),
            'view' => ViewUploadBerkas::route('/{record}'),
            'edit' => EditUploadBerkas::route('/{record}/edit'),
        ];
    }
}
