<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Traits;

use Adultdate\Wirechat\Exceptions\NoPanelProvidedException;
use Adultdate\Wirechat\Facades\Wirechat;
use Adultdate\Wirechat\Panel;
use Exception;

trait InteractsWithPanel
{
    public ?string $panel;

    /**
     * Set the panel from provided value or default.
     *
     * @throws NoPanelProvidedException
     * @throws Exception
     */
    public function resolvePanel(?string $panel = null): void
    {
        if (is_string($panel) && filled($panel)) {
            $this->panel = Wirechat::getPanel($panel)->getId();
        } else {
            $this->panel = Wirechat::getDefaultPanel()->getId();
        }

        if (! $this->panel) {
            throw NoPanelProvidedException::make();
        }
    }

    /**
     * Get the resolved panel instance.
     *
     * @return Panel|\Filament\Panel|null
     */
    public function getPanel()
    {
        return Wirechat::getPanel($this->panel);
    }
}
