<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ExternalAppWidget extends Widget
{
    protected string $view = 'filament.widgets.external-app-widget';

    protected int|string|array $columnSpan = 3;

    public string $iframeUrl = 'https://disphone-mentor.vercel.app/app';

    public string $iframeHeight = '800px';

    public function getHeading(): ?string
    {
        return null;
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
