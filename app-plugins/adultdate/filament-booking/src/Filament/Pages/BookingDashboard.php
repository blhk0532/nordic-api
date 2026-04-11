<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Pages;

use Adultdate\FilamentBooking\Filament\Pages\Dashboard as BaseAppDashboard;
use BackedEnum;
use Closure;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BookingDashboard extends BaseAppDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static ?string $navigationLabel = 'Dash';

    protected static ?string $title = '';

    protected static ?string $slug = 'dashboard';

    protected string $view = 'adultdate/filament-booking::pages.page';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getNavigationLabel(): string
    {
        return ''.Str::ucfirst(Auth::user()->name) ?? 'User';
    }

    public static function getNavigationBadge(): ?string
    {
        //  return now()->format('H:m');
        return 'APP';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public function getView(): string
    {
        return 'adultdate/filament-booking::pages.page';
    }

    public function getPermissionCheckClosure(): Closure
    {
        return fn (string $widgetClass) => true;
    }
}
