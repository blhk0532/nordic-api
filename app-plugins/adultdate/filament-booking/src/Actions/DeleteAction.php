<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Actions;

use Adultdate\FilamentBooking\Filament\Widgets\FullCalendarWidget;
use Filament\Actions\DeleteAction as BaseDeleteAction;

class DeleteAction extends BaseDeleteAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->model(
            fn (FullCalendarWidget $livewire) => $livewire->getModel()
        );

        $this->record(
            fn (FullCalendarWidget $livewire) => $livewire->getRecord()
        );

        $this->after(
            function (FullCalendarWidget $livewire) {
                $livewire->record = null;
                $livewire->refreshRecords();
            }
        );

        $this->cancelParentActions();
    }
}
