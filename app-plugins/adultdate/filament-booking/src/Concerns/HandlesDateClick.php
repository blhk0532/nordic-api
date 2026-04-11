<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

use Adultdate\FilamentBooking\Enums\Context;
use Adultdate\FilamentBooking\ValueObjects\DateClickInfo;
use phpDocumentor\Reflection\Types\This;

trait HandlesDateClick
{
    /**
     * Sets whether clicking on a date cell should be enabled for the calendar or not.
     *
     * To enable date click, set this to true and override `onDateClick` to implement your logic.
     */
    // protected bool $dateClickEnabled = false;

    public function setDateClickEnabled(bool $enabled): void
    {
        $this->dateClickEnabled = $enabled;
    }

    /**
     * Sets whether clicking on a date cell should be enabled for the calendar or not.
     *
     * To enable date click, return true and override `onDateClick` to implement your logic.
     */
    public function isDateClickEnabled(): bool
    {
        return $this->dateClickEnabled ?? false;
    }

    /**
     * @internal Do not override, internal purpose only. Use `onDateClick` instead
     */
    public function onDateClickJs(array $data): void
    {
        // Check if date click is enabled
        if (! $this->isDateClickEnabled()) {
            return;
        }

        $this->setRawCalendarContextData(Context::DateClick, $data);

        $this->onDateClick($this->getCalendarContextInfo());
    }

    /**
     * Implement your date click logic here.
     *
     * This method will only be fired when `$dateClickEnabled` is set to true.
     *
     * @param  DateClickInfo  $info  contains information about the clicked date cell
     */
    protected function onDateClick(DateClickInfo $info): void {}
}
