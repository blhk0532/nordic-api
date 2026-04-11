<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

trait HasOptions
{
    protected array $options = [];

    /**
     * Allows you to set vkurko/calendar options directly in case a helper method was not provided by the package.
     */
    public function getOptions(): array
    {
        return array_merge($this->getConfig() ?? [], $this->options);
    }

    public function setOption(string $key, mixed $value): static
    {
        $this->dispatch('calendar--set', key: $key, value: $value);

        return $this;
    }
}
