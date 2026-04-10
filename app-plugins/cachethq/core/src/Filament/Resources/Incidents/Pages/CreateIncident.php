<?php

namespace Cachet\Filament\Resources\Incidents\Pages;

use Cachet\Filament\Resources\Incidents\IncidentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateIncident extends CreateRecord
{
    protected static string $resource = IncidentResource::class;

    protected ?string $heading = null;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
