<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

use Adultdate\FilamentBooking\Enums\Context;
use Adultdate\FilamentBooking\ValueObjects\EventClickInfo;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

trait HandlesEventClick
{
    protected bool $eventClickEnabled = true;

    protected ?string $defaultEventClickAction = 'view';

    public function isEventClickEnabled(): bool
    {
        return $this->eventClickEnabled;
    }

    public function getDefaultEventClickAction(): ?string
    {
        return $this->evaluate($this->defaultEventClickAction);
    }

    /**
     * @internal Do not override, internal purpose only. Use `onEventClick` instead
     */
    public function onEventClickJs(array $data = [], ?string $action = null): void
    {
        // Check if event click is enabled
        if (! $this->isEventClickEnabled()) {
            return;
        }

        Log::debug('onEventClickJs called (schedule)', ['data' => $data, 'action' => $action]);

        $this->setRawCalendarContextData(Context::EventClick, $data);

        $action ??= $this->getRawCalendarContextData('event.extendedProps.action');
        $action ??= $this->getDefaultEventClickAction();

        // TODO: Similar to how Schemas work, allow users to define a method for each Event Model Type
        // TODO: using attributes. such as #[CalendarEventClick(Sprint::class)] above a method
        // TODO: such as onSprintEventClick would be only called for Sprint.
        // If the concrete class defines a legacy `onEventClick(array $event)` method, prefer calling it.
        if (is_callable([$this, 'onEventClick'])) {
            try {
                $ref = new \ReflectionMethod($this, 'onEventClick');
                $params = $ref->getParameters();

                // Legacy handlers typically accept a single array parameter.
                if (count($params) === 1) {
                    $this->onEventClick($data['event'] ?? []);

                    return;
                }
            } catch (\ReflectionException $_) {
                // ignore and fall back to trait handler
            }
        }

        $this->handleEventClickInternal($this->getCalendarContextInfo(), $this->getEventRecord(), $action);
    }

    /**
     * Internal handler for event clicks when using the new ValueObject-based API.
     *
     * @throws Exception
     */
    protected function handleEventClickInternal(EventClickInfo $info, Model $event, ?string $action = null): void
    {
        // No action to trigger
        if (! $action) {
            return;
        }

        $this->mountAction($action);
    }

    // TODO: Might be worth looking into to automatically choose between view /edit action based on the permissions
    //
    //    protected function resolveDefaultEventClickAction() {
    //        foreach (['view', 'edit'] as $action) {
    //            $action = $this->getAction($action);
    //
    //            if (! $action) {
    //                continue;
    //            }
    //
    //            $action = clone $action;
    //
    //            $action->record($record);
    //            $action->getGroup()?->record($record);
    //
    //            if ($action->isHidden()) {
    //                continue;
    //            }
    //
    //            $url = $action->getUrl();
    //
    //            if (! $url) {
    //                continue;
    //            }
    //
    //            return $url;
    //        }
    //
    //    }
}
