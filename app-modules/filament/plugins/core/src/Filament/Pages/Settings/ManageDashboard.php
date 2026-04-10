<?php

namespace Cachet\Filament\Pages\Settings;

use Cachet\Settings\AppSettings;

use function __;

class ManageDashboard extends SettingsPage
{
    protected static string $settings = AppSettings::class;

    protected static ?string $title = '';

    protected static bool $isDiscovered = false;

    public static function getNavigationGroup(): ?string
    {
        return __('cachet::navigation.settings.label');
    }

    public static function getNavigationLabel(): string
    {
        return __('Dashboard');
    }
}
