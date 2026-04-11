<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Widgets;

use Filament\Widgets\Widget;

/**
 * Wirechat Widget
 *
 * A Filament widget that displays the Wirechat messaging interface.
 * This widget allows users to view and interact with their conversations
 * directly from any Filament dashboard or page.
 */
class WirechatWidget extends Widget
{
    protected string $view = 'filament-wirechat::filament.widgets.wirechat-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected ?string $heading = null;

    protected static bool $isLazy = false;

    protected static bool $isDiscovered = false;

    public function getExtraAttributes(): array
    {
        return [
            'class' => '-mt-6',
        ];
    }
}
