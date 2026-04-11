<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models\Booking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingSchedule extends Model
{
    use HasFactory;

    protected $table = 'booking_schedules';

    protected $fillable = [
        'booking_location_id',
        'date',
        'start_time',
        'end_time',
        'is_available',
        'max_bookings',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean',
        'max_bookings' => 'integer',
    ];

    /** @return BelongsTo<BookingLocation, $this> */
    public function location(): BelongsTo
    {
        return $this->belongsTo(BookingLocation::class, 'booking_location_id');
    }

    /**
     * Get the number of bookings for this schedule
     */
    public function getBookingsCount(): int
    {
        return Booking::where('booking_location_id', $this->booking_location_id)
            ->where('service_date', $this->date)
            ->count();
    }

    /**
     * Check if the schedule has available slots
     */
    public function hasAvailableSlots(): bool
    {
        return $this->is_available && $this->getBookingsCount() < $this->max_bookings;
    }
}
