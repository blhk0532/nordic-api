<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\ValueObjects;

use Carbon\CarbonImmutable;

use function Adultdate\FilamentBooking\utc_to_user_local_time;

final readonly class DatesSetInfo
{
    public CarbonImmutable $start;

    public CarbonImmutable $end;

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

        $this->view = new CalendarView(
            data_get($data, 'view'),
            data_get($data, 'tzOffset'),
            $useFilamentTimezone
        );
    }
}
