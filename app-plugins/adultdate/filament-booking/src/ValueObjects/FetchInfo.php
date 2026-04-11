<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\ValueObjects;

use Carbon\CarbonImmutable;
use Filament\Support\Facades\FilamentTimezone;

final readonly class FetchInfo
{
    public CarbonImmutable $start;

    public CarbonImmutable $end;

    private array $originalData;

    public function __construct(array $data)
    {
        $this->originalData = $data;

        $this->start = browser_date_to_app_date(CarbonImmutable::make($data['startStr']));
        $this->end = browser_date_to_app_date(CarbonImmutable::make($data['endStr']));
    }
}

function browser_date_to_app_date(CarbonImmutable|string $date): CarbonImmutable
{
    if (is_string($date)) {
        $date = CarbonImmutable::make($date);
    }

    return browser_date_to_user_date($date)->setTimezone(config('app.timezone'));
}

function browser_date_to_user_date(CarbonImmutable|string $date): CarbonImmutable
{
    if (is_string($date)) {
        $date = CarbonImmutable::make($date);
    }

    return $date->shiftTimezone(FilamentTimezone::get());
}
