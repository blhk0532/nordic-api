<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\Pages;

use Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\BookingServicePeriodResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBookingServicePeriod extends CreateRecord
{
    protected static string $resource = BookingServicePeriodResource::class;
}
