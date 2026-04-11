<?php

declare(strict_types=1);

namespace AdultDate\FilamentWirechat\Filament\Resources\Messages\Pages;

use AdultDate\FilamentWirechat\Filament\Resources\Messages\MessageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMessages extends ListRecords
{
    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
