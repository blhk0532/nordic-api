<?php

namespace Cachet\Filament\Resources\IncidentTemplates\Pages;

use Cachet\Filament\Resources\IncidentTemplates\IncidentTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIncidentTemplate extends CreateRecord
{
    protected static string $resource = IncidentTemplateResource::class;

    protected ?string $heading = null;

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
