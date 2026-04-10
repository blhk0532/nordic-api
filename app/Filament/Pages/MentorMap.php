<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MentorMap extends Page
{
    protected string $view = 'filament.app.pages.mentor-map';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Mentor MAP';

    protected static ?string $title = '';

    protected static ?int $navigationSort = 5;

    protected static UnitEnum|string|null $navigationGroup = 'Sverige MAP';

    protected static ?string $slug = 'mentor-map';

    // protected static string|UnitEnum|null $navigationGroup = '';

    public static function getNavigationBadge(): ?string
    {
        return (string) '250';
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
