<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Resources\Booking\Users\Pages;

use Adultdate\FilamentBooking\Filament\Resources\Booking\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Tekniker & Bokare';

    public function getTabs(): array
    {
        return [
            // ServiceProvider s
            'service' => Tab::make('Servicetekniker')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'service')),

            // Mötesbokare
            'booking' => Tab::make('Mötesbokare')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'booking')),
            // 'all' => Tab::make('All Users'),

        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
