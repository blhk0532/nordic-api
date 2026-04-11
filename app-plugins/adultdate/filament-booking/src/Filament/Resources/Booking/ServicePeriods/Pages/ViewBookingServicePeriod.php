<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\Pages;

use Adultdate\FilamentBooking\Filament\Resources\Booking\ServicePeriods\BookingServicePeriodResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBookingServicePeriod extends ViewRecord
{
    protected static string $resource = BookingServicePeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
