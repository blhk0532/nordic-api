<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

use Adultdate\FilamentBooking\Enums\Context;
use Adultdate\FilamentBooking\ValueObjects\EventDropInfo;
use Illuminate\Database\Eloquent\Model;

trait HandlesEventDragAndDrop
{
    protected bool $eventDragEnabled = true;

    public function isEventDragEnabled(): bool
    {
        return $this->eventDragEnabled;
    }

    /**
     * @internal Do not override, internal purpose only. Use `onEventDrop()` instead
     */
    public function onEventDropJs(array $data): bool
    {
        // Check if event drag and drop is enabled
        if (! $this->isEventDragEnabled()) {
            return false;
        }

        $this->setRawCalendarContextData(Context::EventDragAndDrop, $data);

        return $this->onEventDrop($this->getCalendarContextInfo(), $this->getEventRecord());
    }

    // TODO: Add a default implementation
    // TODO: for that we need to add two methods to Eventable interface:
    // TODO: -> getStartAttribute()
    // TODO: -> getEndAttribute()
    // TODO: where the user needs to define which attributes is the start/end date
    // TODO: Then we can handle the update outselves by default
    protected function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        return true;
    }
}
