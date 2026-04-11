<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class DisPhone extends Page
{
    protected string $view = 'filament.app.pages.disphone';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationLabel = 'DisPhone SIP';

    protected static ?string $title = '';

    protected static ?int $navigationSort = 5;

    protected static UnitEnum|string|null $navigationGroup = 'Dialers TELE';

    protected static ?string $slug = 'disphone';

    // protected static string|UnitEnum|null $navigationGroup = '';

    public static function getNavigationBadge(): ?string
    {
        return (string) 'Idle';
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'gray';
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public static function shouldRegisterNavigation(): bool
    {
        // if (filament()->getTenant()->getAttribute('is_admin') !== true) {
        //     return false;
        // }
        if (Auth::user()->role === 'admin' || Auth::user()->role === 'super' || Auth::user()->role === 'manager') {
            return true;
        }

        return true;
    }
}
