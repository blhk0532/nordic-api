<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\GeoMapWidget;
use App\Filament\Widgets\LocationMapPickerWidgetFull;
use App\Filament\Widgets\MapPinsTableWidget;
use App\Models\MapPin;
use BackedEnum;
use Filament\Pages\Page as BasePage;
use Filament\Support\Enums\Width;
use UnitEnum;
use Wallacemartinss\FilamentIconPicker\Enums\BootstrapIcons;
use Wallacemartinss\FilamentIconPicker\Enums\Remix;
use Wallacemartinss\FilamentIconPicker\Enums\Tabler;

class KartorPins extends BasePage
{
    // protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = ' ';

    protected static ?string $slug = 'kartor-pins';

    // protected static string|BackedEnum|null $navigationIcon = Tabler::CalendarMonthF;
    //   protected static string|BackedEnum|null $navigationIcon = BootstrapIcons::PersonCheck;
    //   protected static string|BackedEnum|null $activeNavigationIcon = BootstrapIcons::PersonFillCheck;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected static string|BackedEnum|null $activeNavigationIcon = 'heroicon-s-map';

    protected static UnitEnum|string|null $navigationGroup = 'Sverige MAP';

    //   protected static string|BackedEnum|null $navigationIcon = Remix::RiCalendarScheduleLine;
    //   protected static string|BackedEnum|null $activeNavigationIcon = Remix::RiCalendarScheduleFill;

    //  protected static string|UnitEnum|null $navigationGroup = ' ';
    protected static ?int $navigationSort = -20;

    public static function getNavigationBadge(): ?string
    {
        return (string) MapPin::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'gray';
    }

    public static function getNavigationLabel(): string
    {
        return 'Kartor PINS';
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
            LocationMapPickerWidgetFull::class,
            GeoMapWidget::class,
            //    MultiCalendar1::class,
            //    MultiCalendar2::class,
            //    MultiCalendar3::class,
        ];
    }

    public function getWidgets(): array
    {
        return [

        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            MapPinsTableWidget::class,
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
