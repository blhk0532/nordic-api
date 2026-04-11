<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings;

use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Pages\CreateBooking;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Pages\EditBooking;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Pages\ListBookings;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Schemas\BookingForm;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Tables\BookingsTable;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Widgets\BookingStats;
use Adultdate\FilamentBooking\Models\Booking\Booking;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $slug = 'services/bookings';

    protected static ?string $recordTitleAttribute = 'number';

    protected static string|UnitEnum|null $navigationGroup = 'Bokningar Admin';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 0;

    /**
     * Disable Filament tenant scoping for this resource to avoid
     * requiring a `team` relationship on the Booking model.
     */
    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return BookingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingsTable::configure($table);
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
            BookingStats::class,
        ];
    }

    /** @return Builder<Booking> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'client.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Booking $record */

        return [
            'Customer' => optional($record->client)->name,
        ];
    }

    /** @return Builder<Booking> */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['client', 'items']);
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = self::$model;

        return (string) $modelClass::where('status', 'booked')->count();
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        return self::calculateTotalPrice($data);
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        return self::calculateTotalPrice($data);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
            'create' => CreateBooking::route('/create'),
            'edit' => EditBooking::route('/{record}/edit'),
        ];
    }

    /** @return Builder<Booking> */
    public static function getTableQuery(): Builder
    {
        return parent::getTableQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    protected static function calculateTotalPrice(array $data): array
    {
        $totalPrice = 0;

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $qty = isset($item['qty']) ? (int) $item['qty'] : 0;
                $unit = isset($item['unit_price']) ? (float) $item['unit_price'] : 0;
                $totalPrice += $qty * $unit;
            }
        }

        $data['total_price'] = $totalPrice;

        if (empty($data['currency'])) {
            $data['currency'] = 'SEK';
        }

        return $data;
    }
}
