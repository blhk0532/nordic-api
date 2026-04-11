<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Contracts;

use Adultdate\FilamentBooking\ValueObjects\CalendarResource;

interface Resourceable
{
    public function toCalendarResource(): CalendarResource;
}
