<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models;

use Adultdate\FilamentBooking\Contracts\Eventable;
use Adultdate\FilamentBooking\Enums\Priority;
use Adultdate\FilamentBooking\ValueObjects\CalendarEvent;
use Database\Factories\SprintFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingSprint extends Model implements Eventable
{
    /** @use HasFactory<SprintFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'priority',
        'starts_at',
        'ends_at',
    ];

    public function toCalendarEvent(): CalendarEvent
    {
        return CalendarEvent::make($this)
            ->title($this->title)
            ->start($this->starts_at)
            ->end($this->ends_at)
            ->extendedProps([
                'title' => $this->title,
                'priority' => $this->priority->getLabel(),
            ]);
    }

    protected function casts(): array
    {
        return [
            'priority' => Priority::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
