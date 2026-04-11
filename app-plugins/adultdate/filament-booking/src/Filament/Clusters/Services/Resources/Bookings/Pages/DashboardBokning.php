<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Pages;

use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Widgets\MultiCalendar1;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Widgets\MultiCalendar2;
use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Widgets\MultiCalendar3;
use App\Models\BookingCalendar as BookingCalendarModel;
use BackedEnum;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class DashboardBokning extends BaseDashboard
{
    use HasFiltersForm;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDateRange;

    //    protected static ?string $navigationLabel = 'Dash';

    protected static ?string $title = '';

    protected static string $routePath = 'service/bokning';

    //  protected static ?string $slug = 'dashboard';

    protected string $view = 'filament-booking::pages.page';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getNavigationLabel(): string
    {
        return ''.Str::ucfirst('Kalender') ?? 'User';
    }

    public static function getNavigationBadge(): ?string
    {
        return 'x3';

    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'gray';
    }

    public function filtersForm(Schema $schema): Schema
    {
        $calendarOptions = BookingCalendarModel::pluck('name', 'id')->toArray();
        $calendarIds = array_keys($calendarOptions);

        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('booking_calendars_1')
                            ->options($calendarOptions)
                            ->label('#1 ◴ Tekninker')
                            ->placeholder('Select Tekniker for Calendar 1')
                            ->searchable()
                            ->live()
                            ->columnSpan(3)
                            ->default($calendarIds[0] ?? null)
                            ->afterStateUpdated(function ($state) {
                                $this->dispatch('refreshCalendar');
                            }),
                        Select::make('booking_calendars_2')
                            ->options($calendarOptions)
                            ->label('#2 ◴ Tekninker')
                            ->placeholder('Select Tekniker for Calendar 2')
                            ->searchable()
                            ->live()
                            ->default($calendarIds[1] ?? null)
                            ->afterStateUpdated(function ($state) {
                                $this->dispatch('refreshCalendar');
                            }),
                        Select::make('booking_calendars_3')
                            ->options($calendarOptions)
                            ->label('#3 ◴ Tekniker')
                            ->placeholder('Select Tekniker for Calendar 3')
                            ->searchable()
                            ->live()
                            ->default($calendarIds[2] ?? null)
                            ->afterStateUpdated(function ($state) {
                                $this->dispatch('refreshCalendar');
                            }),
                    ])
                    ->columns(12)
                    ->columnSpanFull(),
            ]);
    }

    public function getPermissionCheckClosure(): Closure
    {
        return fn (string $widgetClass) => true;
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 3;
    }

    public function getWidgetsColumns(): int|array
    {
        return 3;
    }

    public function getColumns(): int|array
    {
        return 3;
    }

    public function getHeaderWidgets(): array
    {
        return [

        ];
    }

    public function getWidgets(): array
    {
        return [
            MultiCalendar1::class,
            MultiCalendar2::class,
            MultiCalendar3::class,

        ];
    }
}
