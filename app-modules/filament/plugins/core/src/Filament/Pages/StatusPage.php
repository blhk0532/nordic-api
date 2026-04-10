<?php

declare(strict_types=1);

namespace Cachet\Filament\Pages;

use Cachet\Filament\Widgets\StatusAboutWidget;
use Cachet\Filament\Widgets\StatusAnnouncementWidget;
use Cachet\Filament\Widgets\StatusBarWidget;
use Cachet\Filament\Widgets\StatusComponentsWidget;
use Cachet\Filament\Widgets\StatusGroupsWidget;
use Cachet\Filament\Widgets\StatusScheduleWidget;
use Cachet\Filament\Widgets\StatusTimelineWidget;
use Filament\Pages\Page;

class StatusPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'cachet-component-performance-issues';

    protected static ?string $slug = 'status-page';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'cachet::filament.pages.status';

    protected static ?string $title = '';

    protected function getHeaderWidgets(): array
    {
        return [
            StatusBarWidget::class,
            StatusAnnouncementWidget::class,
            StatusAboutWidget::class,
            StatusGroupsWidget::class,
            StatusComponentsWidget::class,
            StatusScheduleWidget::class,
            StatusTimelineWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    public static function getNavigationLabel(): string
    {
        return __('Status Page');
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
