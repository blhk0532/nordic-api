<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\BookingDataLeads\Pages;

use Adultdate\FilamentBooking\Filament\Resources\BookingDataLeads\BookingDataLeadResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBookingDataLead extends ViewRecord
{
    protected static string $resource = BookingDataLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
