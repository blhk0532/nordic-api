<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Panel\Concerns;

use Closure;

trait HasHeart
{
    /**
     * Enable or disable option
     */
    protected bool|Closure $hasHeart = false;

    public function heart(bool|Closure $condition = true): static
    {
        $this->hasHeart = $condition;

        return $this;
    }

    public function hasHeart(): bool
    {
        return (bool) $this->evaluate($this->hasHeart);

    }
}
