<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Resources\GraperPageResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use CybertronianKelvin\Graper\Resources\GraperPageResource;

class ListGraperPages extends ListRecords
{
    protected static string $resource = GraperPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
