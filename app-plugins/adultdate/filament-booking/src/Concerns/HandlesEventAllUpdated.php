<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

use Adultdate\FilamentBooking\ValueObjects\EventAllUpdatedInfo;

trait HandlesEventAllUpdated
{
    protected bool $eventAllUpdatedEnabled = false;

    public function isEventAllUpdatedEnabled(): bool
    {
        return $this->eventAllUpdatedEnabled;
    }

    /**
     * @internal Do not override, internal purpose only. Use `onEventAllUpdated()` instead
     */
    public function onEventAllUpdatedJs(array $info): void
    {
        // Check if dates set is enabled
        if (! $this->isEventAllUpdatedEnabled()) {
            return;
        }

        $this->onEventAllUpdated(new EventAllUpdatedInfo($info, $this->shouldUseFilamentTimezone()));
    }

    protected function onEventAllUpdated(EventAllUpdatedInfo $info): void {}
}
