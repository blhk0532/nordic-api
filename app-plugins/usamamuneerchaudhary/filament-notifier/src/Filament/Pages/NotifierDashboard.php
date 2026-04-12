<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Pages;

use Filament\Pages\Page;
use Usamamuneerchaudhary\Notifier\Filament\Widgets\NotificationAnalyticsChart;
use Usamamuneerchaudhary\Notifier\Filament\Widgets\NotificationChannelPerformance;
use Usamamuneerchaudhary\Notifier\Filament\Widgets\NotificationEngagementStats;
use Usamamuneerchaudhary\Notifier\Filament\Widgets\NotificationStatsOverview;
use Usamamuneerchaudhary\Notifier\Filament\Widgets\NotificationTimeSeriesChart;
use Usamamuneerchaudhary\Notifier\Filament\Widgets\RateLimitingStatusWidget;
use Illuminate\Contracts\Support\Htmlable;

class NotifierDashboard extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|null|\UnitEnum $navigationGroup = 'Notifications';

    protected static ?string $title = 'Notifier Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 0;

    protected string $view = 'notifier::pages.dashboard';

        public function getHeading(): string|Htmlable|null
{
    return null;
}

    protected function getWidgets(): array
    {
        return [
            NotificationStatsOverview::class,
            NotificationEngagementStats::class,
            NotificationTimeSeriesChart::class,
            NotificationAnalyticsChart::class,
            NotificationChannelPerformance::class,
            RateLimitingStatusWidget::class,
        ];
    }
}
