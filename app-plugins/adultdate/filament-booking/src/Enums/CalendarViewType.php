<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Enums;

enum CalendarViewType: string
{
    case DayGridMonth = 'dayGridMonth';
    case ListDay = 'listDay';
    case ListWeek = 'listWeek';
    case ListMonth = 'listMonth';
    case ListYear = 'listYear';
    case ResourceTimeGridDay = 'resourceTimeGridDay';
    case ResourceTimeGridWeek = 'resourceTimeGridWeek';
    case ResourceTimelineDay = 'resourceTimelineDay';
    case ResourceTimelineWeek = 'resourceTimelineWeek';
    case ResourceTimelineMonth = 'resourceTimelineMonth';
    case TimeGridDay = 'timeGridDay';
    case TimeGridWeek = 'timeGridWeek';

}
