<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper;

use CybertronianKelvin\Graper\Resources\GraperPageResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class GraperPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'graper';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                GraperPageResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
