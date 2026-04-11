<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Clients;

use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Clients\Pages\CreateClient;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Clients\Pages\EditClient;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Clients\Pages\ListClients;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Clients\RelationManagers\AddressesRelationManager;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Clients\RelationManagers\PaymentsRelationManager;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Clients\Schemas\ClientForm;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Clients\Tables\ClientsTable;
use Adultdate\FilamentBooking\Models\Booking\Client;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $slug = 'services/clients';

    protected static ?string $navigationLabel = 'Kunder';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Bokningar Admin';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 4;

    /**
     * Disable Filament tenant scoping for this resource to avoid
     * requiring a `team` relationship on the Client model.
     */
    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return ClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientsTable::configure($table);
    }

    /** @return Builder<Client> */
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
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'edit' => EditClient::route('/{record}/edit'),
        ];
    }
}
