<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Services\Widgets;

use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Services\Pages\ListServices;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ServiceStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListServices::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Total Services', $this->getPageTableQuery()->count()),
            Stat::make('Lowest price', number_format((float) $this->getPageTableQuery()->min('price'), 2)),
            Stat::make('Highest price', number_format((float) $this->getPageTableQuery()->max('price'), 2)),
        ];
    }
}
