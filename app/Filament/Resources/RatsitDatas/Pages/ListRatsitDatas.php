<?php

declare(strict_types=1);

namespace App\Filament\Resources\RatsitDatas\Pages;

use App\Filament\Resources\RatsitDatas\RatsitDataResource;
use App\Filament\Widgets\RatsitDataStatsWidget;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListRatsitDatas extends ListRecords
{
    protected static string $resource = RatsitDataResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            RatsitDataStatsWidget::class,
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
