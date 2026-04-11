<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

use Adultdate\FilamentBooking\Contracts\Eventable;
use Adultdate\FilamentBooking\ValueObjects\CalendarEvent;
use Adultdate\FilamentBooking\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait HasEvents
{
    abstract protected function getEvents(FetchInfo $info): Collection|array|Builder;

    public function getEventsJs(array $info): array
    {
        $events = $this->getEvents(new FetchInfo($info));

        if ($events instanceof Builder) {
            $events = $events->get();
        }

        if (is_array($events)) {
            $events = collect($events);
        }

        return $events
            ->map(function (mixed $event) use ($info): array {
                // If this is an Eventable model convert to CalendarEvent
                if ($event instanceof Eventable) {
                    $event = $event->toCalendarEvent();
                }

                // If it's already a CalendarEvent value object, use it
                if ($event instanceof CalendarEvent) {
                    return $event->toCalendarObject(
                        data_get($info, 'tzOffset'),
                        $this->shouldUseFilamentTimezone()
                    );
                }

                // Fallback: try to adapt Eloquent models that expose the same methods
                if (is_object($event) && method_exists($event, 'toCalendarObject')) {
                    return $event->toCalendarObject(
                        data_get($info, 'tzOffset'),
                        $this->shouldUseFilamentTimezone()
                    );
                }

                // As a last resort, cast to array to avoid type errors
                return (array) $event;
            })
            ->all();
    }
}
