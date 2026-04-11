<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Panel\Concerns;

use Closure;

trait HasActions
{
    protected bool|Closure $redirectToHomeAction = false;

    protected string|Closure|null $homeButtonUrl = null;

    public function redirectToHomeAction(bool|Closure $condition = true): static
    {
        $this->redirectToHomeAction = $condition;

        return $this;
    }

    public function hasRedirectToHomeAction(): bool
    {
        return (bool) $this->evaluate($this->redirectToHomeAction);
    }

    /**
     * Set the URL for the home button redirect.
     *
     * @param  string|Closure|null  $url  The URL, route name, 'default' for default Filament panel, or null to use config
     */
    public function homeButtonUrl(string|Closure|null $url): static
    {
        $this->homeButtonUrl = $url;

        return $this;
    }

    /**
     * Get the evaluated home button URL.
     */
    public function getHomeButtonUrl(): ?string
    {
        return $this->evaluate($this->homeButtonUrl);
    }
}
