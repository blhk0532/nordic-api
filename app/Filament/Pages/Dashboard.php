<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\DatabaseBackupWidget;
use App\Filament\Widgets\GeoMapWidget;
use App\Filament\Widgets\LocationMapPickerWidgetFull;
use App\Filament\Widgets\MapPinsTableWidget;
use Awcodes\Overlook\Widgets\OverlookWidget;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use Wallacemartinss\FilamentIconPicker\Enums\BootstrapIcons;
use Wallacemartinss\FilamentIconPicker\Enums\Remix;
use Wallacemartinss\FilamentIconPicker\Enums\Tabler;

class Dashboard extends BaseDashboard
{
    // protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = ' ';

    protected static ?string $slug = 'dashboard';

    // protected static string|BackedEnum|null $navigationIcon = Tabler::CalendarMonthF;
    //   protected static string|BackedEnum|null $navigationIcon = BootstrapIcons::PersonCheck;
    //   protected static string|BackedEnum|null $activeNavigationIcon = BootstrapIcons::PersonFillCheck;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static string|BackedEnum|null $activeNavigationIcon = 'heroicon-s-user-circle';

    protected static bool $shouldRegisterNavigation = false;

    // protected static UnitEnum|string|null $navigationGroup = 'Dashboard';

    //   protected static UnitEnum|string|null $navigationGroup = 'Kartor MAPS';

    //   protected static string|BackedEnum|null $navigationIcon = Remix::RiCalendarScheduleLine;
    //   protected static string|BackedEnum|null $activeNavigationIcon = Remix::RiCalendarScheduleFill;

    protected static string|UnitEnum|null $navigationGroup = 'Dashboard';

    protected static ?int $navigationSort = -20;

    public static function getNavigationLabel(): string
    {
        return __('Dashboard');
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function getNavigationBadge(): ?string
    {
        return Auth::user()->name;
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm' => 1,
            'md' => 1,
            'lg' => 1,
            'xl' => 1,
            '2xl' => 1,
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            // AccountInfoStackWidget::class,
            // WorldClockWidget::class,

            //    AccountWidget::class,
            //    FilamentInfosWidget::class,
            //    StatsOverviewWidget::class,

            //    \App\Filament\App\Widgets\LatestOrders::class,
            //    \App\Filament\App\Widgets\StatsOverviewWidget::class,

            //    MultiCalendar1::class,
            //    MultiCalendar2::class,
            //    MultiCalendar3::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            //  DatabaseBackupWidget::class,
            //    LocationMapPickerWidgetFull::class,
            // GeoMapWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            //    MapPinsTableWidget::class,
            OverlookWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    public function getWidgetsColumns(): int|array
    {
        return 1;
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 1;
    }
}
