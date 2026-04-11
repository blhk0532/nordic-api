<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Widgets;

use Adultdate\FilamentBooking\Concerns\HasHeaderActions;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\CanBeConfigured;
use Adultdate\FilamentBooking\Filament\Widgets\Concerns\InteractsWithRawJS;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions; // Use fully-qualified class to avoid static analysis issues with the facade import.
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Facades\FilamentAsset;
use Filament\Widgets\Widget;

abstract class SimpleCalendarWidget extends Widget implements HasActions, HasForms
{
    use CanBeConfigured, HasHeaderActions, InteractsWithRawJS;
    use InteractsWithActions;
    use InteractsWithForms;

    // protected string $view = 'adultdate/filament-booking::service-periods-fullcalendar';
    protected string $view = 'adultdate/filament-booking::calendar-widget';

    //    protected int | string | array $columnSpan = 'full';

    final public function eventAssetUrl(): string
    {
        return FilamentAsset::getAlpineComponentSrc('calendar-event', 'adultdate/filament-booking');
    }
}
