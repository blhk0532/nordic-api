<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class AccountWidget extends Widget
{
    protected static ?int $sort = -3;

    protected static bool $isLazy = false;

    /**
     * @var view-string
     */
    protected string $view = 'filament-panels::widgets.account-widget';

    public static function canView(): bool
    {
        return Filament::auth()->check();
    }
}
