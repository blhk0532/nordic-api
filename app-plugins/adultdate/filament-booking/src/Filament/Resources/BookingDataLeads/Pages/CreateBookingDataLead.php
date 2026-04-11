<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\BookingDataLeads\Pages;

use Adultdate\FilamentBooking\Filament\Resources\BookingDataLeads\BookingDataLeadResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBookingDataLead extends CreateRecord
{
    protected static string $resource = BookingDataLeadResource::class;
}
