<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingOutcallQueue extends Model
{
    use HasFactory;

    protected $table = 'booking_outcall_queues';

    protected $fillable = [
        'luid',
        'name',
        'address',
        'street',
        'city',
        'maps',
        'age',
        'sex',
        'dob',
        'phone',
        'status',
        'type',
        'notes',
        'result',
        'attempts',
        'user_id',
        'service_user_id',
        'booking_user_id',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'dob' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
        'attempts' => 'integer',
        'age' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function serviceUser()
    {
        return $this->belongsTo(User::class, 'service_user_id');
    }

    public function bookingUser()
    {
        return $this->belongsTo(User::class, 'booking_user_id');
    }
}
