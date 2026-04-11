<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models\Booking;

use Database\Factories\Booking\OrderAddressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrderAddress extends Model
{
    /** @use HasFactory<OrderAddressFactory> */
    use HasFactory;

    protected $table = 'booking_order_addresses';

    protected $fillable = [
        'country',
        'street',
        'city',
        'state',
        'zip',
    ];

    /** @return MorphTo<Model, $this> */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }
}
