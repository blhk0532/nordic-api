<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    protected $fillable = [
        'title',
        'start',
        'end',
        'all_day',
        'background_color',
        'description',
        'user_id',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'all_day' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toCalendarObject(int $timezoneOffset = 0, bool $useFilamentTimezone = false): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $useFilamentTimezone
                ? $this->start?->setTimezone(config('app.timezone', 'UTC'))->toIso8601String()
                : $this->start?->utcOffset($timezoneOffset)->toIso8601String(),
            'end' => $useFilamentTimezone
                ? $this->end?->setTimezone(config('app.timezone', 'UTC'))->toIso8601String()
                : $this->end?->utcOffset($timezoneOffset)->toIso8601String(),
            'allDay' => $this->all_day,
            'backgroundColor' => $this->background_color,
            'extendedProps' => [
                'description' => $this->description,
                'user_id' => $this->user_id,
            ],
        ];
    }

    /**
     * Return header widgets for the page so Filament will render them
     * in the page header area (the framework filters by canView()).
     *
     * @return array<class-string<Widget>>
     */
}
