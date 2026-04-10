<?php

declare(strict_types=1);

namespace Cachet\Filament\Pages;

use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = '';

    protected Width|string|null $maxContentWidth = Width::Full;

    public static function getNavigationBadge(): ?string
    {
        return Auth::user()->name;
    }
}
