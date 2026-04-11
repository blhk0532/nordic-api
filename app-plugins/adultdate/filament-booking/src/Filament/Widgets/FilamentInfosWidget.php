<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Widgets;

use Filament\Widgets\Widget;

class FilamentInfosWidget extends Widget
{
    protected static ?int $sort = -1;

    protected static bool $isLazy = false;

    /**
     * @var view-string
     */
    protected string $view = 'adultdate/filament-booking::widgets.filament-info-widget';
}
