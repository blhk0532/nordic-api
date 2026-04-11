<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

trait InteractsWithEvent
{
    #[Locked]
    public ?Model $eventRecord = null;

    public function getEventRecord(): ?Model
    {
        return $this->eventRecord;
    }

    public function getEventModel(): ?string
    {
        if ($record = $this->getEventRecord()) {
            return $record::class;
        }

        return null;
    }

    protected function resolveEventRecord(): ?Model
    {
        $model = $this->getRawCalendarContextData('event.extendedProps.model');
        $key = $this->getRawCalendarContextData('event.extendedProps.key');

        // Cannot resolve event record
        if (! $model || ! $key) {
            throw new Exception('Could not resolve event record. A [model] or [key] property set in the [extendedProps] of the mounted event was missing.');
        }

        if ($record = $this->resolveEventRecordRouteBinding($model, $key)) {
            return $this->eventRecord = $record;
        }

        // Return null if record not found, instead of throwing
        return $this->eventRecord = null;
    }

    protected function resolveEventRecordRouteBinding(string $model, mixed $key): ?Model
    {
        return $this->getEloquentQueryForModel($model)
            ->where($this->getEventRecordRouteKeyName($model), $key)
            ->first();
    }

    protected function getEloquentQueryForModel(string $model): Builder
    {
        return $model::query();
    }

    protected function getEventRecordRouteKeyName(?string $model = null): ?string
    {
        if (! $model) {
            return 'id';
        }

        return (new $model)->getRouteKeyName();
    }
}
