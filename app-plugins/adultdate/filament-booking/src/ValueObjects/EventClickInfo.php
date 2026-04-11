<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\ValueObjects;

use Adultdate\FilamentBooking\Contracts\ContextualInfo;
use Adultdate\FilamentBooking\Enums\Context;
use Illuminate\Database\Eloquent\Model;

final readonly class EventClickInfo implements ContextualInfo
{
    public Model $record;

    public CalendarEvent $event;

    public CalendarView $view;

    private array $originalData;

    public function __construct(array $data, Model $record, bool $useFilamentTimezone)
    {
        $this->originalData = $data;

        $this->record = $record;

        $this->event = CalendarEvent::make($record)
            ->fromCalendarObject(
                data_get($data, 'event'),
                data_get($data, 'tzOffset'),
                $useFilamentTimezone
            );

        $this->view = new CalendarView(
            data_get($data, 'view'),
            data_get($data, 'tzOffset'),
            $useFilamentTimezone
        );
    }

    public function getContext(): Context
    {
        return Context::EventClick;
    }
}
