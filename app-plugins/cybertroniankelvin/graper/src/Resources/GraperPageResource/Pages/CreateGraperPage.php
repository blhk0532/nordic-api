<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Resources\GraperPageResource\Pages;

use CybertronianKelvin\Graper\Resources\GraperPageResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateGraperPage extends CreateRecord
{
    protected static string $resource = GraperPageResource::class;

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

}
