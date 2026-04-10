<?php

declare(strict_types=1);

namespace Cachet\Filament\Pages;

use Cachet\Filament\Widgets\StatusOverviewWidget;
use Filament\Pages\Page;

class StatusOverview extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'cachet-component-performance-issues';

    protected static ?string $slug = 'status-overview';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'cachet::filament.pages.status-overview';

    protected static ?string $title = '';

    protected function getHeaderWidgets(): array
    {
        return [
            StatusOverviewWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    public static function getNavigationLabel(): string
    {
        return __('Overview');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('cachet::navigation.resources.label');
    }

    public function getTitle(): string
    {
        return __('');
    }
}
