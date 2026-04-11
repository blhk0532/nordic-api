<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Widgets\Concerns;

use Adultdate\FilamentBooking\FilamentBookingPlugin;
use Carbon\Carbon;

trait InteractsWithEvents
{
    /**
     * Triggered when user clicks an event.
     *
     * @param  array  $event  An Event Object that holds information about the event (date, title, etc).
     */
    public function onEventClickLegacy(array $event): void
    {
        if ($this->getModel()) {
            $this->record = $this->resolveRecord($event['id']);
        }

        $this->mountAction('view', [
            'type' => 'click',
            'event' => $event,
        ]);
    }

    /**
     * Triggered when dragging stops and the event has moved to a different day/time.
     *
     * @param  array  $event  An Event Object that holds information about the event (date, title, etc) after the drop.
     * @param  array  $oldEvent  An Event Object that holds information about the event before the drop.
     * @param  array  $relatedEvents  An array of other related Event Objects that were also dropped. An event might have other recurring event instances or might be linked to other events with the same groupId
     * @param  array  $delta  A Duration Object that represents the amount of time the event was moved by.
     * @param  ?array  $oldResource  A Resource Object that represents the previously assigned resource.
     * @param  ?array  $newResource  A Resource Object that represents the newly assigned resource.
     * @return bool Whether to revert the drop action.
     */
    public function onEventDropLegacy(array $event, array $oldEvent, array $relatedEvents, array $delta, ?array $oldResource, ?array $newResource): bool
    {
        if ($this->getModel()) {
            $this->record = $this->resolveRecord($event['id']);
        }

        $this->mountAction('edit', [
            'type' => 'drop',
            'event' => $event,
            'oldEvent' => $oldEvent,
            'relatedEvents' => $relatedEvents,
            'delta' => $delta,
            'oldResource' => $oldResource,
            'newResource' => $newResource,
        ]);

        return false;
    }

    /**
     * Triggered when resizing stops and the event has changed in duration.
     *
     * @param  array  $event  An Event Object that holds information about the event (date, title, etc) after the drop.
     * @param  array  $oldEvent  An Event Object that holds information about the event before the drop.
     * @param  array  $relatedEvents  An array of other related Event Objects that were also dropped. An event might have other recurring event instances or might be linked to other events with the same groupId
     * @param  array  $startDelta  A Duration Object that represents the amount of time the event's start date was moved by.
     * @param  array  $endDelta  A Duration Object that represents the amount of time the event's end date was moved by.
     * @return mixed Whether to revert the resize action.
     */
    public function onEventResizeLegacy(array $event, array $oldEvent, array $relatedEvents, array $startDelta, array $endDelta): bool
    {
        if ($this->getModel()) {
            $this->record = $this->resolveRecord($event['id']);
        }

        $this->mountAction('edit', [
            'type' => 'resize',
            'event' => $event,
            'oldEvent' => $oldEvent,
            'relatedEvents' => $relatedEvents,
            'startDelta' => $startDelta,
            'endDelta' => $endDelta,
        ]);

        return false;
    }

    /**
     * Triggered when a date/time selection is made (single or multiple days).
     *
     * @param  object  $info  contains information about the selected date range (expects ->start, ->end, ->allDay)
     */
    public function onDateSelectLegacy(object $info): void
    {
        $this->mountAction('create', [
            'type' => 'select',
            'start' => $info->start->toIsoString(),
            'end' => $info->end ? $info->end->toIsoString() : null,
            'allDay' => $info->allDay,
            'resource' => null, // DateSelectInfo doesn't have resource
        ]);
    }

    public function refreshRecords(): void
    {
        $this->dispatch('filament-fullcalendar--refresh');
    }

    protected function calculateTimezoneOffset(string $start, ?string $end, bool $allDay): array
    {
        $timezone = FilamentBookingPlugin::make()->getTimezone();

        $start = Carbon::parse($start, $timezone);

        if ($end) {
            $end = Carbon::parse($end, $timezone);
        }

        if (! is_null($end) && $allDay) {
            /**
             * date is exclusive, read more https://fullcalendar.io/docs/select-callback
             * For example, if the selection is day and the last day is a Thursday, end will be Friday.
             */
            $end->subDay()->endOfDay();
        }

        return [$start, $end, $allDay];
    }
}
