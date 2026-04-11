<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Actions;

use Adultdate\FilamentBooking\Contracts\HasCalendar;
use Filament\Schemas\Schema;

class EditAction extends \Filament\Actions\EditAction
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
            ->schema(
                fn (EditAction $action, Schema $schema, HasCalendar $livewire): Schema => $livewire
                    ->getFormSchemaForModel($schema, $action->getModel())
                    ->record($livewire->getEventRecord())
            )
            ->cancelParentActions();
    }
}
