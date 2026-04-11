<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Clients\Pages;

use Adultdate\FilamentBooking\Filament\Clusters\Services\Resources\Clients\ClientResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getActions(): array
    {
        return [

        ];
    }

    public function getTitle(): string|Htmlable
    {
        return '';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
