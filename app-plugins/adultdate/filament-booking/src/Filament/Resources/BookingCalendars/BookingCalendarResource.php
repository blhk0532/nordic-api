<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\BookingCalendars;

use Adultdate\FilamentBooking\Filament\Resources\BookingCalendars\Pages\CreateBookingCalendar;
use Adultdate\FilamentBooking\Filament\Resources\BookingCalendars\Pages\EditBookingCalendar;
use Adultdate\FilamentBooking\Filament\Resources\BookingCalendars\Pages\ListBookingCalendars;
use Adultdate\FilamentBooking\Filament\Resources\BookingCalendars\Schemas\BookingCalendarForm;
use Adultdate\FilamentBooking\Filament\Resources\BookingCalendars\Tables\BookingCalendarsTable;
use Adultdate\FilamentBooking\Models\BookingCalendar;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class BookingCalendarResource extends Resource
{
    protected static ?string $model = BookingCalendar::class;

    protected static ?string $navigationLabel = 'Kalendrar';

    protected static bool $isScopedToTenant = false;

    protected static string|UnitEnum|null $navigationGroup = 'Hantera Kalendrar';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $sort = 1;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return BookingCalendarForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingCalendarsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $panel = filament()->getCurrentPanel();

        return $panel && in_array($panel->getId(), ['super', 'booking', 'calendar', 'queue']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookingCalendars::route('/'),
            'create' => CreateBookingCalendar::route('/create'),
            'edit' => EditBookingCalendar::route('/{record}/edit'),
        ];
    }
}
