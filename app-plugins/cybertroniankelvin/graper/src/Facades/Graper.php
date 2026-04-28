<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CybertronianKelvin\Graper\Graper
 */
class Graper extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CybertronianKelvin\Graper\Graper::class;
    }
}
