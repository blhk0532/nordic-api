<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\BookingOutcallQueues\Pages;

use Adultdate\FilamentBooking\BookingOutcallQueues\BookingOutcallQueueResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBookingOutcallQueue extends EditRecord
{
    protected static string $resource = BookingOutcallQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
