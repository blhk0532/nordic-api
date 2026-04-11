<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Panels\App\Pages;

use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = '';

    // Prevent this app-level Dashboard from being auto-discovered so that
    // the explicit `AdminDashboard` can be registered as the admin panel root.
    protected static bool $isDiscovered = true;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-pie';

    //  public function filtersForm(Schema $schema): Schema
    //  {
    //      return $schema
    //          ->components([
    //              Section::make()
    //                  ->schema([
    //                      Select::make('Business')
    //                          ->boolean(),
    //                      Select::make('Persons')
    //                          ->boolean(),
    //                      DatePicker::make('startDate')
    //                          ->maxDate(fn (Get $get) => $get('endDate') ?: now()),
    //                      DatePicker::make('endDate')
    //                          ->minDate(fn (Get $get) => $get('startDate') ?: now())
    //                          ->maxDate(now()),
    //                  ])
    //                  ->columns(4)
    //                  ->columnSpanFull(),
    //          ]);
    //  }

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
}
