<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Adultdate\FilamentBooking\FilamentBooking
 */
class FilamentBooking extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Adultdate\FilamentBooking\FilamentBooking::class;
    }
}
