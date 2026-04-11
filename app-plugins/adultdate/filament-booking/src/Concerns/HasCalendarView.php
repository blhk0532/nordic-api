<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

use Adultdate\FilamentBooking\Enums\CalendarViewType;

trait HasCalendarView
{
    protected CalendarViewType $calendarView = CalendarViewType::DayGridMonth;

    public function getCalendarView(): CalendarViewType
    {
        return $this->calendarView;
    }
}
