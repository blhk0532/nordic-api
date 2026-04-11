<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Enums;

use Filament\Support\Contracts\HasLabel;

enum Priority: string implements HasLabel
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Low => 'Low priority',
            self::Medium => 'Medium priority',
            self::High => 'High priority',
            self::Urgent => 'Urgent',
        };
    }
}
