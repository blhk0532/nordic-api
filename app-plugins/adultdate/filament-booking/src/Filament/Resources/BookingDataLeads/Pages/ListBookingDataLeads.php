<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\BookingDataLeads\Pages;

use Adultdate\FilamentBooking\Filament\Resources\BookingDataLeads\BookingDataLeadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookingDataLeads extends ListRecords
{
    protected static string $resource = BookingDataLeadResource::class;

    protected static ?string $title = 'Leads';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
