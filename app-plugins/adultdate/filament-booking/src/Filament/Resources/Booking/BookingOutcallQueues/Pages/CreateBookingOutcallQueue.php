<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\BookingOutcallQueues\Pages;

use Adultdate\FilamentBooking\Filament\Resources\Booking\BookingOutcallQueues\BookingOutcallQueueResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBookingOutcallQueue extends CreateRecord
{
    protected static string $resource = BookingOutcallQueueResource::class;
}
