<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Resources\Messages\Pages;

use AdultDate\FilamentWirechat\Filament\Resources\Messages\MessageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMessage extends CreateRecord
{
    protected static string $resource = MessageResource::class;
}
