<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Pages;

use Adultdate\FilamentBooking\Filament\Widgets\AccountWidget;
use Adultdate\FilamentBooking\Filament\Widgets\FilamentInfosWidget;
use Adultdate\FilamentBooking\Filament\Widgets\StatsOverviewWidget;
use Closure;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;
use Shreejan\DashArrange\Traits\HasDashArrange;

class Dashboard extends BaseDashboard
{
    use HasDashArrange;

    protected string $view = 'dash-arrange::dashboard';

    public function mount(): void
    {
        // Initialize DashArrange functionality
        $this->mountHasDashArrange();
    }

    public function getTitle(): string|Htmlable
    {
        return '';
    }

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            AccountWidget::class,
            FilamentInfosWidget::class,
        ];
    }

    public function getPermissionCheckClosure(): Closure
    {
        return fn (string $widgetClass) => true;
    }
}
