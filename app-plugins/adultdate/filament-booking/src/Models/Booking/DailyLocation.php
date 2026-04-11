<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models\Booking;

use Adultdate\FilamentBooking\Contracts\Eventable;
use Adultdate\FilamentBooking\ValueObjects\CalendarEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder query()
 */
class DailyLocation extends Model implements Eventable
{
    protected $table = 'booking_daily_locations';

    protected $fillable = [
        'date',
        'service_date',
        'service_user_id',
        'location',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'service_user_id',
        'service_date',
        'location',
        'created_by',
        'id',
    ];

    public function serviceUser()
    {
        return $this->belongsTo(User::class, 'service_user_id')
            ->where('role', 'service');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function setServiceDateAttribute($value)
    {
        $this->attributes['date'] = $value;
    }

    public function getServiceDateAttribute()
    {
        return $this->attributes['date'] ?? null;
    }

    public function toCalendarEvent(): CalendarEvent
    {
        $title = $this->location ?: ($this->serviceUser?->name ?? 'Location');

        return CalendarEvent::make($this)
            ->title($title)
            ->start($this->date)
            ->allDay(true)
            ->backgroundColor('#ffffff')
            ->borderColor('#e5e7eb')
            ->textColor('#111827')
            ->extendedProps([
                'id' => $this->id,
                'is_location' => true,
                'type' => 'location',
                'daily_location_id' => $this->id,
                'service_user_id' => $this->service_user_id,
                'location' => $this->location,
                'serviceUser' => $this->serviceUser?->name,
                'displayLocation' => $this->location ?: ($this->serviceUser?->name ?? 'Location'),
            ]);
    }

    /**
     * Return the stored location value.
     */
    public function getLocation(): ?string
    {
        $title = $this->location ?: ($this->serviceUser?->name ?? 'Location');

        return $title;
    }
}
