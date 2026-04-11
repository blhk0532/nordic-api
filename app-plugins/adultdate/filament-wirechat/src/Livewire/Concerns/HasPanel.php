<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Livewire\Concerns;

use Adultdate\Wirechat\Exceptions\NoPanelProvidedException;
use Adultdate\Wirechat\Facades\Wirechat;
use Adultdate\Wirechat\Panel;
use Adultdate\Wirechat\PanelRegistry;
use Livewire\Attributes\Computed;

trait HasPanel
{
    public Panel|string|null $panel = null;

    /**
     * Resolve and assign the panel during mount.
     */
    public function mountHasPanel(): void
    {
        // If already set by Livewire/public property, use it
        $this->initializePanel($this->panel);
    }

    /**
     * Initialize the panel manually (can be called anywhere).
     *
     * @throws NoPanelProvidedException
     */
    public function initializePanel(Panel|string|null $panelId = null): void
    {
        if ($panelId instanceof Panel) {
            $this->panel = $panelId->getId();
        } elseif (is_string($panelId) && filled($panelId)) {
            $this->panel = $panelId;
        } else {
            $this->panel = Wirechat::getDefaultPanel()?->getId();
        }

        if (! $this->panel || ! Wirechat::getPanel($this->panel)) {
            throw NoPanelProvidedException::make();
        }

        app(PanelRegistry::class)->setCurrent($this->panel);
    }

    #[Computed(cache: false)]
    public function panel(): ?Panel
    {
        return $this->panel ? Wirechat::getPanel($this->panel) : null;
    }
}
