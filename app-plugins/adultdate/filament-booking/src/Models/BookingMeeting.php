<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models;

use Adultdate\FilamentBooking\Contracts\Eventable;
use Adultdate\FilamentBooking\ValueObjects\CalendarEvent;
use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BookingMeeting extends Model implements Eventable
{
    /** @use HasFactory<MeetingFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'starts_at',
        'ends_at',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function toCalendarEvent(): CalendarEvent
    {
        $participants = $this->users_count ?? $this->users()->count();

        return CalendarEvent::make($this)
            ->title($this->title)
            ->start($this->starts_at)
            ->end($this->ends_at)
            ->durationEditable(false)
            ->extendedProps([
                'title' => $this->title,
                'participants' => $participants,
            ]);
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
