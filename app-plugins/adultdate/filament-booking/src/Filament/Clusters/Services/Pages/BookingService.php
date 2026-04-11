<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Services\Pages;

// use Adultdate\FilamentBooking\Filament\Widgets\BookingCalendarWidget;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Pages\Page;
use Filament\Widgets\Widget;
use UnitEnum;

class BookingService extends Page
{
    protected string $view = 'filament-booking::pages.page';

    protected static ?string $navigationLabel = 'Booking';

    protected static string $routePath = '/booking/service';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?int $sort = 1;

    protected static bool $shouldRegisterNavigation = false;

    protected static bool $isDiscovered = false;

    protected static string|UnitEnum|null $navigationGroup = '';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    /**
     * Return header widgets for the page.
     *
     * @return array<class-string<Widget>>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            //       BookingCalendarWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('create-new-booking')::make()
                ->label('New schedule')
                ->icon('heroicon-o-calendar'), ];
    }
}
