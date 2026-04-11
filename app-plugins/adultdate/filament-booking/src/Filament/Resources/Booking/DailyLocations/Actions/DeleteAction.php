<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Actions;

use Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Widgets\EventCalendar;
use Filament\Actions\DeleteAction as BaseDeleteAction;

class DeleteAction extends BaseDeleteAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->model(
            fn (EventCalendar $livewire) => $livewire->getModel()
        );

        $this->record(
            fn (EventCalendar $livewire) => $livewire->getRecord()
        );

        $this->after(
            function (EventCalendar $livewire) {
                $livewire->record = null;
                $livewire->refreshRecords();
            }
        );

        $this->cancelParentActions();
    }
}
