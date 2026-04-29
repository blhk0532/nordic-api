<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Resources\GraperPageResource\Pages;

use CybertronianKelvin\Graper\Resources\GraperPageResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListGraperPages extends ListRecords
{
    protected static string $resource = GraperPageResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }
}
