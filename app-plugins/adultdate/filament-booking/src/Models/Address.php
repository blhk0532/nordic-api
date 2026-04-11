<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Models;

use Adultdate\FilamentBooking\Models\Booking\Brand;
use Adultdate\FilamentBooking\Models\Booking\Customer;
use Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Address extends Model
{
    /** @use HasFactory<AddressFactory> */
    use HasFactory;

    protected $table = 'booking_addresses';

    /** @return MorphToMany<Customer, $this> */
    public function customers(): MorphToMany
    {
        return $this->morphedByMany(Customer::class, 'booking_addressable');
    }

    /** @return MorphToMany<Brand, $this> */
    public function brands(): MorphToMany
    {
        return $this->morphedByMany(Brand::class, 'booking_addressable');
    }
}
