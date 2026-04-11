<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Widgets;

use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Pages\ListBookings;
use Adultdate\FilamentBooking\Models\Booking\Booking;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class BookingStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListBookings::class;
    }

    protected function getStats(): array
    {
        $orderData = Trend::model(Booking::class)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            Stat::make('Bookings', $this->getPageTableQuery()->count())
                ->chart(
                    $orderData
                        ->map(fn (TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),
            Stat::make('Complete', $this->getPageTableQuery()->whereIn('status', ['complete'])->count()),
            Stat::make('Average price', number_format((float) $this->getPageTableQuery()->avg('total_price'), 2)),
        ];
    }
}
