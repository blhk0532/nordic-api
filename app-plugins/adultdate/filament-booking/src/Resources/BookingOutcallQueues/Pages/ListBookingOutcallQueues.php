<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\BookingOutcallQueues\Pages;

use Adultdate\FilamentBooking\BookingOutcallQueues\BookingOutcallQueueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookingOutcallQueues extends ListRecords
{
    protected static string $resource = BookingOutcallQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
