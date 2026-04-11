<?php

namespace App\Filament\Resources\SwedenGators;

use App\Filament\Resources\SwedenGators\Pages\CreateSwedenGator;
use App\Filament\Resources\SwedenGators\Pages\EditSwedenGator;
use App\Filament\Resources\SwedenGators\Pages\ListSwedenGators;
use App\Filament\Resources\SwedenGators\Pages\ViewSwedenGator;
use App\Filament\Resources\SwedenGators\Schemas\SwedenGatorForm;
use App\Filament\Resources\SwedenGators\Schemas\SwedenGatorInfolist;
use App\Filament\Resources\SwedenGators\Tables\SwedenGatorsTable;
use App\Models\SwedenGator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SwedenGatorResource extends Resource
{
    protected static ?string $model = SwedenGator::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|UnitEnum|null $navigationGroup = 'Sweden GEO';

    protected static ?string $navigationLabel = 'Gator';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    public static function form(Schema $schema): Schema
    {
        return SwedenGatorForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SwedenGatorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SwedenGatorsTable::configure($table);
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
            'index' => ListSwedenGators::route('/'),
            'create' => CreateSwedenGator::route('/create'),
            'view' => ViewSwedenGator::route('/{record}'),
            'edit' => EditSwedenGator::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) self::getModel()::count();
    }
}
