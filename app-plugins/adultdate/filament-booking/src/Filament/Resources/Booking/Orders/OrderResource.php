<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\Orders;

use Adultdate\FilamentBooking\Filament\Resources\Booking\Orders\Pages\CreateOrder;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Orders\Pages\EditOrder;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Orders\Pages\ListOrders;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Orders\RelationManagers\PaymentsRelationManager;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Orders\Schemas\OrderForm;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Orders\Tables\OrdersTable;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Orders\Widgets\OrderStats;
use Adultdate\FilamentBooking\Models\Booking\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $slug = 'booking/orders';

    protected static ?string $recordTitleAttribute = 'number';

    protected static string|UnitEnum|null $navigationGroup = 'Produkt';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 11;

    /**
     * Disable Filament tenant scoping for this resource to avoid
     * requiring a `team` relationship on the Order model.
     */
    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OrderStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }

    /** @return Builder<Order> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'customer.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Order $record */

        return [
            'Customer' => optional($record->customer)->name,
        ];
    }

    /** @return Builder<Order> */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['customer', 'items']);
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = self::$model;

        return (string) $modelClass::where('status', 'new')->count();
    }
}
