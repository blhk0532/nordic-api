<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\Pages;

use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Bookings\BookingResource;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;

class ListBookings extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = BookingResource::class;

    public function getTabs(): array
    {
        return [
            null => Tab::make('Show All'),
            'booked' => Tab::make()->query(fn ($query) => $query->where('status', 'booked')),
            'confirmed' => Tab::make()->query(fn ($query) => $query->where('status', 'confirmed')),
            'processing' => Tab::make()->query(fn ($query) => $query->where('status', 'processing')),
            'cancelled' => Tab::make()->query(fn ($query) => $query->where('status', 'cancelled')),
            'updated' => Tab::make()->query(fn ($query) => $query->where('status', 'updated')),
            'completed' => Tab::make()->query(fn ($query) => $query->where('status', 'completed')),
        ];
    }

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    //   protected function getHeaderWidgets(): array
    //   {
    //       return BookingResource::getWidgets();
    //   }

    public function getTitle(): string|Htmlable
    {
        return '';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
