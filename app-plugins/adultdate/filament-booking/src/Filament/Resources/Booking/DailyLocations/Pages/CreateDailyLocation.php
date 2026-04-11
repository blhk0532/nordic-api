<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Pages;

use Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\DailyLocationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDailyLocation extends CreateRecord
{
    protected static string $resource = DailyLocationResource::class;
}
