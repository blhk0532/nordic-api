<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Resources\GraperPageResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use CybertronianKelvin\Graper\Resources\GraperPageResource;

class CreateGraperPage extends CreateRecord
{
    protected static string $resource = GraperPageResource::class;
}
