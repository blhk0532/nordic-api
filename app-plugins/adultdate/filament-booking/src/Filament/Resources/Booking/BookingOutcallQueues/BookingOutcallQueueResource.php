<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\BookingOutcallQueues;

use Adultdate\FilamentBooking\Filament\Resources\Booking\BookingOutcallQueues\Pages\CreateBookingOutcallQueue;
use Adultdate\FilamentBooking\Filament\Resources\Booking\BookingOutcallQueues\Pages\EditBookingOutcallQueue;
use Adultdate\FilamentBooking\Filament\Resources\Booking\BookingOutcallQueues\Pages\ListBookingOutcallQueues;
use Adultdate\FilamentBooking\Filament\Resources\Booking\BookingOutcallQueues\Pages\ViewBookingOutcallQueue;
use Adultdate\FilamentBooking\Filament\Resources\Booking\BookingOutcallQueues\Schemas\BookingOutcallQueueForm;
use Adultdate\FilamentBooking\Filament\Resources\Booking\BookingOutcallQueues\Schemas\BookingOutcallQueueInfolist;
use Adultdate\FilamentBooking\Filament\Resources\Booking\BookingOutcallQueues\Tables\BookingOutcallQueuesTable;
use App\Models\BookingOutcallQueue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BookingOutcallQueueResource extends Resource
{
    protected static ?string $model = BookingOutcallQueue::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

    protected static ?string $navigationLabel = 'Queues';

    protected static string|UnitEnum|null $navigationGroup = 'Nummer';

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return BookingOutcallQueueForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BookingOutcallQueueInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingOutcallQueuesTable::configure($table);
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
            'index' => ListBookingOutcallQueues::route('/'),
            'create' => CreateBookingOutcallQueue::route('/create'),
            'view' => ViewBookingOutcallQueue::route('/{record}'),
            'edit' => EditBookingOutcallQueue::route('/{record}/edit'),
        ];
    }
}
