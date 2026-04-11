<?php

namespace App\Filament\Resources\SwedenPostorters\Pages;

use App\Filament\Resources\SwedenPostorters\SwedenPostorterResource;
use App\Filament\Widgets\KommunerMapWidget2;
use App\Filament\Widgets\LocationMapPickerWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSwedenPostorters extends ListRecords
{
    protected static string $resource = SwedenPostorterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [

            KommunerMapWidget2::make(),
            //    LocationMapPickerWidget::class,   // Interactive picker
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}
