<?php

declare(strict_types=1);

namespace App\Filament\Panels\App\Resources\Searches\Pages;

use App\Filament\Panels\App\Resources\Searches\SearchResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSearch extends ViewRecord
{
    protected static string $resource = SearchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
