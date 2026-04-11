<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\AuthRole;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class DisPhone extends Page
{
    protected string $view = 'filament.app.pages.disphone';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationLabel = 'Mentor SIP';

    protected static ?string $title = '';

    protected static ?int $navigationSort = 5;

    protected static UnitEnum|string|null $navigationGroup = 'Dialers TELE';

    protected static ?string $slug = 'mentor-sip';

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
        $role = Auth::user()->role;

        // Check if role is enum
        if ($role instanceof AuthRole) {
            return in_array($role, [AuthRole::Admin, AuthRole::Super, AuthRole::Manager], true);
        }

        // Role is string - normalize legacy values
        $normalizedRole = match ($role) {
            'super_admin', 'superadmin' => 'super',
            default => $role,
        };

        return in_array($normalizedRole, ['admin', 'super', 'manager'], true);
    }
}
