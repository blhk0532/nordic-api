<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BookingStatus: string implements HasColor, HasIcon, HasLabel
{
    case Booked = 'booked';
    case Pending = 'processing';
    case Confirmed = 'confirmed';
    case Updated = 'updated';
    case Cancelled = 'cancelled';
    case Complete = 'complete';

    public static function toOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->getLabel()])
            ->all();
    }

    public static function restrictedOptions(): array
    {
        return [
            self::Booked->value => self::Booked->getLabel(),
            self::Confirmed->value => self::Confirmed->getLabel(),
            self::Cancelled->value => self::Cancelled->getLabel(),
            self::Complete->value => self::Complete->getLabel(),
        ];
    }

    public function getLabel(): ?string
    {
        return match ($this) {

            self::Booked => 'Booked',
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Updated => 'Updated',
            self::Cancelled => 'Cancelled',
            self::Complete => 'Complete',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Booked => 'gray',
            self::Pending => 'gray',
            self::Confirmed => 'warning',
            self::Updated => 'info',
            self::Cancelled => 'danger',
            self::Complete => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Booked => 'heroicon-o-calendar',
            self::Pending => 'heroicon-o-clock',
            self::Confirmed => 'heroicon-o-check-circle',
            self::Updated => 'heroicon-o-pencil-square',
            self::Cancelled => 'heroicon-o-x-circle',
            self::Complete => 'heroicon-o-check-badge',
        };
    }

    public function getCalendarColor(): string
    {
        return match ($this) {
            self::Booked => '#6366f1',
            self::Pending => '#6b7280',
            self::Confirmed => '#f59e0b',
            self::Updated => '#06b6d4',
            self::Cancelled => '#ef4444',
            self::Complete => '#22c55e',
        };
    }
}
