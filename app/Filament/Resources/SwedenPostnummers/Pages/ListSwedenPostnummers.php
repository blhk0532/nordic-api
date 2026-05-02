<?php

namespace App\Filament\Resources\SwedenPostnummers\Pages;

use App\Filament\Resources\SwedenPostnummers\SwedenPostnummerResource;
use App\Filament\Resources\SwedenPostnummers\Widgets\MapPickerWidget;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Leek\FilamentHeaderFilters\Concerns\HasHeaderFilters;

class ListSwedenPostnummers extends ListRecords
{
    use HasHeaderFilters;

    protected static string $resource = SwedenPostnummerResource::class;

    protected function getFooterWidgets(): array
    {
        return [
            //    MapPickerWidget::class,           // Table with map
        ];
    }

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
