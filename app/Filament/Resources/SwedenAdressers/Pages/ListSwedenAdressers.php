<?php

namespace App\Filament\Resources\SwedenAdressers\Pages;

use App\Filament\Resources\SwedenAdressers\SwedenAdresserResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListSwedenAdressers extends ListRecords
{
    protected static string $resource = SwedenAdresserResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getTitle(): string|Htmlable
    {
        return '';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
