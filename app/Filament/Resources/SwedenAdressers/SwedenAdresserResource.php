<?php

namespace App\Filament\Resources\SwedenAdressers;

use App\Filament\Resources\SwedenAdressers\Pages\CreateSwedenAdresser;
use App\Filament\Resources\SwedenAdressers\Pages\EditSwedenAdresser;
use App\Filament\Resources\SwedenAdressers\Pages\ListSwedenAdressers;
use App\Filament\Resources\SwedenAdressers\Pages\ViewSwedenAdresser;
use App\Filament\Resources\SwedenAdressers\Schemas\SwedenAdresserForm;
use App\Filament\Resources\SwedenAdressers\Schemas\SwedenAdresserInfolist;
use App\Filament\Resources\SwedenAdressers\Tables\SwedenAdressersTable;
use App\Models\SwedenAdresser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SwedenAdresserResource extends Resource
{
    protected static ?string $model = SwedenAdresser::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Sweden GEO';

    protected static ?string $navigationLabel = 'Adresser';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    public static function form(Schema $schema): Schema
    {
        return SwedenAdresserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SwedenAdresserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SwedenAdressersTable::configure($table);
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
            'index' => ListSwedenAdressers::route('/'),
            'create' => CreateSwedenAdresser::route('/create'),
            'view' => ViewSwedenAdresser::route('/{record}'),
            'edit' => EditSwedenAdresser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) self::getModel()::count();
    }
}
