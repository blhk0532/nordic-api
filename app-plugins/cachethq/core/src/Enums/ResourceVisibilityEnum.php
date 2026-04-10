<?php

namespace Cachet\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ResourceVisibilityEnum: int implements HasColor, HasIcon, HasLabel
{
    case team = 0;
    case admin = 1;
    case authenticated = 2;
    case guest = 3;
    case hidden = 4;

    public static function visibleToGuests(): array
    {
        return [self::guest];
    }

    public static function visibleToUsers(): array
    {
        return [self::authenticated, self::guest];
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::team => 'heroicon-o-user-group',
            self::admin => 'heroicon-o-shield-check',
            self::authenticated => 'heroicon-o-lock-closed',
            self::guest => 'heroicon-o-eye',
            self::hidden => 'heroicon-o-eye-slash',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::team => __('Team'),
            self::admin => __('Admin'),
            self::authenticated => __('Inloggad'),
            self::guest => __('Alla'),
            self::hidden => __('Ingen'),

        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::team => 'gray',
            self::admin => 'gray',
            self::authenticated => 'gray',
            self::guest => 'gray',
            self::hidden => 'danger',

        };
    }
}
