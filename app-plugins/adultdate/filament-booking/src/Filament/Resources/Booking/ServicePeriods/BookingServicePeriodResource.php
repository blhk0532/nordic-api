<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods;

use Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\Pages\CreateBookingServicePeriod;
use Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\Pages\EditBookingServicePeriod;
use Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\Pages\ListBookingServicePeriods;
use Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\Pages\ViewBookingServicePeriod;
use Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\Schemas\BookingServicePeriodForm;
use Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\Schemas\BookingServicePeriodInfolist;
use Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\Tables\BookingServicePeriodsTable;
use Adultdate\FilamentBooking\Models\BookingServicePeriod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BookingServicePeriodResource extends Resource
{
    protected static ?string $model = BookingServicePeriod::class;

    protected static ?string $recordTitleAttribute = 'period';

    protected static ?string $modelLabel = 'Period';

    protected static ?string $navigationLabel = 'Stopptider';

    protected static string|UnitEnum|null $navigationGroup = 'Hantera Kalendrar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?int $navigationSort = 2;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return BookingServicePeriodForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BookingServicePeriodInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingServicePeriodsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {

        return [Widgets\BookingPeriodsCalendar::class];

    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookingServicePeriods::route('/'),
            'create' => CreateBookingServicePeriod::route('/create'),
            'view' => ViewBookingServicePeriod::route('/{record}'),
            'edit' => EditBookingServicePeriod::route('/{record}/edit'),
        ];
    }

    public static function get()
    {
        return parent::getEloquentQuery()->withCount('items');
    }
}
