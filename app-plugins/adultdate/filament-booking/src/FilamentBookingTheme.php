<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Theme;
use Filament\Support\Colors;
use Filament\Support\Facades\FilamentAsset;

class FilamentBooking implements Plugin
{
    public function getId(): string
    {
        return 'filament-booking';
    }

    public function register(Panel $panel): void
    {
        FilamentAsset::register([
            Theme::make('filament-booking', __DIR__.'/../resources/dist/filament-booking.css'),
        ]);

        $panel
            ->font('DM Sans')
            ->primaryColor(Colors\Color::Amber)
            ->secondaryColor(Colors\Color::Gray)
            ->warningColor(Colors\Color::Amber)
            ->dangerColor(Colors\Color::Rose)
            ->successColor(Colors\Color::Green)
            ->grayColor(Colors\Color::Gray)
            ->theme('filament-booking');
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
