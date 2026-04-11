<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Services;

class ColorService
{
    // Base colors registered globally (not tied to any panel)
    private array $colors = [];

    /**
     * Register global default colors.
     */
    public function register(array $map): void
    {
        foreach ($map as $name => $palette) {
            if (is_array($palette)) {
                $this->colors[$name] = $palette;
            }
        }
    }

    /**
     * Get a single color by name + shade (default: 500).
     */
    public function get(string $name, int $shade = 500): ?string
    {
        $colors = $this->all();

        $palette = $colors[$name] ?? null;

        if (! $palette) {
            return null;
        }

        return $palette[$shade] ?? ($palette[500] ?? null);
    }

    /**
     * Get the full palette for a single color.
     */
    public function palette(string $name): ?array
    {
        return $this->all()[$name] ?? null;
    }

    /**
     * Return all available colors:
     * - colors (global)
     * - merged with current panel overrides (if applicable)
     */
    public function all(): array
    {
        // For Filament, we'll use the configured colors
        // Panel-specific colors can be added later if needed
        return $this->colors;
    }

    // === Convenience shortcuts for common colors ===

    /** Get the "primary" color. */
    public function primary(int $shade = 500): ?string
    {
        return $this->get('primary', $shade);
    }

    /** Get the "danger" color. */
    public function danger(int $shade = 500): ?string
    {
        return $this->get('danger', $shade);
    }

    /** Get the "warning" color. */
    public function warning(int $shade = 500): ?string
    {
        return $this->get('warning', $shade);
    }
}
