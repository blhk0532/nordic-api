<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\BookingCalendars\Pages;

use Adultdate\FilamentBooking\Filament\Resources\BookingCalendars\BookingCalendarResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBookingCalendar extends CreateRecord
{
    protected static string $resource = BookingCalendarResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['creator_id'] = Auth::id();

        return $data;
    }
}
