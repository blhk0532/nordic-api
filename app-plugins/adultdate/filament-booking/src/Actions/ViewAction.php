<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Actions;

use Adultdate\FilamentBooking\Filament\Widgets\FullCalendarWidget;
use Filament\Actions\ViewAction as BaseViewAction;

class ViewAction extends BaseViewAction
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

        $this->schema(
            fn (FullCalendarWidget $livewire) => $livewire->getFormSchema()
        );

        $this->modalFooterActions(
            fn (ViewAction $action, FullCalendarWidget $livewire) => [
                ...$livewire->getCachedFormActions(),
                $action->getModalCancelAction(),
            ]
        );

        $this->after(
            fn (FullCalendarWidget $livewire) => $livewire->refreshRecords()
        );

        $this->cancelParentActions();
    }
}
