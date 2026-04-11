<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\Customers;

use Adultdate\FilamentBooking\Filament\Resources\Booking\Customers\Pages\CreateCustomer;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Customers\Pages\EditCustomer;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Customers\Pages\ListCustomers;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Customers\RelationManagers\AddressesRelationManager;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Customers\RelationManagers\PaymentsRelationManager;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Customers\Schemas\CustomerForm;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Customers\Tables\CustomersTable;
use Adultdate\FilamentBooking\Models\Booking\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $slug = 'booking/customers';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Kunder';

    protected static string|UnitEnum|null $navigationGroup = 'Produkt';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?int $navigationSort = 2;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }

    /** @return Builder<Customer> */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('addresses')->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getRelations(): array
    {
        return [
            AddressesRelationManager::class,
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }
}
