<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Actions;

use Adultdate\FilamentBooking\Filament\Widgets\FullCalendarWidget;
use Filament\Actions\Action;
use Filament\Actions\CreateAction as BaseCreateAction;
use Filament\Schemas\Schema as FilamentSchema;

class CreateAction extends BaseCreateAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->model(
            fn (FullCalendarWidget $livewire) => $livewire->getModel()
        );

        $this->schema(
            fn (FilamentSchema $schema, CreateAction $action, FullCalendarWidget $livewire) => $livewire->getFormSchemaForModel($schema, $livewire->getModel())
        );

        $this->after(
            fn (FullCalendarWidget $livewire) => $livewire->refreshRecords()
        );

        $this->modalFooterActions(fn (CreateAction $action, FullCalendarWidget $livewire) => [
            // Keep the default form actions (Create, Create & create another, etc.)
            ...$livewire->getCachedFormActions(),

            // Add a "Block Period" button before the cancel button so it is visible
            Action::make('block-period')
                ->label('Block Period')
                ->icon('heroicon-o-ban')
                ->color('danger')
                ->button()
                ->action(fn () => $livewire->dispatch('block-period')),

            // Keep the cancel button
            $action->getModalCancelAction(),
        ]);

        $this->cancelParentActions();
    }
}
