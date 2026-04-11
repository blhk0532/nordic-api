<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Actions;

use Adultdate\FilamentBooking\Filament\Resources\Booking\DailyLocations\Widgets\EventCalendar;
use Filament\Actions\ViewAction as BaseViewAction;

class ViewAction extends BaseViewAction
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

        $this->schema(
            fn (EventCalendar $livewire) => $livewire->getFormSchema()
        );

        $this->modalFooterActions(
            fn (ViewAction $action, EventCalendar $livewire) => [
                ...$livewire->getCachedFormActions(),
                $action->getModalCancelAction(),
            ]
        );

        $this->after(
            fn (EventCalendar $livewire) => $livewire->refreshRecords()
        );

        $this->cancelParentActions();
    }
}
