<?php

declare(strict_types=1);

namespace Adultdate\Wirechat;

use Illuminate\Support\ServiceProvider;

abstract class PanelProvider extends ServiceProvider
{
    abstract public function panel(Panel $panel): Panel;

    final public function register(): void
    {
        $panel = $this->panel(Panel::make());
        app(PanelRegistry::class)->register($panel);
    }
}
