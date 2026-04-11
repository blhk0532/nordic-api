<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

use Adultdate\FilamentBooking\Contracts\ContextualInfo;
use Adultdate\FilamentBooking\Contracts\HasCalendar;
use Adultdate\FilamentBooking\ValueObjects\DateClickInfo;
use Adultdate\FilamentBooking\ValueObjects\DateSelectInfo;
use Adultdate\FilamentBooking\ValueObjects\EventClickInfo;
use Adultdate\FilamentBooking\ValueObjects\NoEventsClickInfo;

trait CalendarAction
{
    protected function resolveDefaultClosureDependencyForEvaluationByType(string $parameterType): array
    {
        /** @var InteractsWithCalendar $livewire */
        $livewire = $this->getLivewire();

        // Action is used outside the calendar
        if (! ($livewire instanceof HasCalendar)) {
            return parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType);
        }

        return match ($parameterType) {
            DateClickInfo::class, DateSelectInfo::class, EventClickInfo::class, NoEventsClickInfo::class, ContextualInfo::class => [
                $livewire->getCalendarContextInfo(),
            ],
            default => parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType),
        };
    }
}
