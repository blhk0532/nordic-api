<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\ValueObjects;

use Adultdate\FilamentBooking\Contracts\ContextualInfo;
use Adultdate\FilamentBooking\Enums\Context;
use Carbon\CarbonImmutable;

use function Adultdate\FilamentBooking\utc_to_user_local_time;

final readonly class DateClickInfo implements ContextualInfo
{
    public CarbonImmutable $date;

    public bool $allDay;

    public CalendarView $view;

    private array $originalData;

    public function __construct(array $data, bool $useFilamentTimezone)
    {
        $this->originalData = $data;

        $this->date = utc_to_user_local_time(
            data_get($data, 'date'),
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
        return Context::DateClick;
    }
}
