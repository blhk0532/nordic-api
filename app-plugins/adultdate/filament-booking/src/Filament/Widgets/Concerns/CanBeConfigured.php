<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Widgets\Concerns;

use Adultdate\FilamentBooking\FilamentBookingPlugin;

trait CanBeConfigured
{
    public function config(): array
    {
        return [];
    }

    public function getConfig(): array
    {
        return $this->array_merge_recursive_unique(
            FilamentBookingPlugin::get()->getConfig(),
            $this->config()
        );
    }

    public function getOptions(): array
    {
        return $this->getConfig();
    }

    private function array_merge_recursive_unique(array $array1, array $array2): array
    {
        $merged = [];

        foreach ([$array1, $array2] as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                    $merged[$key] = $this->array_merge_recursive_unique($merged[$key], $value);
                } elseif (is_array($value)) {
                    $merged[$key] = $value;
                } else {
                    $merged[$key] = $value;
                }
            }
        }

        // Apply uniqueness to non-array values if needed, but keep arrays as is
        foreach ($merged as $key => $value) {
            if (! is_array($value)) {
                // For simplicity, assume no duplicates in scalars; extend if needed
            }
        }

        return $merged;
    }
}
