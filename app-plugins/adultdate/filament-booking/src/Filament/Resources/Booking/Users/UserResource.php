<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\Users;

use Adultdate\FilamentBooking\Filament\Resources\Booking\Users\Pages\CreateUser;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Users\Pages\EditUser;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Users\Pages\ListUsers;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Users\Pages\ManageServiceProviderSchedules;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Users\Schemas\UserForm;
use Adultdate\FilamentBooking\Filament\Resources\Booking\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static ?string $navigationLabel = 'Användare';

    protected static string|UnitEnum|null $navigationGroup = 'Bokningar Admin';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 20;

    /**
     * Disable Filament tenant scoping for this resource to avoid
     * requiring a `team` relationship on the User model.
     */
    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
            'schedule' => ManageServiceProviderSchedules::route('/{record}/schedule'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
