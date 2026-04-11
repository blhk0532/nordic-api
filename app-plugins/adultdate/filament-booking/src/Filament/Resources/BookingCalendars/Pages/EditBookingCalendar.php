<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\BookingCalendars\Pages;

use Adultdate\FilamentBooking\Filament\Resources\BookingCalendars\BookingCalendarResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBookingCalendar extends EditRecord
{
    protected static string $resource = BookingCalendarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
