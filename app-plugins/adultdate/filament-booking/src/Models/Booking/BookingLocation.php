<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models\Booking;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingLocation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'booking_locations';

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'postal_code',
        'country',
        'phone',
        'email',
        'description',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    /** @return HasMany<BookingSchedule, $this> */
    public function schedules(): HasMany
    {
        return $this->hasMany(BookingSchedule::class, 'booking_location_id');
    }

    /** @return HasMany<Booking, $this> */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'booking_location_id');
    }
}
