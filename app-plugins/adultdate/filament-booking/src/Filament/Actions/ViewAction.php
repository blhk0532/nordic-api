<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Actions;

use Adultdate\FilamentBooking\Contracts\HasCalendar;

class ViewAction extends \Filament\Actions\ViewAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->model(fn (HasCalendar $livewire) => $livewire->getEventModel())
            ->record(fn (HasCalendar $livewire) => $livewire->getEventRecord())
            ->before(function (HasCalendar $livewire) {
                if (! $livewire->getEventRecord()) {
                    $livewire->refreshRecords();

                    return false; // Prevent the action
                }

                return true;
            })
            ->cancelParentActions();
    }
}
