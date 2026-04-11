<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

use Adultdate\FilamentBooking\Enums\Context;
use Adultdate\FilamentBooking\ValueObjects\DateSelectInfo;

trait HandlesDateSelect
{
    /**
     * Sets whether selecting a date range should be enabled for the calendar or not.
     *
     * To enable date select, set this to true and override `onDateSelect` to implement your logic.
     */
    protected bool $dateSelectEnabled = true;

    public function setDateSelectEnabled(bool $enabled): void
    {
        $this->dateSelectEnabled = $enabled;
    }

    /**
     * Implement your date select logic here.
     *
     * This method will only be fired when `$dateSelectEnabled` is set to true.
     *
     * @param  DateSelectInfo  $info  contains information about the selected date range
     */
    public function onDateSelectLegacy(DateSelectInfo $info): void {}

    /**
     * Sets whether selecting a date range should be enabled for the calendar or not.
     *
     * To enable date select, return true and override `onDateSelect` to implement your logic.
     */
    public function isDateSelectEnabled(): bool
    {
        return $this->dateSelectEnabled;
    }

    /**
     * @internal Do not override, internal purpose only. Use `onDateSelect` instead
     */
    public function onDateSelectJs(array $data): void
    {
        // Check if date select is enabled
        if (! $this->isDateSelectEnabled()) {
            return;
        }

        $this->setRawCalendarContextData(Context::DateSelect, $data);

        $this->onDateSelectLegacy($this->getCalendarContextInfo());
    }
}
