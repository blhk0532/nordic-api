<?php

declare(strict_types=1);

namespace App\Filament\Panels\App\Resources\Searches\Pages;

use App\Filament\Panels\App\Resources\Searches\SearchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSearches extends ListRecords
{
    protected static string $resource = SearchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
