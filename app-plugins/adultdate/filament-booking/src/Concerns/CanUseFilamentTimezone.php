<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

trait CanUseFilamentTimezone
{
    protected bool $useFilamentTimezone = false;

    public function shouldUseFilamentTimezone(): bool
    {
        return $this->useFilamentTimezone;
    }
}
