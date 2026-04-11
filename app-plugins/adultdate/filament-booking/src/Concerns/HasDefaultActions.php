<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

use Adultdate\FilamentBooking\Filament\Actions\CreateAction;
use Adultdate\FilamentBooking\Filament\Actions\DeleteAction;
use Adultdate\FilamentBooking\Filament\Actions\EditAction;
use Adultdate\FilamentBooking\Filament\Actions\ViewAction;
use Adultdate\FilamentBooking\Models\Booking\Booking;
use Adultdate\FilamentBooking\Models\Booking\DailyLocation;
use Adultdate\FilamentBooking\Models\BookingMeeting;
use Adultdate\FilamentBooking\Models\BookingServicePeriod;
use Adultdate\FilamentBooking\Models\BookingSprint;
use Filament\Actions\Action;
use Illuminate\Support\Str;
use Throwable;

trait HasDefaultActions
{
    public function viewAction(): ViewAction
    {
        return ViewAction::make();
    }

    public function editAction(): EditAction
    {
        return EditAction::make();
    }

    public function deleteAction(): DeleteAction
    {
        return DeleteAction::make();
    }

    /**
     * Returns a create action configured for the specified model.
     *
     * @param  string  $model  The model class for which you want to make a create action.
     */
    protected function createAction(?string $model = null, ?string $name = null): CreateAction
    {
        if (! $model && method_exists($this, 'getModel')) {
            $model = $this->getModel();
        }

        if (! $name) {
            $name = $model ? Str::of('create')->append(Str::pascal(class_basename($model)))->toString() : 'create';
        }

        $action = CreateAction::make($name);

        if ($model) {
            $action->model($model);
        }

        return $action;
    }

    /**
     * Cache default actions so they can be resolved by name when mounted.
     * This is invoked by Filament's `InteractsWithActions::cacheTraitActions()`
     * during the Livewire component boot cycle.
     */
    protected function cacheHasDefaultActions(): void
    {
        // Cache common create actions used by the calendar widgets.
        try {
            $this->cacheAction($this->createAction(BookingMeeting::class, 'ctxCreateMeeting'));
        } catch (Throwable $e) {
            // Swallow - caching is best-effort during boot.
        }

        try {
            $this->cacheAction($this->createAction(BookingSprint::class, 'ctxCreateSprint'));
        } catch (Throwable $e) {
            // Swallow
        }

        try {
            $this->cacheAction($this->createAction(DailyLocation::class, 'ctxCreateDailyLocation'));
        } catch (Throwable $e) {
            // Swallow - caching is best-effort during boot.
        }

        try {
            $this->cacheAction($this->createAction(Booking::class, 'ctxCreateBooking'));
        } catch (Throwable $e) {
            // Swallow
        }

        try {
            $this->cacheAction($this->createAction(BookingServicePeriod::class, 'ctxCreateServicePeriod'));
        } catch (Throwable $e) {
            // Swallow
        }
    }
}
