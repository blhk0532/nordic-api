<?php

namespace App\Filament\Resources\SwedenPostorters\Pages;

use App\Filament\Resources\SwedenPostorters\SwedenPostorterResource;
use App\Filament\Widgets\PostortViewMapWidget;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSwedenPostorter extends ViewRecord
{
    protected static string $resource = SwedenPostorterResource::class;

    protected static ?string $title = 'View Postort';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PostortViewMapWidget::class,
        ];
    }

    public function getWidgetData(): array
    {
        $record = $this->getRecord();

        return [
            'postortName' => (string) ($record->postort ?? ''),
            'kommunName' => (string) ($record->kommun ?? ''),
            'postortLatitude' => $record->latitude,
            'postortLongitude' => $record->longitude,
        ];
    }
}
