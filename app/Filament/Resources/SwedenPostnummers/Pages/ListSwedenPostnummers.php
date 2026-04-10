<?php

namespace App\Filament\Resources\SwedenPostnummers\Pages;

use App\Filament\Resources\SwedenPostnummers\SwedenPostnummerResource;
use App\Filament\Resources\SwedenPostnummers\Widgets\MapPickerWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSwedenPostnummers extends ListRecords
{
    protected static string $resource = SwedenPostnummerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MapPickerWidget::class,           // Table with map
        ];
    }
}
