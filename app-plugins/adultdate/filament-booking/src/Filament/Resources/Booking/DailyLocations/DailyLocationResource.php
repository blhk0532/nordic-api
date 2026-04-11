<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations;

use Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Pages\CreateDailyLocation;
use Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Pages\EditDailyLocation;
use Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Pages\ListDailyLocations;
use Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Pages\ViewDailyLocation;
use Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Schemas\DailyLocationForm;
use Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Schemas\DailyLocationInfolist;
use Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Tables\DailyLocationsTable;
use Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Widgets\LocationCalendarWidget;
use Adultdate\FilamentBooking\Models\Booking\DailyLocation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DailyLocationResource extends Resource
{
    protected static ?string $model = DailyLocation::class;

    protected static ?string $recordTitleAttribute = 'location';

    protected static ?string $modelLabel = 'Location';

    protected static ?string $navigationLabel = 'Områden';

    protected static string|UnitEnum|null $navigationGroup = 'Hantera Kalendrar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?int $navigationSort = 5;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return DailyLocationForm::configure($schema);
    }

    /**
     * Force visibility for debugging; remove or tighten this before production.
     */
    public static function canViewAny(): bool
    {
        return true;
    }

    public static function infolist(Schema $schema): Schema
    {
        return DailyLocationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyLocationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            LocationCalendarWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDailyLocations::route('/'),
            'create' => CreateDailyLocation::route('/create'),
            //    'view' => ViewDailyLocation::route('/{record}'),
            'edit' => EditDailyLocation::route('/{record}/edit'),
        ];
    }
}
