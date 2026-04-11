<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Services;

use Filament\Facades\Filament;
use Filament\Support\Colors\Color;

class ThemeService
{
    /**
     * Render the theme CSS variables as a style tag.
     * Uses exact OKLCH values from original wirechat to match preview image.
     * Allows Filament panel colors and config overrides.
     */
    public function renderStyles(): string
    {
        $config = config('filament-wirechat.theme', []);

        // Get Filament panel colors (if available)
        $panel = Filament::getCurrentPanel();
        $panelColors = $panel?->getColors() ?? [];

        // Get primary color from Filament panel or config
        $brandPrimary = $this->getBrandPrimary($panelColors, $config);

        // Use exact OKLCH values from original wirechat implementation to match preview
        // These match the original wirechat documentation and preview image
        $lightPrimary = $config['light_primary'] ?? '#fff'; // white
        $lightSecondary = $config['light_secondary'] ?? 'oklch(0.967 0.001 286.375)'; // zinc-100
        $lightAccent = $config['light_accent'] ?? 'oklch(0.985 0 0)'; // zinc-50
        $lightBorder = $config['light_border'] ?? 'oklch(0.92 0.004 286.32)'; // zinc-200

        // Dark mode - exact values from original wirechat (zinc palette)
        $darkPrimary = $config['dark_primary'] ?? 'oklch(0.21 0.006 285.885)'; // zinc-900
        $darkSecondary = $config['dark_secondary'] ?? 'oklch(0.274 0.006 286.033)'; // zinc-800
        $darkAccent = $config['dark_accent'] ?? 'oklch(0.37 0.013 285.805)'; // zinc-700
        $darkBorder = $config['dark_border'] ?? 'oklch(0.37 0.013 285.805)'; // zinc-700

        // Build CSS variables
        $css = ":root {\n";

        // Brand primary
        $css .= "    --wc-brand-primary: {$brandPrimary};\n";

        // Light mode
        $css .= "    --wc-light-primary: {$lightPrimary};\n";
        $css .= "    --wc-light-secondary: {$lightSecondary};\n";
        $css .= "    --wc-light-accent: {$lightAccent};\n";
        $css .= "    --wc-light-border: {$lightBorder};\n";

        $css .= "}\n\n";

        $css .= ".dark {\n";

        // Dark mode
        $css .= "    --wc-dark-primary: {$darkPrimary};\n";
        $css .= "    --wc-dark-secondary: {$darkSecondary};\n";
        $css .= "    --wc-dark-accent: {$darkAccent};\n";
        $css .= "    --wc-dark-border: {$darkBorder};\n";
        $css .= "    --wc-brand-primary: {$brandPrimary};\n";

        $css .= "}\n";

        return "<style>{$css}</style>";
    }

    /**
     * Get the brand primary color from Filament panel or config.
     */
    private function getBrandPrimary(array $panelColors, array $config): string
    {
        // Use config override if provided
        if (! empty($config['brand_primary'])) {
            return $config['brand_primary'];
        }

        // Try to get from Filament panel
        if (! empty($panelColors['primary'])) {
            $primary = $panelColors['primary'];

            // If it's an array (Color constant), get the 500 shade
            if (is_array($primary) && isset($primary[500])) {
                return $primary[500];
            }

            // If it's a string, use it directly
            if (is_string($primary)) {
                return $primary;
            }
        }

        // Default fallback to Blue-500 (OKLCH format matching original wirechat)
        return 'oklch(0.623 0.214 259.815)'; // Blue-500 from original wirechat
    }
}
