<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

use Illuminate\Contracts\Support\Htmlable;

trait HasHeading
{
    protected string|Htmlable|null|bool $heading = true;

    public function getHeading(): string|Htmlable
    {
        if ($this->heading === false || is_null($this->heading)) {
            return '';
        }

        if ($this->heading === true) {
            return __('filament-booking::translations.heading');
        }

        return $this->heading;
    }
}
