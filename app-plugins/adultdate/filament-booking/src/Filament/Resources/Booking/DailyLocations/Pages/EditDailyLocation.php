<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Pages;

use Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\DailyLocationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDailyLocation extends EditRecord
{
    protected static string $resource = DailyLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
