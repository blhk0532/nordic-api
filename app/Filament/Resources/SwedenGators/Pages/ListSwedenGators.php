<?php

namespace App\Filament\Resources\SwedenGators\Pages;

use App\Filament\Resources\SwedenGators\SwedenGatorResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListSwedenGators extends ListRecords
{
    protected static string $resource = SwedenGatorResource::class;

    protected static ?string $title = '@ Gator DB';

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string|Htmlable
    {
        return '';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }
}
