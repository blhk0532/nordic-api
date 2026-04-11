<?php

declare(strict_types=1);

namespace App\Filament\Resources\HittaDatas\Pages;

use App\Filament\Resources\HittaDatas\HittaDataResource;
use App\Filament\Widgets\HittaDataStatsWidget;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListHittaDatas extends ListRecords
{
    protected static string $resource = HittaDataResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            HittaDataStatsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getBreadcrumbs(): array
    {
        return [

        ];
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }
}
