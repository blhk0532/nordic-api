<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\BookingOutcallQueues\Pages;

use Adultdate\FilamentBooking\BookingOutcallQueues\BookingOutcallQueueResource;
use Adultdate\FilamentBooking\BookingOutcallQueues\Widgets\BookingOutcallQueueActions;
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
