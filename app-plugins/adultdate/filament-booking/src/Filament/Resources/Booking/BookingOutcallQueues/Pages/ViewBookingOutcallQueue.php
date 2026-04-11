<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\BookingOutcallQueues\Pages;

use Adultdate\FilamentBooking\Filament\Resources\Booking\BookingOutcallQueues\BookingOutcallQueueResource;
use Adultdate\FilamentBooking\Filament\Resources\Booking\BookingOutcallQueues\Widgets\BookingOutcallQueueActions;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBookingOutcallQueue extends ViewRecord
{
    protected static string $resource = BookingOutcallQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BookingOutcallQueueActions::make(),
        ];
    }
}
