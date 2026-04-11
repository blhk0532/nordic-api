<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\ValueObjects;

use Adultdate\FilamentBooking\Contracts\ContextualInfo;
use Adultdate\FilamentBooking\Enums\Context;
use Carbon\CarbonImmutable;

use function Adultdate\FilamentBooking\utc_to_user_local_time;

final readonly class DateSelectInfo implements ContextualInfo
{
    public CarbonImmutable $start;

    public CarbonImmutable $end;

    public bool $allDay;

    public CalendarView $view;

    private array $originalData;

    public function __construct(array $data, bool $useFilamentTimezone)
    {
        $this->originalData = $data;

        $this->start = utc_to_user_local_time(
            data_get($data, 'start'),
            data_get($data, 'tzOffset'),
            $useFilamentTimezone
        );

        $this->end = utc_to_user_local_time(
            data_get($data, 'end'),
            data_get($data, 'tzOffset'),
            $useFilamentTimezone
        );

        $this->allDay = data_get($data, 'allDay');

        $this->view = new CalendarView(
            data_get($data, 'view'),
            data_get($data, 'tzOffset'),
            $useFilamentTimezone
        );
    }

    public function getContext(): Context
    {
        return Context::DateSelect;
    }
}
