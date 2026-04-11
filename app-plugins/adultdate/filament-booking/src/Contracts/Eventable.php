<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Contracts;

use Adultdate\FilamentBooking\ValueObjects\CalendarEvent;

interface Eventable
{
    public function toCalendarEvent(): CalendarEvent;
}
