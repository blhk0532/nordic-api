<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\ValueObjects;

use Adultdate\FilamentBooking\Enums\CalendarViewType;
use Carbon\CarbonImmutable;

use function Adultdate\FilamentBooking\utc_to_user_local_time;

final readonly class CalendarView
{
    public CalendarViewType $type;

    public string $title;

    public CalendarView $view;

    public CarbonImmutable $currentStart;

    public CarbonImmutable $currentEnd;

    public CarbonImmutable $activeStart;

    public CarbonImmutable $activeEnd;

    private array $originalData;

    public function __construct(array $data, int $timezoneOffset, bool $useFilamentTimezone)
    {
        $this->originalData = $data;

        $this->type = CalendarViewType::tryFrom(data_get($data, 'type'));
        $this->title = data_get($data, 'title');
        $this->currentStart = utc_to_user_local_time(data_get($data, 'currentStart'), $timezoneOffset, $useFilamentTimezone);
        $this->currentEnd = utc_to_user_local_time(data_get($data, 'currentEnd'), $timezoneOffset, $useFilamentTimezone);
        $this->activeStart = utc_to_user_local_time(data_get($data, 'activeStart'), $timezoneOffset, $useFilamentTimezone);
        $this->activeEnd = utc_to_user_local_time(data_get($data, 'activeEnd'), $timezoneOffset, $useFilamentTimezone);
    }
}
