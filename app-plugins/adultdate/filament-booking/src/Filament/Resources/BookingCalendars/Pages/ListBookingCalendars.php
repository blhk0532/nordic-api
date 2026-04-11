<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\BookingCalendars\Pages;

use Adultdate\FilamentBooking\Filament\Resources\BookingCalendars\BookingCalendarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookingCalendars extends ListRecords
{
    protected static string $resource = BookingCalendarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
