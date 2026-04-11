<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Enums;

use Filament\Support\Colors\Color;
use ReflectionClass;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class BookingState extends State
{
    abstract public function color(): string;

    /**
     * Compatibility: provide an options array similar to PHP enums.
     * Keys are the state class names and values are labels.
     */
    final public static function toOptions(): array
    {
        return [
            Pending::class => 'Pending',
            Paid::class => 'Paid',
            Failed::class => 'Failed',
        ];
    }

    final public static function config(): StateConfig
    {
        return parent::config()
            ->registerState([
                Pending::class,
                Paid::class,
                Failed::class,
                Booked::class,
            ])
            ->default(Pending::class)
            ->allowTransition(Pending::class, Paid::class)
            ->allowTransition(Pending::class, Failed::class)
            ->allowTransition(Booked::class, Paid::class)
            ->allowTransition(Booked::class, Failed::class);
    }

    /**
     * Compatibility: return a Filament-friendly color name.
     */
    final public function getColor(): string
    {
        return $this->color();
    }

    /**
     * Compatibility: return a human label for the state (used by form options).
     */
    final public function getLabel(): string
    {
        return (new ReflectionClass($this))->getShortName();
    }
}

class Pending extends BookingState
{
    public function color(): string
    {
        return 'warning';
    }
}

class Booked extends BookingState
{
    public function color(): string
    {
        return 'primary';
    }
}

class Paid extends BookingState
{
    public function color(): string
    {
        return 'success';
    }
}

class Failed extends BookingState
{
    public function color(): string
    {
        return 'danger';
    }
}
