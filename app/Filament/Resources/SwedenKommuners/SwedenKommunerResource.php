<?php

namespace App\Filament\Resources\SwedenKommuners;

use App\Filament\Resources\SwedenKommuners\Pages\CreateSwedenKommuner;
use App\Filament\Resources\SwedenKommuners\Pages\EditSwedenKommuner;
use App\Filament\Resources\SwedenKommuners\Pages\ListSwedenKommuners;
use App\Filament\Resources\SwedenKommuners\Pages\ViewSwedenKommuner;
use App\Filament\Resources\SwedenKommuners\Schemas\SwedenKommunerForm;
use App\Filament\Resources\SwedenKommuners\Schemas\SwedenKommunerInfolist;
use App\Filament\Resources\SwedenKommuners\Tables\SwedenKommunersTable;
use App\Models\SwedenKommuner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class SwedenKommunerResource extends Resource
{
    protected static ?string $model = SwedenKommuner::class;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Kommuner';

    protected static string|UnitEnum|null $navigationGroup = 'Sweden GEO';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static bool $shouldRegisterNavigation = false;


        public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    public static function form(Schema $schema): Schema
    {
        return SwedenKommunerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SwedenKommunerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SwedenKommunersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [

            RelationPages\KommunPostorterPage::class,
            RelationPages\KommunPostnummerPage::class,
            RelationPages\KommunGatorPage::class,
            RelationPages\KommunAdresserPage::class,
            RelationPages\KommunPersonerPage::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSwedenKommuners::route('/'),
            'create' => CreateSwedenKommuner::route('/create'),
            'view' => ViewSwedenKommuner::route('/{record}'),
            'edit' => EditSwedenKommuner::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) self::getModel()::count();
    }
}
